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

	public function __construct( ActivityGateway $gateway ) {
		$this->gateway = $gateway;
	}

	public function sanitize_visibility( string $value ): string {
		$value = strtolower( preg_replace( '/[^a-z0-9_-]/', '', $value ) );
		return in_array( $value, array( 'public', 'friends', 'onlyme' ), true ) ? $value : 'public';
	}

	public function visibility( int $activity_id ): string {
		$value = (string) $this->gateway->get_meta( $activity_id, 'activity_accessibility' );
		return '' === $value ? 'public' : $this->sanitize_visibility( $value );
	}

	public function can_view( int $activity_id, int $viewer_id = 0 ): bool {
		$activity = $this->gateway->find( $activity_id );
		if ( ! $activity || empty( $activity->id ) ) {
			return false;
		}

		$owner_id = isset( $activity->user_id ) ? (int) $activity->user_id : 0;
		if ( $viewer_id > 0 && ( $viewer_id === $owner_id || $this->gateway->can_moderate( $viewer_id ) ) ) {
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
			return $viewer_id > 0 && $owner_id > 0 && $this->gateway->are_friends( $viewer_id, $owner_id );
		}

		return (bool) apply_filters( 'metafans_core_can_view_activity', false, $activity, $viewer_id, $visibility );
	}

	public function can_edit( int $activity_id, int $actor_id ): bool {
		$activity = $this->gateway->find( $activity_id );
		if ( ! $activity || $actor_id < 1 ) {
			return false;
		}
		return (int) $activity->user_id === $actor_id || $this->gateway->can_moderate( $actor_id );
	}

	public function can_delete( int $activity_id, int $actor_id ): bool {
		return $this->can_edit( $activity_id, $actor_id );
	}
}
