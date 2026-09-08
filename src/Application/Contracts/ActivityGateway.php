<?php
/**
 * BuddyPress activity persistence/query boundary for MetaFans v6.
 *
 * @package MetaFansCore
 */

namespace METAFANSCORE\Application\Contracts;

defined( 'ABSPATH' ) || exit;

interface ActivityGateway {
	public function is_available(): bool;
	public function find( int $activity_id ): ?object;
	public function query( array $args ): array;
	public function search( string $search_terms, int $page, int $per_page ): array;
	public function native_can_read( object $activity, int $viewer_id ): bool;
	public function are_friends( int $viewer_id, int $owner_id ): bool;
	public function can_moderate( int $user_id ): bool;
	public function is_group_member( int $user_id, int $group_id ): bool;
	public function create_update( int $actor_id, string $content, string $component, int $item_id ): int;
	public function update_existing( int $activity_id, int $actor_id, string $content, string $component, int $item_id ): int;
	public function delete( int $activity_id ): bool;
	public function get_meta( int $activity_id, string $key );
	public function update_meta( int $activity_id, string $key, $value ): bool;
}
