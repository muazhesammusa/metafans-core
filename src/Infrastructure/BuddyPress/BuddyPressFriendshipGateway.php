<?php
/**
 * BuddyPress friendship adapter.
 *
 * @package MetaFansCore
 */

namespace METAFANSCORE\Infrastructure\BuddyPress;

use METAFANSCORE\Application\Contracts\FriendshipGateway;

defined( 'ABSPATH' ) || exit;

final class BuddyPressFriendshipGateway implements FriendshipGateway {
	public function is_available(): bool {
		return class_exists( 'BP_Friends_Friendship' )
			&& function_exists( 'friends_add_friend' )
			&& function_exists( 'friends_remove_friend' );
	}

	public function member_exists( int $user_id ): bool {
		return $user_id > 0 && (bool) get_userdata( $user_id );
	}

	public function status( int $actor_id, int $target_id ): string {
		if ( ! class_exists( 'BP_Friends_Friendship' ) ) {
			return '';
		}

		return (string) \BP_Friends_Friendship::check_is_friend( $actor_id, $target_id );
	}

	public function request( int $actor_id, int $target_id ): bool {
		return function_exists( 'friends_add_friend' ) && (bool) friends_add_friend( $actor_id, $target_id );
	}

	public function withdraw( int $actor_id, int $target_id ): bool {
		return function_exists( 'friends_withdraw_friendship' ) && (bool) friends_withdraw_friendship( $actor_id, $target_id );
	}

	public function remove( int $actor_id, int $target_id ): bool {
		return function_exists( 'friends_remove_friend' ) && (bool) friends_remove_friend( $actor_id, $target_id );
	}

	public function accept( int $actor_id, int $target_id ): bool {
		if ( ! function_exists( 'friends_accept_friendship' ) || ! class_exists( 'BP_Friends_Friendship' ) ) {
			return false;
		}

		$friendship_id = (int) \BP_Friends_Friendship::get_friendship_id( $actor_id, $target_id );
		return $friendship_id > 0 && (bool) friends_accept_friendship( $friendship_id );
	}
}
