<?php
namespace METAFANSCORE\Console;
use METAFANSCORE\Application\Upgrade\MigrationRunner;
defined( 'ABSPATH' ) || exit;
final class MigrateCommand {
	private static ?MigrationRunner $runner=null;
	public static function register( MigrationRunner $runner ): void { self::$runner=$runner; \WP_CLI::add_command('metafans migrate',self::class); }
	public function status(): void { $this->require_runner(); \WP_CLI::line(wp_json_encode(self::$runner->status(),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)); }
	public function run( array $args, array $assoc_args ): void {
		$this->require_runner(); $steps=isset($assoc_args['steps'])?max(1,(int)$assoc_args['steps']):25;
		$result=self::$runner->run($steps,isset($assoc_args['allow-destructive']));
		\WP_CLI::line(wp_json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
		if ( in_array($result['result']??'',array('failed','locked'),true) ) \WP_CLI::halt(1);
	}
	private function require_runner(): void { if ( ! self::$runner ) \WP_CLI::error('MetaFans migration runner is unavailable.'); }
}
