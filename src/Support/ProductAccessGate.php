<?php

namespace METAFANSCORE\Support;

defined( 'ABSPATH' ) || exit;

/**
 * Fail-closed access boundary for the MetaFans product runtime.
 *
 * MetaFans Core owns no license key, activation state, or remote licensing
 * transport. It consumes the canonical manager generated into the MetaFans
 * theme build and accepts runtime only while its signed authorization is valid.
 */
final class ProductAccessGate {

	private const PRODUCT_SLUG = 'metafans';
	private const BOUNDARY = 'metafans_core_runtime';

	public static function allows(): bool {
		$managers = $GLOBALS['anylicense_license_managers'] ?? null;
		if ( ! is_array( $managers ) ) {
			return false;
		}

		$manager = $managers[ self::PRODUCT_SLUG ] ?? null;
		if ( ! is_object( $manager ) || ! method_exists( $manager, 'runtimeEnforcer' ) ) {
			return false;
		}

		try {
			$enforcer = $manager->runtimeEnforcer();
			return is_object( $enforcer )
				&& method_exists( $enforcer, 'allowsBoundary' )
				&& true === $enforcer->allowsBoundary( self::BOUNDARY );
		} catch ( \Throwable $error ) {
			return false;
		}
	}

	public static function boundary(): string {
		return self::BOUNDARY;
	}
}
