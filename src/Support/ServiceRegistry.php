<?php
/**
 * Lightweight service registry for incremental v6 migration.
 *
 * @package MetaFansCore
 */

namespace METAFANSCORE\Support;

defined( 'ABSPATH' ) || exit;

final class ServiceRegistry {
	private static array $services = array();

	public static function set( string $id, object $service ): void {
		self::$services[ $id ] = $service;
	}

	public static function get( string $id ): ?object {
		return self::$services[ $id ] ?? null;
	}

	public static function has( string $id ): bool {
		return isset( self::$services[ $id ] );
	}

	public static function reset(): void {
		self::$services = array();
	}
}
