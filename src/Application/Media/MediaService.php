<?php
/** Core-owned community media lifecycle. */
namespace METAFANSCORE\Application\Media;
use METAFANSCORE\Application\Contracts\MediaGateway;
use METAFANSCORE\Domain\Social\MutationResult;
defined( 'ABSPATH' ) || exit;
final class MediaService {
    private MediaGateway $gateway;
    public function __construct( MediaGateway $gateway ) { $this->gateway = $gateway; }
    public function upload( int $actor_id, string $field_name, bool $images_only = false ): MutationResult {
        if ( $actor_id < 1 || '' === $field_name ) return MutationResult::failure( 'invalid_upload_request', __( 'Invalid upload request.', 'metafans-core' ), 400 );
        $uploaded = $this->gateway->upload( $actor_id, $field_name, $images_only );
        if ( is_wp_error( $uploaded ) ) return MutationResult::failure( $uploaded->get_error_code(), $uploaded->get_error_message(), 400 );
        return MutationResult::success( is_array( $uploaded ) ? $uploaded : array() );
    }
    public function mark_attached( int $attachment_id, int $actor_id, int $activity_id ): void { $this->gateway->mark_attached( $attachment_id, $actor_id, $activity_id ); }
    public function cleanup_orphans( int $max_age = 86400, int $limit = 50 ): int { return $this->gateway->cleanup_orphans( time() - max( 3600, $max_age ), $limit ); }
    public function delete( int $actor_id, int $attachment_id ): MutationResult {
        if ( $attachment_id < 1 ) return MutationResult::failure( 'invalid_attachment', __( 'Invalid media attachment.', 'metafans-core' ), 400 );
        if ( ! $this->gateway->can_delete( $actor_id, $attachment_id ) ) return MutationResult::failure( 'media_delete_forbidden', __( 'You are not allowed to delete this media.', 'metafans-core' ), 403 );
        return $this->gateway->delete( $attachment_id ) ? MutationResult::success( array( 'attachment_id' => $attachment_id ) ) : MutationResult::failure( 'media_delete_failed', __( 'Media could not be deleted.', 'metafans-core' ), 500 );
    }
}
