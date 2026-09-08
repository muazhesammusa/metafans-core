<?php
namespace METAFANSCORE\Http\Ajax;
use METAFANSCORE\Application\Contracts\PermissionGate;
use METAFANSCORE\Application\Media\MediaService;
use METAFANSCORE\Domain\Social\MutationResult;
defined( 'ABSPATH' ) || exit;
final class MediaController {
 public const UPLOAD_ACTION='activity_upload'; public const DELETE_ACTION='th_bp_remove_media'; public const QUILL_ACTION='upload_quill_image';
 private PermissionGate $permissions; private MediaService $media;
 public function __construct(PermissionGate $permissions,MediaService $media){$this->permissions=$permissions;$this->media=$media;}
 public function register():void{add_action('wp_ajax_activity_upload',array($this,'handle_upload'));add_action('wp_ajax_th_bp_remove_media',array($this,'handle_delete'));add_action('wp_ajax_upload_quill_image',array($this,'handle_quill_upload'));}
 public function handle_upload():void{$actor=$this->authorize(self::UPLOAD_ACTION);if(!apply_filters('metafans_user_can_upload_activity_media',true,$actor))wp_send_json_error(array('message'=>__('You are not allowed to upload community media.','metafans-core')),403);$result=$this->media->upload($actor,'upload_file',false);if(!$result->is_success())$this->fail($result);$data=$result->data();$data=apply_filters('metafans_core_media_upload_response',$data,$result);wp_send_json($data,200);}
 public function handle_quill_upload():void{$actor=$this->authorize(self::QUILL_ACTION);$result=$this->media->upload($actor,'file',true);if(!$result->is_success())$this->fail($result);wp_send_json_success(array('url'=>$result->data()['url']??'','id'=>$result->data()['id']??0));}
 public function handle_delete():void{$actor=$this->authorize(self::DELETE_ACTION);$id=isset($_POST['att_id'])?absint($_POST['att_id']):0;$result=$this->media->delete($actor,$id);if(!$result->is_success())$this->fail($result);wp_send_json_success($result->data());}
 private function authorize(string $action):int{$actor=$this->permissions->authorize_ajax_mutation($action,'read');if(is_wp_error($actor)){ $data=$actor->get_error_data(); wp_send_json_error(array('code'=>$actor->get_error_code(),'message'=>$actor->get_error_message()),is_array($data)&&isset($data['status'])?absint($data['status']):400);}return (int)$actor;}
 private function fail(MutationResult $r):void{wp_send_json_error(array('code'=>$r->code(),'message'=>$r->message()),$r->http_status());}
}
