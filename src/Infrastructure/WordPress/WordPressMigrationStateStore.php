<?php
namespace METAFANSCORE\Infrastructure\WordPress;
use METAFANSCORE\Application\Upgrade\MigrationStateStore;
defined( 'ABSPATH' ) || exit;
final class WordPressMigrationStateStore implements MigrationStateStore {
	private const STATE_OPTION='metafans_core_migration_state';
	private const LOCK_OPTION='metafans_core_migration_lock';
	private const LOCK_TTL=600;
	public function load(): array { $state=get_option(self::STATE_OPTION,array()); return is_array($state)?$state:array(); }
	public function save( array $state ): void { update_option(self::STATE_OPTION,$state,false); }
	public function acquire_lock(): ?string {
		$now=$this->now(); $token=function_exists('wp_generate_uuid4')?wp_generate_uuid4():uniqid('metafans-',true); $lock=array('token'=>$token,'created_at'=>$now);
		if ( add_option(self::LOCK_OPTION,$lock,'',false) ) return $token;
		$current=get_option(self::LOCK_OPTION,array()); $created=is_array($current)?(int)($current['created_at']??0):0;
		if ( $created>0 && ($now-$created)>self::LOCK_TTL ) { delete_option(self::LOCK_OPTION); if ( add_option(self::LOCK_OPTION,$lock,'',false) ) return $token; }
		return null;
	}
	public function release_lock( string $token ): void { $current=get_option(self::LOCK_OPTION,array()); if ( is_array($current) && hash_equals((string)($current['token']??''),$token) ) delete_option(self::LOCK_OPTION); }
	public function now(): int { return time(); }
}
