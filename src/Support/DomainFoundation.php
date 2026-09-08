<?php
/**
 * MetaFans v6 domain foundation bootstrap.
 *
 * @package MetaFansCore
 */

namespace METAFANSCORE\Support;

use METAFANSCORE\Application\Activity\ActivityContentPreparer;
use METAFANSCORE\Application\Activity\ActivityMutationService;
use METAFANSCORE\Application\Activity\ActivityPrivacyService;
use METAFANSCORE\Application\Activity\ActivityQueryService;
use METAFANSCORE\Application\Friendship\FriendshipService;
use METAFANSCORE\Http\Ajax\ActivityController;
use METAFANSCORE\Http\Ajax\FriendshipController;
use METAFANSCORE\Infrastructure\BuddyPress\BuddyPressActivityGateway;
use METAFANSCORE\Infrastructure\BuddyPress\BuddyPressFriendshipGateway;
use METAFANSCORE\Infrastructure\WordPress\WordPressPermissionGate;

defined( 'ABSPATH' ) || exit;

final class DomainFoundation {
	private static bool $booted = false;

	public static function boot(): void {
		if ( self::$booted ) {
			return;
		}

		$permissions      = new WordPressPermissionGate();
		$friendships      = new BuddyPressFriendshipGateway();
		$friendship       = new FriendshipService( $friendships );
		$friend_controller = new FriendshipController( $permissions, $friendship );

		$activities       = new BuddyPressActivityGateway();
		$activity_privacy = new ActivityPrivacyService( $activities );
		$activity_queries = new ActivityQueryService( $activities, $activity_privacy );
		$activity_mutations = new ActivityMutationService( $activities, $activity_privacy );
		$activity_content = new ActivityContentPreparer();
		$activity_controller = new ActivityController( $permissions, $activities, $activity_privacy, $activity_mutations, $activity_content );

		ServiceRegistry::set( 'permissions.ajax', $permissions );
		ServiceRegistry::set( 'friendship.gateway', $friendships );
		ServiceRegistry::set( 'friendship.service', $friendship );
		ServiceRegistry::set( 'friendship.ajax_controller', $friend_controller );
		ServiceRegistry::set( 'activity.gateway', $activities );
		ServiceRegistry::set( 'activity.privacy', $activity_privacy );
		ServiceRegistry::set( 'activity.query', $activity_queries );
		ServiceRegistry::set( 'activity.mutation', $activity_mutations );
		ServiceRegistry::set( 'activity.content', $activity_content );
		ServiceRegistry::set( 'activity.ajax_controller', $activity_controller );

		$friend_controller->register();
		$activity_controller->register();
		self::$booted = true;

		do_action( 'metafans_core_domain_foundation_booted', ServiceRegistry::class );
	}
}
