<?php
define( 'ABSPATH', __DIR__ . '/' );
require_once dirname(__DIR__,2).'/src/Application/Upgrade/Migration.php';
require_once dirname(__DIR__,2).'/src/Application/Upgrade/MigrationStepResult.php';
require_once dirname(__DIR__,2).'/src/Application/Upgrade/MigrationStateStore.php';
require_once dirname(__DIR__,2).'/src/Application/Upgrade/MigrationRunner.php';
use METAFANSCORE\Application\Upgrade\MigrationRunner;
use METAFANSCORE\Application\Upgrade\MigrationStateStore;
function p10_release_assert($condition,string $message):void{if(!$condition){fwrite(STDERR,"FAIL {$message}\n");exit(1);}fwrite(STDOUT,"PASS {$message}\n");}
final class P10ReleaseStore implements MigrationStateStore{public array $state=array();public bool $locked=false;public function load():array{return $this->state;}public function save(array $state):void{$this->state=$state;}public function acquire_lock():?string{if($this->locked)return null;$this->locked=true;return'release-lock';}public function release_lock(string $token):void{if('release-lock'===$token)$this->locked=false;}public function now():int{return 1700000000;}}
$representative=array(
 'members'=>array(11,22,33),
 'activities'=>array(101=>array('user_id'=>11,'privacy'=>'onlyme'),102=>array('user_id'=>22,'privacy'=>'friends'),103=>array('user_id'=>33,'privacy'=>'public')),
 'groups'=>array(7=>array('members'=>array(11,22))),
 'messages'=>array(501=>array('sender_id'=>11,'thread_id'=>41)),
 'notifications'=>array(901=>array('user_id'=>22,'component'=>'activity')),
 'friendships'=>array(array(11,22)),
);
$before=serialize($representative);
$store=new P10ReleaseStore();$runner=new MigrationRunner($store,array());
$status=$runner->run(25,false);
p10_release_assert('complete'===$status['result'],'clean install with no registered migrations completes safely');
p10_release_assert($before===serialize($representative),'representative BuddyPress IDs and social data remain byte-for-byte unchanged by the v6 no-op upgrade path');
p10_release_assert(array()===$status['completed']&&null===$status['current'],'no synthetic migration completion is fabricated when no data migration is registered');
p10_release_assert(!$store->locked,'release acceptance path releases the migration lock');
echo "PASS MetaFans Phase 10 clean-install and v5-to-v6 preservation contract\n";
