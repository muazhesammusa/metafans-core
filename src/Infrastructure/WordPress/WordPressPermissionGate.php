<?php
/**
 * WordPress cookie-authenticated mutation permission gate.
 *
 * @package MetaFansCore
 */

namespace METAFANSCORE\Infrastructure\WordPress;

use METAFANSCORE\Application\Contracts\PermissionGate;

defined( 'ABSPATH' ) || exit;

final class WordPressPermissionGate implements PermissionGate {
	public function authorize_ajax_mutation( string $action, ?string $capability = 'read' ) {
		if ( ! is_user_logged_in() ) {
			return new \WP_Error( 'authentication_required', __( 'Authentication required.', 'metafans-core' ), array( 'status' => 401 ) );
		}

		$nonce = '';
		if ( isset( $_REQUEST['nonce'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) );
		} elseif ( isset( $_REQUEST['_ajax_nonce'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_ajax_nonce'] ) );
		}

		$nonce_action = (string) apply_filters( 'metafans_core_ajax_nonce_action', 'metafans_mutation_' . sanitize_key( $action ), $action );
		if ( ! $nonce || ! wp_verify_nonce( $nonce, $nonce_action ) ) {
			return new \WP_Error( 'invalid_nonce', __( 'Security check failed.', 'metafans-core' ), array( 'status' => 403 ) );
		}

		if ( $capability && ! current_user_can( $capability ) ) {
			return new \WP_Error( 'permission_denied', __( 'Permission denied.', 'metafans-core' ), array( 'status' => 403 ) );
		}

		return get_current_user_id();
	}
}
