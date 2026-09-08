<?php
namespace METAFANSCORE\Infrastructure\WordPress;
use METAFANSCORE\Application\Contracts\FollowGateway;
defined( 'ABSPATH' ) || exit;
final class WordPressFollowGateway implements FollowGateway {
 public function user_exists(int $user_id):bool{return (bool)get_userdata($user_id);} private function ids($v):array{return is_array($v)?array_values(array_unique(array_filter(array_map('absint',$v)))):array();}
 public function followers(int $user_id):array{return $this->ids(get_user_meta($user_id,'followers',true));} public function following(int $user_id):array{return $this->ids(get_user_meta($user_id,'following',true));}
 public function persist_followers(int $user_id,array $followers):bool{return false!==update_user_meta($user_id,'followers',$this->ids($followers));} public function persist_following(int $user_id,array $following):bool{return false!==update_user_meta($user_id,'following',$this->ids($following));}
 public function with_lock(int $actor_id,int $target_id,callable $callback){$ids=array($actor_id,$target_id);sort($ids);$key='_metafans_follow_lock_'.md5(implode(':',$ids));$deadline=microtime(true)+2.0;do{if(add_option($key,time(),'',false)){try{return $callback();}finally{delete_option($key);}}$created=(int)get_option($key,0);if($created&&$created<time()-10){delete_option($key);continue;}usleep(20000);}while(microtime(true)<$deadline);return null;}
}
