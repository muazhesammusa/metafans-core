<?php
namespace METAFANSCORE\Support;
use METAFANSCORE\Application\Upgrade\Migration;
defined( 'ABSPATH' ) || exit;
final class MigrationRegistry {
	public static function all(): array {
		$migrations=array();
		if ( function_exists('apply_filters') ) $migrations=apply_filters('metafans_core_migrations',$migrations);
		return array_values(array_filter((array)$migrations,static fn($migration)=>$migration instanceof Migration));
	}
}
