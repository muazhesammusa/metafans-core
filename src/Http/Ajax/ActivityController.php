<?php
/**
 * Core-owned legacy activity mutation AJAX controller.
 *
 * @package MetaFansCore
 */

namespace METAFANSCORE\Http\Ajax;

use METAFANSCORE\Application\Activity\ActivityContentPreparer;
use METAFANSCORE\Application\Activity\ActivityMutationService;
use METAFANSCORE\Application\Activity\ActivityPrivacyService;
use METAFANSCORE\Application\Contracts\ActivityGateway;
use METAFANSCORE\Application\Contracts\PermissionGate;
use METAFANSCORE\Domain\Activity\ActivityResult;

defined( 'ABSPATH' ) || exit;

final class ActivityController {
	public const UPSERT_ACTION = 'th_bp_post_update';
	public const DELETE_ACTION = 'mf_activity_delete';
	public const VISIBILITY_ACTION = 'th_bp_update_activity_visibility';

	private PermissionGate $permissions;
	private ActivityGateway $gateway;
	private ActivityPrivacyService $privacy;
	private ActivityMutationService $mutations;
	private ActivityContentPreparer $content;

	public function __construct( PermissionGate $permissions, ActivityGateway $gateway, ActivityPrivacyService $privacy, ActivityMutationService $mutations, ActivityContentPreparer $content ) {
		$this->permissions = $permissions;
		$this->gateway     = $gateway;
		$this->privacy     = $privacy;
		$this->mutations   = $mutations;
		$this->content     = $content;
	}

	public function register(): void {
		add_action( 'wp_ajax_th_bp_post_update', array( $this, 'handle_upsert' ) );
		add_action( 'wp_ajax_mf_activity_delete', array( $this, 'handle_delete' ) );
		add_action( 'wp_ajax_th_bp_update_activity_visibility', array( $this, 'handle_visibility' ) );
	}

