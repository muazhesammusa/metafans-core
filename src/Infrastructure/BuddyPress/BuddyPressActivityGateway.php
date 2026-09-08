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
	private array $activity_cache = array();

	public function is_available(): bool {
		return class_exists( 'BP_Activity_Activity' ) && function_exists( 'bp_activity_add' );
	}

	public function find( int $activity_id ): ?object {
		if ( $activity_id < 1 || ! class_exists( 'BP_Activity_Activity' ) ) {
			return null;
		}
		if ( array_key_exists( $activity_id, $this->activity_cache ) ) {
			return $this->activity_cache[ $activity_id ];
		}
		$activity = new \BP_Activity_Activity( $activity_id );
		$this->activity_cache[ $activity_id ] = ! empty( $activity->id ) ? $activity : null;
		return $this->activity_cache[ $activity_id ];
	}

	public function query( array $args ): array {
		if ( ! class_exists( 'BP_Activity_Activity' ) ) {
			return array();
		}
		$args['page']              = isset( $args['page'] ) ? max( 1, (int) $args['page'] ) : 1;
		$args['per_page']          = isset( $args['per_page'] ) ? min( 50, max( 1, (int) $args['per_page'] ) ) : 10;
		$args['count_total']       = false;
		$args['update_meta_cache'] = true;
		$result                    = \BP_Activity_Activity::get( $args );
		$activities                = isset( $result['activities'] ) && is_array( $result['activities'] ) ? $result['activities'] : array();
		foreach ( $activities as $activity ) {
			if ( is_object( $activity ) && ! empty( $activity->id ) ) {
				$this->activity_cache[ (int) $activity->id ] = $activity;
			}
		}
		return $activities;
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
			$id = (int) groups_post_update( array( 'content' => $content, 'group_id' => $item_id ) );
		} elseif ( function_exists( 'bp_activity_post_update' ) ) {
			$id = (int) bp_activity_post_update( array( 'content' => $content, 'user_id' => $actor_id ) );
		} else {
			$id = 0;
		}
		if ( $id > 0 ) {
			unset( $this->activity_cache[ $id ] );
		}
		return $id;
	}

	public function update_existing( int $activity_id, int $actor_id, string $content, string $component, int $item_id ): int {
		if ( ! function_exists( 'bp_activity_add' ) ) {
			return 0;
		}
		$id = (int) bp_activity_add(
			array(
				'id'        => $activity_id,
				'user_id'   => $actor_id,
				'content'   => $content,
				'component' => $component ?: false,
				'type'      => 'activity_update',
				'item_id'   => $item_id ?: false,
			)
		);
		unset( $this->activity_cache[ $activity_id ] );
		return $id;
	}

	public function delete( int $activity_id ): bool {
		$deleted = function_exists( 'bp_activity_delete' ) && (bool) bp_activity_delete( array( 'id' => $activity_id ) );
		if ( $deleted ) {
			unset( $this->activity_cache[ $activity_id ] );
		}
		return $deleted;
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

	public function with_lock( int $activity_id, string $scope, callable $callback ) {
		$key      = '_metafans_lock_' . md5( $scope . ':' . $activity_id );
		$deadline = microtime( true ) + 2.0;
		do {
			if ( add_option( $key, time(), '', false ) ) {
				try {
					return $callback();
				} finally {
					delete_option( $key );
				}
			}
			$created = (int) get_option( $key, 0 );
			if ( $created && $created < time() - 10 ) {
				delete_option( $key );
				continue;
			}
			usleep( 20000 );
		} while ( microtime( true ) < $deadline );
		return null;
	}
}
