<?php
/**
 * Friendship application service.
 *
 * @package MetaFansCore
 */

namespace METAFANSCORE\Application\Friendship;

use METAFANSCORE\Application\Contracts\FriendshipGateway;
use METAFANSCORE\Domain\Friendship\FriendshipResult;

defined( 'ABSPATH' ) || exit;

final class FriendshipService {
	private FriendshipGateway $gateway;

	public function __construct( FriendshipGateway $gateway ) {
		$this->gateway = $gateway;
	}

	public function toggle( int $actor_id, int $target_id ): FriendshipResult {
		if ( $actor_id < 1 ) {
			return FriendshipResult::failure( 'authentication_required', 'Authentication required.', 401 );
		}

		if ( $target_id < 1 || $actor_id === $target_id ) {
			return FriendshipResult::failure( 'invalid_friendship_target', 'Invalid friendship target.', 400, $actor_id, $target_id );
		}

		if ( ! $this->gateway->is_available() ) {
			return FriendshipResult::failure( 'friendship_unavailable', 'BuddyPress friendships are unavailable.', 503, $actor_id, $target_id );
		}

		if ( ! $this->gateway->member_exists( $target_id ) ) {
			return FriendshipResult::failure( 'member_not_found', 'Member not found.', 404, $actor_id, $target_id );
		}

		$state     = $this->gateway->status( $actor_id, $target_id );
		$operation = 'none';
		$updated   = false;

		switch ( $state ) {
			case 'pending':
				$operation = 'withdraw';
				$updated   = $this->gateway->withdraw( $actor_id, $target_id );
				break;

			case 'not_friends':
			case '':
				$operation = 'request';
				$updated   = $this->gateway->request( $actor_id, $target_id );
				break;

			case 'is_friend':
				$operation = 'remove';
				$updated   = $this->gateway->remove( $actor_id, $target_id );
				break;

			case 'awaiting_response':
				$operation = 'accept';
				$updated   = $this->gateway->accept( $actor_id, $target_id );
				break;

			default:
				return FriendshipResult::failure( 'unsupported_friendship_state', 'Unsupported friendship state.', 409, $actor_id, $target_id, $state );
		}

		if ( ! $updated ) {
			return FriendshipResult::failure( 'friendship_update_failed', 'Friendship could not be updated.', 409, $actor_id, $target_id, $state );
		}

		return FriendshipResult::success(
			$actor_id,
			$target_id,
			$state,
			$this->gateway->status( $actor_id, $target_id ),
			$operation
		);
	}
}
