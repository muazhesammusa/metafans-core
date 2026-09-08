<?php
namespace METAFANSCORE\Infrastructure\BuddyPress;
use METAFANSCORE\Application\Contracts\NotificationGateway;
use METAFANSCORE\Application\Contracts\ActivityGateway;
defined( 'ABSPATH' ) || exit;
final class BuddyPressNotificationGateway implements NotificationGateway { private ActivityGateway $activities; public function __construct(ActivityGateway $activities){$this->activities=$activities;} public function activity_comment(int $activity_id,int $actor_id,string $action):void{ if(!function_exists('bp_notifications_add_notification'))return; $activity=$this->activities->find($activity_id); $owner=$activity?(int)$activity->user_id:0; if(!$owner||$owner===$actor_id)return; bp_notifications_add_notification(array('user_id'=>$owner,'item_id'=>$activity_id,'secondary_item_id'=>$actor_id,'component_name'=>'activity','component_action'=>$action,'date_notified'=>function_exists('bp_core_current_time')?bp_core_current_time():current_time('mysql'),'is_new'=>1)); } }
