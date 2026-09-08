<?php
namespace METAFANSCORE\Http\Ajax;
use METAFANSCORE\Application\Contracts\PermissionGate;
use METAFANSCORE\Application\Interaction\InteractionService;
use METAFANSCORE\Domain\Social\MutationResult;
defined( 'ABSPATH' ) || exit;
final class InteractionController {
 private PermissionGate $permissions; private InteractionService $service;
 public function __construct(PermissionGate $permissions,InteractionService $service){$this->permissions=$permissions;$this->service=$service;}
 public function register():void{
  add_action( 'wp_ajax_tophive_bp_activity_comment', array( $this, 'handle_comment' ) );
  add_action( 'wp_ajax_comment_react', array( $this, 'handle_comment_reaction' ) );
  add_action( 'wp_ajax_tophive_bp_delete_comment', array( $this, 'handle_comment_delete' ) );
  add_action( 'wp_ajax_th_bp_activity_reaction', array( $this, 'handle_activity_reaction' ) );
  add_action( 'wp_ajax_th_bp_media_comments_post', array( $this, 'handle_media_comment' ) );
  add_action( 'wp_ajax_th_bp_media_reaction', array( $this, 'handle_media_reaction' ) );
 }
 private function actor(string $action):int{$a=$this->permissions->authorize_ajax_mutation($action,'read');if(is_wp_error($a)){ $d=$a->get_error_data();wp_send_json_error(array('message'=>$a->get_error_message()),is_array($d)&&isset($d['status'])?absint($d['status']):400);}return (int)$a;}
 private function aid():int{return isset($_POST['activity_id'])?absint($_POST['activity_id']):0;} private function fail(MutationResult $r):void{wp_send_json_error(array('code'=>$r->code(),'message'=>$r->message()),$r->http_status());}
 public function handle_comment():void{$actor=$this->actor('tophive_bp_activity_comment');$aid=$this->aid();$text=isset($_POST['comment_text'])?wp_kses_post(wp_unslash($_POST['comment_text'])):'';$type=isset($_POST['type'])?sanitize_key(wp_unslash($_POST['type'])):'';$cid=isset($_POST['comment_id'])?sanitize_text_field(wp_unslash($_POST['comment_id'])):'';$open=isset($_POST['cur_num_of_open_cmnt'])?max(1,absint($_POST['cur_num_of_open_cmnt'])):1;$profile=array('name'=>get_the_author_meta('display_name',$actor),'url'=>get_the_author_meta('user_url',$actor),'avatar'=>get_avatar_url($actor));$text=(string)apply_filters('metafans_core_prepare_comment_content',$text,$actor,$aid);$r='postcommentreply'===$type?$this->service->add_reply($actor,$aid,$cid,$text):('postcomment'===$type?$this->service->add_comment($actor,$aid,$text,$profile):MutationResult::failure('invalid_comment_action',__('Invalid comment action.','metafans-core'),400));if(!$r->is_success())$this->fail($r);$payload=apply_filters('metafans_core_comment_response',array(),$aid,$open,'add');wp_send_json($payload,200);}
 public function handle_comment_delete():void{$actor=$this->actor('tophive_bp_delete_comment');$aid=$this->aid();$cid=isset($_POST['comment_id'])?sanitize_text_field(wp_unslash($_POST['comment_id'])):'';$reply=isset($_POST['reply_id'])&&''!==$_POST['reply_id']?absint($_POST['reply_id']):null;$r=$this->service->delete_comment($actor,$aid,$cid,$reply);if(!$r->is_success())$this->fail($r);wp_send_json(apply_filters('metafans_core_comment_response',array(),$aid,1,'delete'),200);}
 public function handle_comment_reaction():void{$actor=$this->actor('comment_react');$aid=$this->aid();$cid=isset($_POST['comment_id'])?sanitize_text_field(wp_unslash($_POST['comment_id'])):'';$type=isset($_POST['reaction_type'])?sanitize_key(wp_unslash($_POST['reaction_type'])):'';$reply=isset($_POST['reply_index'])&&''!==$_POST['reply_index']?absint($_POST['reply_index']):null;$open=isset($_POST['cur_num_of_open_cmnt'])?max(1,absint($_POST['cur_num_of_open_cmnt'])):1;$r=$this->service->react_comment($actor,$aid,$cid,$type,$reply);if(!$r->is_success())$this->fail($r);$payload=apply_filters('metafans_core_comment_response',array(),$aid,$open,'reaction');wp_send_json_success($payload);}
 public function handle_activity_reaction():void{$actor=$this->actor('th_bp_activity_reaction');$aid=$this->aid();$type=isset($_POST['reaction_type'])?sanitize_key(wp_unslash($_POST['reaction_type'])):'';$r=$this->service->react_activity($actor,$aid,$type);if(!$r->is_success())$this->fail($r);wp_send_json((string)apply_filters('metafans_core_activity_reactions_render_html','',$aid),200);}
 public function handle_media_comment():void{$actor=$this->actor('th_bp_media_comments_post');$aid=$this->aid();$mid=isset($_POST['media_id'])?sanitize_text_field(wp_unslash($_POST['media_id'])):'';$text=isset($_POST['comment_text'])?sanitize_textarea_field(wp_unslash($_POST['comment_text'])):'';$r=$this->service->add_media_comment($actor,$aid,$mid,$text);if(!$r->is_success())$this->fail($r);wp_send_json(true,200);}
 public function handle_media_reaction():void{$actor=$this->actor('th_bp_media_reaction');$aid=$this->aid();$mid=isset($_POST['media_id'])?sanitize_text_field(wp_unslash($_POST['media_id'])):'';$type=isset($_POST['reaction_type'])?sanitize_key(wp_unslash($_POST['reaction_type'])):'';$r=$this->service->react_media($actor,$aid,$mid,$type);if(!$r->is_success())$this->fail($r);$html=(string)apply_filters('metafans_core_media_meta_render_html','',$mid,$aid,!empty($r->data()['active'])?'active':'');wp_send_json($html,200);}
}
