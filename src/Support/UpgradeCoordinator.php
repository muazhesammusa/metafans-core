<?php
namespace METAFANSCORE\Support;
use METAFANSCORE\Application\Upgrade\MigrationRunner;
use METAFANSCORE\Console\MigrateCommand;
use METAFANSCORE\Infrastructure\WordPress\WordPressMigrationStateStore;
defined( 'ABSPATH' ) || exit;
final class UpgradeCoordinator {
	private static bool $booted=false;
	public static function boot(): void {
		if ( self::$booted ) return;
		$runner=new MigrationRunner(new WordPressMigrationStateStore(),MigrationRegistry::all());
		ServiceRegistry::set('upgrade.migrations',$runner);
		add_action('admin_init',array(self::class,'maybe_run'));
		if ( defined('WP_CLI') && WP_CLI ) MigrateCommand::register($runner);
		self::$booted=true;
	}
	public static function maybe_run(): void {
		$runner=ServiceRegistry::get('upgrade.migrations');
		if ( ! $runner instanceof MigrationRunner || ! $runner->has_pending() ) return;
		if ( ! current_user_can('update_plugins') ) return;
		$runner->run(5,false);
	}
}
