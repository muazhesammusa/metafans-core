<?php
/**
 * Friendship persistence contract.
 *
 * @package MetaFansCore
 */

namespace METAFANSCORE\Application\Contracts;

defined( 'ABSPATH' ) || exit;

interface FriendshipGateway {
	public function is_available(): bool;

	public function member_exists( int $user_id ): bool;

	public function status( int $actor_id, int $target_id ): string;

	public function request( int $actor_id, int $target_id ): bool;

	public function withdraw( int $actor_id, int $target_id ): bool;

	public function remove( int $actor_id, int $target_id ): bool;

	public function accept( int $actor_id, int $target_id ): bool;
}
