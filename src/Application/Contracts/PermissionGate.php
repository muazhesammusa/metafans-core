<?php
/**
 * Authenticated request permission contract.
 *
 * @package MetaFansCore
 */

namespace METAFANSCORE\Application\Contracts;

defined( 'ABSPATH' ) || exit;

interface PermissionGate {
	/**
	 * Resolve the authenticated actor for one AJAX mutation.
	 *
	 * @return int|\WP_Error
	 */
	public function authorize_ajax_mutation( string $action, ?string $capability = 'read' );
}
