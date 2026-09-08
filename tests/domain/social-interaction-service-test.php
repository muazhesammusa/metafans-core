<?php
/** Pure PHP characterization for Phase 5 social interaction services. */
define( 'ABSPATH', __DIR__ . '/' );
function __( $text ) { return $text; }
function apply_filters( $tag, $value ) { return $value; }
function wp_strip_all_tags( $text ) { return strip_tags( $text ); }
function wp_generate_password( $length = 12 ) { return str_repeat( 'A', $length ); }
function absint( $value ) { return abs( (int) $value ); }
class WP_Error { private string $code; private string $message; public function __construct($c,$m){$this->code=$c;$this->message=$m;} public function get_error_code(){return $this->code;} public function get_error_message(){return $this->message;} }
function is_wp_error( $value ) { return $value instanceof WP_Error; }

require_once dirname( __DIR__, 2 ) . '/src/Application/Contracts/ActivityGateway.php';
require_once dirname( __DIR__, 2 ) . '/src/Application/Contracts/MediaGateway.php';
require_once dirname( __DIR__, 2 ) . '/src/Application/Contracts/FollowGateway.php';
require_once dirname( __DIR__, 2 ) . '/src/Application/Contracts/NotificationGateway.php';
require_once dirname( __DIR__, 2 ) . '/src/Domain/Social/MutationResult.php';
require_once dirname( __DIR__, 2 ) . '/src/Application/Activity/ActivityPrivacyService.php';
require_once dirname( __DIR__, 2 ) . '/src/Application/Media/MediaService.php';
require_once dirname( __DIR__, 2 ) . '/src/Application/Follow/FollowService.php';
require_once dirname( __DIR__, 2 ) . '/src/Application/Interaction/InteractionService.php';

use METAFANSCORE\Application\Contracts\ActivityGateway;
use METAFANSCORE\Application\Contracts\MediaGateway;
use METAFANSCORE\Application\Contracts\FollowGateway;
use METAFANSCORE\Application\Contracts\NotificationGateway;
use METAFANSCORE\Application\Activity\ActivityPrivacyService;
use METAFANSCORE\Application\Media\MediaService;
use METAFANSCORE\Application\Follow\FollowService;
use METAFANSCORE\Application\Interaction\InteractionService;

function p5_assert( $condition, string $message ): void { if ( ! $condition ) { fwrite( STDERR, "FAIL $message\n" ); exit( 1 ); } fwrite( STDOUT, "PASS $message\n" ); }

