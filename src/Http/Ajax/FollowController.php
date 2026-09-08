<?php
namespace METAFANSCORE\Http\Ajax;
use METAFANSCORE\Application\Contracts\PermissionGate;
use METAFANSCORE\Application\Follow\FollowService;
defined( 'ABSPATH' ) || exit;
final class FollowController {
 public const ACTION='metafans_handle_follow'; private PermissionGate $permissions; private FollowService $service;
 public function __construct(PermissionGate $permissions,FollowService $service){$this->permissions=$permissions;$this->service=$service;}
 public function register():void{add_action('wp_ajax_metafans_handle_follow',array($this,'handle_toggle'));}
 public function handle_toggle():void{$actor=$this->permissions->authorize_ajax_mutation(self::ACTION,'read');if(is_wp_error($actor)){ $d=$actor->get_error_data();wp_send_json_error(array('message'=>$actor->get_error_message()),is_array($d)&&isset($d['status'])?absint($d['status']):400);} $target=isset($_REQUEST['following_id'])?absint($_REQUEST['following_id']):0;$r=$this->service->toggle((int)$actor,$target);if(!$r->is_success())wp_send_json_error(array('code'=>$r->code(),'message'=>$r->message()),$r->http_status());wp_send_json($r->data(),200);}
}
