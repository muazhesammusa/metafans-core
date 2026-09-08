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
use METAFANSCORE\Application\Follow\FollowService;
use METAFANSCORE\Application\Interaction\InteractionService;
use METAFANSCORE\Application\Media\MediaService;
use METAFANSCORE\Http\Ajax\ActivityController;
use METAFANSCORE\Http\Ajax\FriendshipController;
use METAFANSCORE\Http\Ajax\FollowController;
use METAFANSCORE\Http\Ajax\InteractionController;
use METAFANSCORE\Http\Ajax\MediaController;
use METAFANSCORE\Infrastructure\BuddyPress\BuddyPressActivityGateway;
use METAFANSCORE\Infrastructure\BuddyPress\BuddyPressFriendshipGateway;
use METAFANSCORE\Infrastructure\BuddyPress\BuddyPressNotificationGateway;
use METAFANSCORE\Infrastructure\WordPress\WordPressPermissionGate;
use METAFANSCORE\Infrastructure\WordPress\WordPressFollowGateway;
use METAFANSCORE\Infrastructure\WordPress\WordPressMediaGateway;

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

		$media_gateway = new WordPressMediaGateway();
		$media_service = new MediaService( $media_gateway );
		$media_controller = new MediaController( $permissions, $media_service );
		add_action( 'metafans_core_activity_media_attached', array( $media_service, 'mark_attached' ), 10, 3 );
		add_action( 'wp_scheduled_delete', static function () use ( $media_service ) { $media_service->cleanup_orphans(); } );
		$notifications = new BuddyPressNotificationGateway( $activities );
		$interactions = new InteractionService( $activities, $activity_privacy, $notifications );
		$interaction_controller = new InteractionController( $permissions, $interactions );
		$follow_gateway = new WordPressFollowGateway();
		$follow_service = new FollowService( $follow_gateway );
		$follow_controller = new FollowController( $permissions, $follow_service );

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
		ServiceRegistry::set( 'media.gateway', $media_gateway );
		ServiceRegistry::set( 'media.service', $media_service );
		ServiceRegistry::set( 'media.ajax_controller', $media_controller );
		ServiceRegistry::set( 'interaction.service', $interactions );
		ServiceRegistry::set( 'interaction.ajax_controller', $interaction_controller );
		ServiceRegistry::set( 'follow.gateway', $follow_gateway );
		ServiceRegistry::set( 'follow.service', $follow_service );
		ServiceRegistry::set( 'follow.ajax_controller', $follow_controller );

		$friend_controller->register();
		$activity_controller->register();
		$media_controller->register();
		$interaction_controller->register();
		$follow_controller->register();
		self::$booted = true;

		do_action( 'metafans_core_domain_foundation_booted', ServiceRegistry::class );
	}
}