final class P5ActivityGateway implements ActivityGateway {
    public array $activities = array(); public array $meta = array(); public array $moderators=array(); public array $friends=array(); public bool $lock_available=true; public array $lock_scopes=array();
    public function is_available(): bool { return true; }
    public function find(int $id): ?object { return $this->activities[$id]??null; }
    public function query(array $args): array { return array_values($this->activities); }
    public function search(string $terms,int $page,int $per_page): array { return $this->query(array()); }
    public function native_can_read(object $activity,int $viewer_id): bool { return true; }
    public function are_friends(int $a,int $b): bool { return in_array("$a:$b",$this->friends,true)||in_array("$b:$a",$this->friends,true); }
    public function can_moderate(int $id): bool { return in_array($id,$this->moderators,true); }
    public function is_group_member(int $user_id,int $group_id): bool { return true; }
    public function create_update(int $actor_id,string $content,string $component,int $item_id): int { return 0; }
    public function update_existing(int $id,int $actor_id,string $content,string $component,int $item_id): int { return 0; }
    public function delete(int $id): bool { return false; }
    public function get_meta(int $id,string $key) { return $this->meta[$id][$key]??''; }
    public function update_meta(int $id,string $key,$value): bool { $this->meta[$id][$key]=$value; return true; }
    public function with_lock(int $id,string $scope,callable $callback){$this->lock_scopes[]=$scope;return $this->lock_available?$callback():null;}
}
final class P5Notifications implements NotificationGateway { public array $calls=array(); public function activity_comment(int $activity_id,int $actor_id,string $action):void{$this->calls[]=array($activity_id,$actor_id,$action);} }
final class P5FollowGateway implements FollowGateway { public array $users=array(10=>1,20=>1,30=>1); public array $followers=array(); public array $following=array(); public bool $fail_following=false; public bool $lock_available=true; public int $lock_calls=0; public function user_exists(int $id):bool{return isset($this->users[$id]);} public function followers(int $id):array{return $this->followers[$id]??array();} public function following(int $id):array{return $this->following[$id]??array();} public function persist_followers(int $id,array $v):bool{$this->followers[$id]=$v;return true;} public function persist_following(int $id,array $v):bool{if($this->fail_following)return false;$this->following[$id]=$v;return true;} public function with_lock(int $actor,int $target,callable $callback){$this->lock_calls++;return $this->lock_available?$callback():null;} }
final class P5MediaGateway implements MediaGateway { public bool $allow_delete=true; public bool $deleted=false; public array $attached=array(); public int $cleaned=0; public function upload(int $actor,string $field,bool $images=false){return array('id'=>55,'url'=>'https://example.test/a.jpg','mime'=>'image/jpeg','filename'=>'a');} public function can_delete(int $actor,int $id):bool{return $this->allow_delete;} public function delete(int $id):bool{$this->deleted=true;return true;} public function mark_attached(int $id,int $actor,int $activity):void{$this->attached[]=array($id,$actor,$activity);} public function cleanup_orphans(int $older_than,int $limit=50):int{$this->cleaned=2;return 2;} }

$activities=new P5ActivityGateway();
$activities->activities[100]=(object)array('id'=>100,'user_id'=>10);
$activities->meta[100]['activity_accessibility']='public';
$privacy=new ActivityPrivacyService($activities); $notifications=new P5Notifications(); $social=new InteractionService($activities,$privacy,$notifications);

$r=$social->add_comment(20,100,'hello',array('name'=>'Twenty','url'=>'','avatar'=>''));
p5_assert($r->is_success() && count($activities->meta[100]['tophive_activity_comments'])===1,'comment persists through Core service');
$comment_id=$activities->meta[100]['tophive_activity_comments'][0]['ID'];
p5_assert(count($notifications->calls)===1,'comment notification dispatched through gateway');
$r=$social->add_reply(30,100,$comment_id,'reply');
p5_assert($r->is_success() && count($activities->meta[100]['tophive_activity_comments'][0]['replies'])===1,'reply persists through Core service');
$r=$social->react_comment(20,100,$comment_id,'like'); p5_assert($r->is_success() && $activities->meta[100]['tophive_activity_comments'][0]['reactions']['like']['count']===1,'comment reaction add is normalized');
$r=$social->react_comment(20,100,$comment_id,'like'); p5_assert($r->is_success() && $activities->meta[100]['tophive_activity_comments'][0]['reactions']['like']['count']===0,'comment same-reaction toggles off');
$r=$social->delete_comment(30,100,$comment_id,null); p5_assert(!$r->is_success() && $r->http_status()===403,'non-owner cannot delete top-level comment');
$r=$social->delete_comment(10,100,$comment_id,null); p5_assert($r->is_success() && empty($activities->meta[100]['tophive_activity_comments']),'activity owner can moderate comment');

$r=$social->react_activity(20,100,'like'); p5_assert($r->is_success() && $activities->meta[100]['tophive_activity_reactions']['like']['count']===1,'activity reaction add persists');
$r=$social->react_activity(20,100,'like'); p5_assert($r->is_success() && $activities->meta[100]['tophive_activity_reactions']['like']['count']===1,'same activity reaction remains a no-op for legacy contract');
$r=$social->react_activity(20,100,'decrement'); p5_assert($r->is_success() && $activities->meta[100]['tophive_activity_reactions']['like']['count']===0,'activity decrement removes current reaction');

