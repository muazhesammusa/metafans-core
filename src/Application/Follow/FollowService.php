<?php
/** Core-owned idempotent follow relationships. */
namespace METAFANSCORE\Application\Follow;

use METAFANSCORE\Application\Contracts\FollowGateway;
use METAFANSCORE\Domain\Social\MutationResult;

defined( 'ABSPATH' ) || exit;

final class FollowService {
	private FollowGateway $gateway;

	public function __construct( FollowGateway $gateway ) {
		$this->gateway = $gateway;
	}

	public function toggle( int $actor_id, int $target_id ): MutationResult {
		if ( $actor_id < 1 || $target_id < 1 || $actor_id === $target_id || ! $this->gateway->user_exists( $target_id ) ) {
			return MutationResult::failure( 'invalid_follow_target', __( 'Invalid follow target.', 'metafans-core' ), 400 );
		}

		$result = $this->gateway->with_lock(
			$actor_id,
			$target_id,
			function () use ( $actor_id, $target_id ) {
				$previous_followers = $this->normalize_ids( $this->gateway->followers( $target_id ) );
				$previous_following = $this->normalize_ids( $this->gateway->following( $actor_id ) );
				$followers          = $previous_followers;
				$following          = $previous_following;
				$has_follower_side  = in_array( $actor_id, $followers, true );
				$has_following_side = in_array( $target_id, $following, true );
				$inconsistent       = $has_follower_side !== $has_following_side;
				$is_following       = $has_follower_side || $has_following_side;

				if ( $is_following ) {
					$followers = array_values( array_diff( $followers, array( $actor_id ) ) );
					$following = array_values( array_diff( $following, array( $target_id ) ) );
					$next      = false;
				} else {
					$followers[] = $actor_id;
					$following[] = $target_id;
					$followers   = $this->normalize_ids( $followers );
					$following   = $this->normalize_ids( $following );
					$next        = true;
				}

				if ( ! $this->gateway->persist_followers( $target_id, $followers ) ) {
					return MutationResult::failure( 'follow_persist_failed', __( 'Follow relationship could not be updated.', 'metafans-core' ), 500 );
				}
				if ( ! $this->gateway->persist_following( $actor_id, $following ) ) {
					$this->gateway->persist_followers( $target_id, $previous_followers );
					return MutationResult::failure( 'follow_persist_failed', __( 'Follow relationship could not be updated.', 'metafans-core' ), 500 );
				}

				return MutationResult::success(
					array(
						'is_following'    => $next,
						'followers_count' => count( $followers ),
						'following_count' => count( $following ),
						'repaired'        => $inconsistent,
					)
				);
			}
		);

		return $result instanceof MutationResult
			? $result
			: MutationResult::failure( 'follow_busy', __( 'Follow relationship is busy. Try again.', 'metafans-core' ), 409 );
	}

	public function info( int $follower_id, int $following_id ): array {
		$followers          = $this->normalize_ids( $this->gateway->followers( $following_id ) );
		$following          = $this->normalize_ids( $this->gateway->following( $follower_id ) );
		$has_follower_side  = in_array( $follower_id, $followers, true );
		$has_following_side = in_array( $following_id, $following, true );

		return array(
			'is_following'    => $has_follower_side && $has_following_side,
			'followers_count' => count( $followers ),
			'following_count' => count( $following ),
			'consistent'      => $has_follower_side === $has_following_side,
		);
	}

	private function normalize_ids( array $ids ): array {
		return array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
	}
}
