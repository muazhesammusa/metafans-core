<?php
/**
 * Viewer-aware activity privacy and ownership policy.
 *
 * @package MetaFansCore
 */

namespace METAFANSCORE\Application\Activity;

use METAFANSCORE\Application\Contracts\ActivityGateway;

defined( 'ABSPATH' ) || exit;

final class ActivityPrivacyService {
	private ActivityGateway $gateway;
	private array $visibility_cache = array();
	private array $friendship_cache = array();
	private array $moderation_cache = array();

	public function __construct( ActivityGateway $gateway ) {
		$this->gateway = $gateway;
	}

	public function sanitize_visibility( string $value ): string {
		$value = strtolower( preg_replace( '/[^a-z0-9_-]/', '', $value ) );
		return in_array( $value, array( 'public', 'friends', 'onlyme' ), true ) ? $value : 'public';
	}

	public function visibility( int $activity_id ): string {
		if ( $activity_id < 1 ) {
			return 'public';
		}
		if ( array_key_exists( $activity_id, $this->visibility_cache ) ) {
			return $this->visibility_cache[ $activity_id ];
		}
		$value = (string) $this->gateway->get_meta( $activity_id, 'activity_accessibility' );
		$this->visibility_cache[ $activity_id ] = '' === $value ? 'public' : $this->sanitize_visibility( $value );
		return $this->visibility_cache[ $activity_id ];
	}

	public function can_view( int $activity_id, int $viewer_id = 0 ): bool {
		$activity = $this->gateway->find( $activity_id );
		return $activity ? $this->can_view_activity( $activity, $viewer_id ) : false;
	}

	/**
	 * Evaluate a BuddyPress activity object already loaded by a bounded query.
	 *
	 * This avoids re-fetching each activity while filtering a feed/search result.
	 */
	public function can_view_activity( object $activity, int $viewer_id = 0 ): bool {
		if ( empty( $activity->id ) ) {
			return false;
		}

		$owner_id = isset( $activity->user_id ) ? (int) $activity->user_id : 0;
		if ( $viewer_id > 0 && ( $viewer_id === $owner_id || $this->can_moderate( $viewer_id ) ) ) {
			return true;
		}

		if ( ! $this->gateway->native_can_read( $activity, $viewer_id ) ) {
			return false;
		}

		$visibility = $this->visibility( (int) $activity->id );
		if ( 'public' === $visibility ) {
			return true;
		}
		if ( 'onlyme' === $visibility ) {
			return false;
		}
		if ( 'friends' === $visibility ) {
			return $viewer_id > 0 && $owner_id > 0 && $this->are_friends( $viewer_id, $owner_id );
		}

		return (bool) apply_filters( 'metafans_core_can_view_activity', false, $activity, $viewer_id, $visibility );
	}

	public function filter_visible( array $activities, int $viewer_id = 0 ): array {
		return array_values(
			array_filter(
				$activities,
				fn( $activity ) => is_object( $activity ) && $this->can_view_activity( $activity, $viewer_id )
			)
		);
	}

	public function can_edit( int $activity_id, int $actor_id ): bool {
		$activity = $this->gateway->find( $activity_id );
		if ( ! $activity || $actor_id < 1 ) {
			return false;
		}
		return (int) $activity->user_id === $actor_id || $this->can_moderate( $actor_id );
	}

	public function can_delete( int $activity_id, int $actor_id ): bool {
		return $this->can_edit( $activity_id, $actor_id );
	}

	/**
	 * Drop request-local privacy state after an activity mutation.
	 */
	public function invalidate( int $activity_id = 0 ): void {
		if ( $activity_id > 0 ) {
			unset( $this->visibility_cache[ $activity_id ] );
			return;
		}
		$this->visibility_cache = array();
		$this->friendship_cache = array();
		$this->moderation_cache = array();
	}

	private function can_moderate( int $viewer_id ): bool {
		if ( ! array_key_exists( $viewer_id, $this->moderation_cache ) ) {
			$this->moderation_cache[ $viewer_id ] = $this->gateway->can_moderate( $viewer_id );
		}
		return $this->moderation_cache[ $viewer_id ];
	}

	private function are_friends( int $viewer_id, int $owner_id ): bool {
		$ids = array( $viewer_id, $owner_id );
		sort( $ids, SORT_NUMERIC );
		$key = $ids[0] . ':' . $ids[1];
		if ( ! array_key_exists( $key, $this->friendship_cache ) ) {
			$this->friendship_cache[ $key ] = $this->gateway->are_friends( $viewer_id, $owner_id );
		}
		return $this->friendship_cache[ $key ];
	}
}
