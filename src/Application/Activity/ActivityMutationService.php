<?php
/**
 * Activity create/edit/delete/visibility application service.
 *
 * @package MetaFansCore
 */

namespace METAFANSCORE\Application\Activity;

use METAFANSCORE\Application\Contracts\ActivityGateway;
use METAFANSCORE\Domain\Activity\ActivityResult;

defined( 'ABSPATH' ) || exit;

final class ActivityMutationService {
	private ActivityGateway $gateway;
	private ActivityPrivacyService $privacy;

	public function __construct( ActivityGateway $gateway, ActivityPrivacyService $privacy ) {
		$this->gateway = $gateway;
		$this->privacy = $privacy;
	}

	public function upsert( int $actor_id, array $command ): ActivityResult {
		if ( $actor_id < 1 ) {
			return ActivityResult::failure( 'authentication_required', 'Authentication required.', 401 );
		}
		if ( ! $this->gateway->is_available() ) {
			return ActivityResult::failure( 'activity_unavailable', 'BuddyPress activity is unavailable.', 503 );
		}

		$activity_id = isset( $command['activity_id'] ) ? (int) $command['activity_id'] : 0;
		$component   = isset( $command['component'] ) ? (string) $command['component'] : '';
		$item_id     = isset( $command['item_id'] ) ? (int) $command['item_id'] : 0;
		$content     = isset( $command['content'] ) ? (string) $command['content'] : '';

		if ( $activity_id > 0 && ! $this->privacy->can_edit( $activity_id, $actor_id ) ) {
			return ActivityResult::failure( 'activity_edit_forbidden', 'You are not allowed to edit this activity.', 403, $activity_id );
		}
		if ( 'groups' === $component && $item_id > 0 && ! $this->gateway->is_group_member( $actor_id, $item_id ) && ! $this->gateway->can_moderate( $actor_id ) ) {
			return ActivityResult::failure( 'group_post_forbidden', 'You are not allowed to post in this group.', 403, $activity_id );
		}

		if ( $activity_id > 0 ) {
			$persisted_id = $this->gateway->update_existing( $activity_id, $actor_id, $content, $component, $item_id );
		} else {
			$persisted_id = $this->gateway->create_update( $actor_id, $content, $component, $item_id );
		}
		if ( $persisted_id < 1 ) {
			return ActivityResult::failure( 'activity_update_failed', 'There was an error when posting your update. Please try again.', 500, $activity_id );
		}

		$this->gateway->update_meta( $persisted_id, 'activity_media', isset( $command['media'] ) ? $command['media'] : array() );
		$this->gateway->update_meta( $persisted_id, 'activity_accessibility', $this->privacy->sanitize_visibility( (string) ( $command['visibility'] ?? 'public' ) ) );
		if ( ! empty( $command['album'] ) ) {
			$this->gateway->update_meta( $persisted_id, 'is_album_activity', $command['album'] );
		}
		if ( ! empty( $command['mark_edited'] ) ) {
			$this->gateway->update_meta( $persisted_id, 'last_edited', array( 'is_edited' => true, 'timestamp' => time() ) );
		}

		return ActivityResult::success( $persisted_id, $activity_id > 0 ? 'activity_edited' : 'activity_created' );
	}

	public function delete( int $actor_id, int $activity_id ): ActivityResult {
		if ( ! $this->privacy->can_delete( $activity_id, $actor_id ) ) {
			return ActivityResult::failure( 'activity_delete_forbidden', 'You are not allowed to delete this activity.', 403, $activity_id );
		}
		return $this->gateway->delete( $activity_id )
			? ActivityResult::success( $activity_id, 'activity_deleted' )
			: ActivityResult::failure( 'activity_delete_failed', 'The activity could not be deleted!', 500, $activity_id );
	}

	public function update_visibility( int $actor_id, int $activity_id, string $visibility ): ActivityResult {
		if ( ! $this->privacy->can_edit( $activity_id, $actor_id ) ) {
			return ActivityResult::failure( 'activity_visibility_forbidden', 'You are not allowed to edit this activity.', 403, $activity_id );
		}
		$visibility = $this->privacy->sanitize_visibility( $visibility );
		if ( ! $this->gateway->update_meta( $activity_id, 'activity_accessibility', $visibility ) ) {
			return ActivityResult::failure( 'activity_visibility_failed', 'Could not update activity visibility.', 500, $activity_id );
		}
		return ActivityResult::success( $activity_id, 'activity_visibility_updated' );
	}
}
