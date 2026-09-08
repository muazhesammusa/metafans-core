<?php
/**
 * MetaFans v6 domain foundation bootstrap.
 *
 * @package MetaFansCore
 */

namespace METAFANSCORE\Support;

use METAFANSCORE\Application\Friendship\FriendshipService;
use METAFANSCORE\Http\Ajax\FriendshipController;
use METAFANSCORE\Infrastructure\BuddyPress\BuddyPressFriendshipGateway;
use METAFANSCORE\Infrastructure\WordPress\WordPressPermissionGate;

defined( 'ABSPATH' ) || exit;

final class DomainFoundation {
	private static bool $booted = false;

	public static function boot(): void {
		if ( self::$booted ) {
			return;
		}

		$permissions = new WordPressPermissionGate();
		$friendships = new BuddyPressFriendshipGateway();
		$service     = new FriendshipService( $friendships );
		$controller  = new FriendshipController( $permissions, $service );

		ServiceRegistry::set( 'permissions.ajax', $permissions );
		ServiceRegistry::set( 'friendship.gateway', $friendships );
		ServiceRegistry::set( 'friendship.service', $service );
		ServiceRegistry::set( 'friendship.ajax_controller', $controller );

		$controller->register();
		self::$booted = true;

		do_action( 'metafans_core_domain_foundation_booted', ServiceRegistry::class );
	}
}
