<?php
/** Community media lifecycle boundary. */
namespace METAFANSCORE\Application\Contracts;
defined( 'ABSPATH' ) || exit;
interface MediaGateway {
    public function upload( int $actor_id, string $field_name, bool $images_only = false );
    public function can_delete( int $actor_id, int $attachment_id ): bool;
    public function delete( int $attachment_id ): bool;
    public function mark_attached( int $attachment_id, int $actor_id, int $activity_id ): void;
    public function cleanup_orphans( int $older_than, int $limit = 50 ): int;
}
