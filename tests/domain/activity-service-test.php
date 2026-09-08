<?php
/** Pure PHP characterization for Phase 4 activity/privacy services. */
define( 'ABSPATH', __DIR__ . '/' );
function apply_filters( $tag, $value ) { return $value; }

require_once dirname( __DIR__, 2 ) . '/src/Application/Contracts/ActivityGateway.php';
require_once dirname( __DIR__, 2 ) . '/src/Domain/Activity/ActivityResult.php';
require_once dirname( __DIR__, 2 ) . '/src/Application/Activity/ActivityPrivacyService.php';
require_once dirname( __DIR__, 2 ) . '/src/Application/Activity/ActivityQueryService.php';
require_once dirname( __DIR__, 2 ) . '/src/Application/Activity/ActivityMutationService.php';

use METAFANSCORE\Application\Contracts\ActivityGateway;
use METAFANSCORE\Application\Activity\ActivityMutationService;
use METAFANSCORE\Application\Activity\ActivityPrivacyService;
use METAFANSCORE\Application\Activity\ActivityQueryService;

final class ActivityGatewayStub implements ActivityGateway {
	public bool $available = true;
	public array $activities = array();
	public array $meta = array();
	public array $friends = array();
	public array $moderators = array();
	public array $native_denied = array();
	public array $group_members = array();
	public array $calls = array();
	public int $next_id = 500;

	public function is_available(): bool { return $this->available; }
	public function find( int $id ): ?object { return $this->activities[$id] ?? null; }
	public function query( array $args ): array { return array_values( $this->activities ); }
	public function search( string $terms, int $page, int $per_page ): array { return $this->query( array() ); }
	public function native_can_read( object $activity, int $viewer_id ): bool { return ! in_array( (int) $activity->id, $this->native_denied, true ); }
	public function are_friends( int $a, int $b ): bool { return in_array( "$a:$b", $this->friends, true ) || in_array( "$b:$a", $this->friends, true ); }
	public function can_moderate( int $id ): bool { return in_array( $id, $this->moderators, true ); }
	public function is_group_member( int $user_id, int $group_id ): bool { return in_array( "$user_id:$group_id", $this->group_members, true ); }
	public function create_update( int $actor_id, string $content, string $component, int $item_id ): int { $id=$this->next_id++; $this->activities[$id]=(object)array('id'=>$id,'user_id'=>$actor_id,'content'=>$content); $this->calls[]='create'; return $id; }
	public function update_existing( int $id, int $actor_id, string $content, string $component, int $item_id ): int { $this->calls[]='edit'; if(!isset($this->activities[$id])) return 0; $this->activities[$id]->content=$content; return $id; }
	public function delete( int $id ): bool { $this->calls[]='delete'; if(!isset($this->activities[$id])) return false; unset($this->activities[$id]); return true; }
	public function get_meta( int $id, string $key ) { return $this->meta[$id][$key] ?? ''; }
	public function update_meta( int $id, string $key, $value ): bool { $this->meta[$id][$key]=$value; return true; }
}
function phase4_assert( $condition, string $message ): void { if(!$condition){fwrite(STDERR,"FAIL $message\n");exit(1);} fwrite(STDOUT,"PASS $message\n"); }

$gateway = new ActivityGatewayStub();
$gateway->activities[100]=(object)array('id'=>100,'user_id'=>10,'content'=>'hello');
$gateway->activities[101]=(object)array('id'=>101,'user_id'=>11,'content'=>'private');
$gateway->activities[102]=(object)array('id'=>102,'user_id'=>12,'content'=>'friends');
$gateway->meta[100]['activity_accessibility']='onlyme';
$gateway->meta[101]['activity_accessibility']='public';
$gateway->meta[102]['activity_accessibility']='friends';
$privacy = new ActivityPrivacyService($gateway);
$query = new ActivityQueryService($gateway,$privacy);
$mutations = new ActivityMutationService($gateway,$privacy);

phase4_assert($privacy->can_view(100,10),'owner sees only-me activity');
phase4_assert(!$privacy->can_view(100,20),'other user cannot see only-me activity');
$gateway->moderators=array(99);
phase4_assert($privacy->can_view(100,99) && $privacy->can_edit(100,99),'moderator can view/edit activity');
$gateway->moderators=array();
$gateway->friends=array('20:12');
phase4_assert($privacy->can_view(102,20),'friend sees friends-only activity');
$gateway->friends=array();
phase4_assert(!$privacy->can_view(102,20),'non-friend cannot see friends-only activity');
$gateway->native_denied=array(101);
phase4_assert(!$privacy->can_view(101,20),'BuddyPress native privacy denial wins');
$gateway->native_denied=array();
$visible=$query->search_visible('anything',10,0,20);
phase4_assert(count($visible)===1 && (int)$visible[0]->id===101,'search returns only viewer-visible rows');

$result=$mutations->upsert(10,array('activity_id'=>100,'content'=>'edited','visibility'=>'public'));
phase4_assert($result->is_success() && $gateway->activities[100]->content==='edited','owner edits through activity service');
$result=$mutations->upsert(20,array('activity_id'=>100,'content'=>'attack'));
phase4_assert(!$result->is_success() && 'activity_edit_forbidden'===$result->code(),'non-owner edit rejected');
$result=$mutations->upsert(20,array('content'=>'group','component'=>'groups','item_id'=>77));
phase4_assert(!$result->is_success() && 'group_post_forbidden'===$result->code(),'group post requires membership');
$gateway->group_members=array('20:77');
$result=$mutations->upsert(20,array('content'=>'group','component'=>'groups','item_id'=>77,'visibility'=>'friends'));
phase4_assert($result->is_success() && 'friends'===$gateway->meta[$result->activity_id()]['activity_accessibility'],'group member creates activity with privacy meta');
$result=$mutations->update_visibility(10,100,'friends');
phase4_assert($result->is_success() && 'friends'===$gateway->meta[100]['activity_accessibility'],'owner updates activity visibility');
$result=$mutations->delete(20,100);
phase4_assert(!$result->is_success(),'non-owner delete rejected');
$result=$mutations->delete(10,100);
phase4_assert($result->is_success() && !isset($gateway->activities[100]),'owner deletes through activity service');

echo "PASS MetaFans Phase 4 activity/privacy service contract\n";
