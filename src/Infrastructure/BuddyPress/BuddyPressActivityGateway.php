<?php
/**
 * BuddyPress activity adapter for MetaFans v6.
 *
 * @package MetaFansCore
 */

namespace METAFANSCORE\Infrastructure\BuddyPress;

use METAFANSCORE\Application\Contracts\ActivityGateway;

defined( 'ABSPATH' ) || exit;

final class BuddyPressActivityGateway implements ActivityGateway {
	public function is_available(): bool {
		return class_exists( 'BP_Activity_Activity' ) && function_exists( 'bp_activity_add' );
	}

	public function find( int $activity_id ): ?object {
		if ( $activity_id < 1 || ! class_exists( 'BP_Activity_Activity' ) ) {
			return null;
		}
		$activity = new \BP_Activity_Activity( $activity_id );
		return ! empty( $activity->id ) ? $activity : null;
	}

	public function query( array $args ): array {
		if ( ! class_exists( 'BP_Activity_Activity' ) ) {
			return array();
		}
		$result = \BP_Activity_Activity::get( $args );
		return isset( $result['activities'] ) && is_array( $result['activities'] ) ? $result['activities'] : array();
	}

	public function search( string $search_terms, int $page, int $per_page ): array {
		if ( ! class_exists( 'BP_Activity_Activity' ) ) {
			return array();
		}
		return $this->query(
			array(
				'search_terms' => $search_terms,
				'page'         => max( 1, $page ),
				'per_page'     => min( 50, max( 1, $per_page ) ),
				'show_hidden'  => false,
				'spam'         => 'ham_only',
			)
		);
	}

	public function native_can_read( object $activity, int $viewer_id ): bool {
		return ! function_exists( 'bp_activity_user_can_read' ) || (bool) bp_activity_user_can_read( $activity, $viewer_id );
	}

	public function are_friends( int $viewer_id, int $owner_id ): bool {
		return $viewer_id > 0 && $owner_id > 0 && function_exists( 'friends_check_friendship' )
			? (bool) friends_check_friendship( $viewer_id, $owner_id )
			: false;
	}

	public function can_moderate( int $user_id ): bool {
		return $user_id > 0 && ( user_can( $user_id, 'bp_moderate' ) || user_can( $user_id, 'manage_options' ) );
	}

	public function is_group_member( int $user_id, int $group_id ): bool {
		return function_exists( 'groups_is_user_member' ) && (bool) groups_is_user_member( $user_id, $group_id );
	}

	public function create_update( int $actor_id, string $content, string $component, int $item_id ): int {
		if ( 'groups' === $component && $item_id > 0 && function_exists( 'groups_post_update' ) ) {
			return (int) groups_post_update( array( 'content' => $content, 'group_id' => $item_id ) );
		}
		if ( ! function_exists( 'bp_activity_post_update' ) ) {
			return 0;
		}
		return (int) bp_activity_post_update( array( 'content' => $content, 'user_id' => $actor_id ) );
	}

	public function update_existing( int $activity_id, int $actor_id, string $content, string $component, int $item_id ): int {
		if ( ! function_exists( 'bp_activity_add' ) ) {
			return 0;
		}
		return (int) bp_activity_add(
			array(
				'id'        => $activity_id,
				'user_id'   => $actor_id,
				'content'   => $content,
				'component' => $component ?: false,
				'type'      => 'activity_update',
				'item_id'   => $item_id ?: false,
			)
		);
	}

	public function delete( int $activity_id ): bool {
		return function_exists( 'bp_activity_delete' ) && (bool) bp_activity_delete( array( 'id' => $activity_id ) );
	}

	public function get_meta( int $activity_id, string $key ) {
		return function_exists( 'bp_activity_get_meta' ) ? bp_activity_get_meta( $activity_id, $key, true ) : null;
	}

	public function update_meta( int $activity_id, string $key, $value ): bool {
		if ( ! function_exists( 'bp_activity_update_meta' ) ) {
			return false;
		}
		$result = bp_activity_update_meta( $activity_id, $key, $value );
		return false !== $result;
	}
}