$activities->meta[100]['activity_media']=array(array('id'=>'MEDIA1','comments'=>array(),'reactions'=>array('likes'=>array('count'=>0,'people_reacted'=>array()))));
$r=$social->add_media_comment(20,100,'MEDIA1','media comment'); p5_assert($r->is_success() && count($activities->meta[100]['activity_media'][0]['comments'])===1,'media comment persists through Core service');
$r=$social->react_media(20,100,'MEDIA1','like'); p5_assert($r->is_success() && $r->data()['active'] && $activities->meta[100]['activity_media'][0]['reactions']['likes']['count']===1,'media reaction toggles on');
$r=$social->react_media(20,100,'MEDIA1','like'); p5_assert($r->is_success() && !$r->data()['active'] && $activities->meta[100]['activity_media'][0]['reactions']['likes']['count']===0,'media reaction toggles off idempotently');
p5_assert(in_array('comments',$activities->lock_scopes,true) && in_array('comment_reaction',$activities->lock_scopes,true) && in_array('activity_reaction',$activities->lock_scopes,true) && in_array('media_comments',$activities->lock_scopes,true) && in_array('media_reaction',$activities->lock_scopes,true),'interaction meta mutations run inside activity-scoped locks');
$activities->lock_available=false; $r=$social->react_activity(20,100,'like'); p5_assert(!$r->is_success() && $r->http_status()===409,'reaction fails closed when activity lock is busy'); $activities->lock_available=true;

$r=$social->react_activity(20,100,'invalid'); p5_assert(!$r->is_success() && $r->http_status()===400,'unknown activity reaction is rejected');
$r=$social->react_comment(20,100,'missing','invalid'); p5_assert(!$r->is_success() && $r->http_status()===400,'unknown comment reaction is rejected before persistence');
$r=$social->add_media_comment(20,100,'MEDIA1','   '); p5_assert(!$r->is_success() && $r->http_status()===400,'empty media comment is rejected');
$r=$social->react_media(20,100,'MEDIA1','invalid'); p5_assert(!$r->is_success() && $r->http_status()===400,'unknown media reaction is rejected');

$fg=new P5FollowGateway(); $follow=new FollowService($fg);
$r=$follow->toggle(10,20); p5_assert($r->is_success() && $r->data()['is_following'] && $fg->followers[20]===array(10) && $fg->following[10]===array(20),'follow creates synchronized unique relation');
p5_assert($fg->lock_calls===1,'follow mutation runs inside relationship lock');
$r=$follow->toggle(10,20); p5_assert($r->is_success() && !$r->data()['is_following'] && $fg->followers[20]===array() && $fg->following[10]===array(),'follow toggle removes both directions');
p5_assert(!$follow->toggle(10,10)->is_success(),'self-follow is rejected');
$fg->lock_available=false; $r=$follow->toggle(10,20); p5_assert(!$r->is_success() && $r->http_status()===409,'follow fails closed when relationship lock is busy'); $fg->lock_available=true;
$fg->followers[20]=array(10); $fg->following[10]=array(); $fg->fail_following=true; $r=$follow->toggle(10,20); p5_assert(!$r->is_success() && $fg->followers[20]===array(10),'follow partial write rolls back first direction');

$mg=new P5MediaGateway(); $media=new MediaService($mg); $r=$media->upload(10,'upload_file'); p5_assert($r->is_success() && $r->data()['id']===55,'media upload flows through Core media gateway'); $media->mark_attached(55,10,100); p5_assert($mg->attached===array(array(55,10,100)),'media lifecycle marks persisted attachment as attached'); p5_assert($media->cleanup_orphans(86400,50)===2,'staged orphan cleanup is bounded through media gateway');
$mg->allow_delete=false; p5_assert(!$media->delete(20,55)->is_success(),'media delete denies unauthorized actor'); $mg->allow_delete=true; p5_assert($media->delete(10,55)->is_success() && $mg->deleted,'media delete flows through Core media gateway');

echo "PASS MetaFans Phase 5 social interaction service contract\n";