	public function handle_upsert(): void {
		$actor_id = $this->permissions->authorize_ajax_mutation( self::UPSERT_ACTION, 'read', array( 'post_update' ) );
		if ( is_wp_error( $actor_id ) ) {
			$this->send_wp_error( $actor_id );
		}

		$data = isset( $_POST['data'] ) && is_array( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : array();
		$raw_content = isset( $data['whats-new-post-content'] ) ? (string) $data['whats-new-post-content'] : '';
		$preview_url = isset( $data['whats-new-post-url-preview'] ) ? esc_url_raw( $data['whats-new-post-url-preview'] ) : '';
		$content     = $this->content->prepare( $raw_content, $preview_url );
		$media       = $this->build_media_records( (int) $actor_id, isset( $data['whats-new-post-media'] ) ? (string) $data['whats-new-post-media'] : '' );
		if ( is_wp_error( $media ) ) {
			$this->send_wp_error( $media );
		}

		$activity_id = isset( $data['activity_id'] ) && 'false' !== (string) $data['activity_id'] ? absint( $data['activity_id'] ) : 0;
		$component   = isset( $data['whats-new-post-object'] ) ? sanitize_key( apply_filters( 'bp_activity_post_update_object', $data['whats-new-post-object'] ) ) : '';
		$item_id     = isset( $data['whats-new-post-in'] ) ? absint( apply_filters( 'bp_activity_post_update_item_id', $data['whats-new-post-in'] ) ) : 0;
		$visibility  = $this->privacy->sanitize_visibility( isset( $data['activity_accessibility'] ) ? (string) $data['activity_accessibility'] : 'public' );
		$album       = array();
		if ( isset( $data['is_album_activity']['name'] ) ) {
			$album = array( 'album_name' => sanitize_text_field( $data['is_album_activity']['name'] ), 'created_at' => time() );
		}

		$result = $this->mutations->upsert(
			(int) $actor_id,
			array(
				'activity_id' => $activity_id,
				'content'     => $content,
				'component'   => $component,
				'item_id'     => $item_id,
				'media'       => $media,
				'visibility'  => $visibility,
				'album'       => $album,
				'mark_edited' => isset( $data['is_delete_activity'] ) && 'true' === (string) $data['is_delete_activity'],
			)
		);
		if ( ! $result->is_success() ) {
			$this->send_activity_failure( $result );
		}

		$persisted_id = $result->activity_id();
		$last_recorded = current_time( 'timestamp' );
		$args = array(
			'since'       => $last_recorded,
			'activity_id' => $persisted_id,
			'class'       => 'activity activity_update activity-item date-recorded-' . $last_recorded . ' just-posted',
		);
		$html = (string) apply_filters( 'metafans_core_activity_render_html', '', $persisted_id, $args );
		wp_send_json(
			array(
				'activity' => $html,
				'success'  => array( 'message' => __( 'Post and media updated', 'metafans-core' ), 'res' => true ),
			),
			200
		);
	}

	public function handle_delete(): void {
		$actor_id = $this->permissions->authorize_ajax_mutation( self::DELETE_ACTION, 'read' );
		if ( is_wp_error( $actor_id ) ) {
			$this->send_wp_error( $actor_id );
		}
		$activity_id = isset( $_POST['activity_id'] ) ? absint( $_POST['activity_id'] ) : 0;
		$result      = $this->mutations->delete( (int) $actor_id, $activity_id );
		wp_send_json(
			array(
				'response_type' => $result->is_success() ? 'success' : 'error',
				'response_msg'  => $result->is_success() ? __( 'The activity deleted successfully', 'metafans-core' ) : $result->message(),
			),
			$result->is_success() ? 200 : $result->http_status()
		);
	}

	public function handle_visibility(): void {
		$actor_id = $this->permissions->authorize_ajax_mutation( self::VISIBILITY_ACTION, 'read' );
		if ( is_wp_error( $actor_id ) ) {
			$this->send_wp_error( $actor_id );
		}
		$activity_id = isset( $_POST['activity_id'] ) ? absint( $_POST['activity_id'] ) : 0;
		$visibility  = isset( $_POST['activity_accessibility'] ) ? (string) wp_unslash( $_POST['activity_accessibility'] ) : 'public';
		$result      = $this->mutations->update_visibility( (int) $actor_id, $activity_id, $visibility );
		if ( ! $result->is_success() ) {
			$this->send_activity_failure( $result );
		}
		wp_send_json_success( array( 'visibility' => $this->privacy->sanitize_visibility( $visibility ) ) );
	}

	private function build_media_records( int $actor_id, string $raw_ids ) {
		if ( '' === trim( $raw_ids ) ) {
			return array();
		}
		$records = array();
		foreach ( preg_split( '/\s*,\s*/', trim( $raw_ids ) ) as $raw_id ) {
			$attachment_id = absint( $raw_id );
			$attachment    = $attachment_id ? get_post( $attachment_id ) : null;
			$allowed       = $attachment && 'attachment' === $attachment->post_type && ( (int) $attachment->post_author === $actor_id || $this->gateway->can_moderate( $actor_id ) || user_can( $actor_id, 'delete_post', $attachment_id ) );
			if ( ! $allowed ) {
				return new \WP_Error( 'invalid_activity_media', __( 'Invalid or unauthorized media attachment.', 'metafans-core' ), array( 'status' => 403 ) );
			}
			$records[] = array(
				'id'              => strtoupper( wp_generate_password( 10, false, false ) ),
				'thumb'           => wp_get_attachment_image_src( $attachment_id, array( 300, 300 ) ),
				'full'            => wp_get_attachment_url( $attachment_id ),
				'author'          => $actor_id,
				'reactions'       => array( 'likes' => 0, 'love' => 0, 'care' => 0, 'haha' => 0, 'sad' => 0, 'wow' => 0, 'angry' => 0, 'so_what' => 0 ),
				'comments'        => array(),
				'timestamp'       => time(),
				'attachment_id'   => $attachment_id,
				'attachment_type' => get_post_mime_type( $attachment_id ),
			);
		}
		return $records;
	}

	private function send_activity_failure( ActivityResult $result ): void {
		wp_send_json_error( array( 'code' => $result->code(), 'message' => $result->message() ), $result->http_status() );
	}

	private function send_wp_error( \WP_Error $error ): void {
		$data   = $error->get_error_data();
		$status = is_array( $data ) && isset( $data['status'] ) ? absint( $data['status'] ) : 400;
		wp_send_json_error( array( 'code' => $error->get_error_code(), 'message' => $error->get_error_message() ), $status );
	}
}
