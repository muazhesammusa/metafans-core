<?php
/** Follow relationship persistence boundary. */
namespace METAFANSCORE\Application\Contracts;
defined( 'ABSPATH' ) || exit;
interface FollowGateway {
    public function user_exists( int $user_id ): bool;
    public function followers( int $user_id ): array;
    public function following( int $user_id ): array;
    public function persist_followers( int $user_id, array $followers ): bool;
    public function persist_following( int $user_id, array $following ): bool;
    public function with_lock( int $actor_id, int $target_id, callable $callback );
}
