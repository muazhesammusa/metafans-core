<?php
/**
 * AJAX controller for the legacy MetaFans friendship action.
 *
 * @package MetaFansCore
 */

namespace METAFANSCORE\Http\Ajax;

use METAFANSCORE\Application\Contracts\PermissionGate;
use METAFANSCORE\Application\Friendship\FriendshipService;
use METAFANSCORE\Domain\Friendship\FriendshipResult;

defined( 'ABSPATH' ) || exit;

final class FriendshipController {
	public const ACTION = 'tophive_bp_friends_action';

	private PermissionGate $permissions;
	private FriendshipService $service;

	public function __construct( PermissionGate $permissions, FriendshipService $service ) {
		$this->permissions = $permissions;
		$this->service     = $service;
	}

	public function register(): void {
		add_action( 'wp_ajax_tophive_bp_friends_action', array( $this, 'handle' ) );
	}

	public function handle(): void {
		$actor_id = $this->permissions->authorize_ajax_mutation( self::ACTION, 'read' );
		if ( is_wp_error( $actor_id ) ) {
			$this->send_error( $actor_id );
		}

		$target_id = isset( $_REQUEST['user_id'] ) ? absint( $_REQUEST['user_id'] ) : 0;
		$result    = $this->service->toggle( (int) $actor_id, $target_id );

		if ( ! $result->is_success() ) {
			wp_send_json_error(
				array(
					'code'    => $result->code(),
					'message' => $result->message(),
				),
				$result->http_status()
			);
		}

		$response = apply_filters( 'metafans_core_friendship_ajax_response', $result->to_array(), $result );
		wp_send_json( $response, 200 );
	}

	private function send_error( \WP_Error $error ): void {
		$data   = $error->get_error_data();
		$status = is_array( $data ) && isset( $data['status'] ) ? absint( $data['status'] ) : 400;
		wp_send_json_error(
			array(
				'code'    => $error->get_error_code(),
				'message' => $error->get_error_message(),
			),
			$status
		);
	}
}
