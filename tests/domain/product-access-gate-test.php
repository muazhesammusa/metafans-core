<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

require dirname( __DIR__, 2 ) . '/src/Support/ProductAccessGate.php';

use METAFANSCORE\Support\ProductAccessGate;

$failures = array();
$assert = static function ( bool $condition, string $label ) use ( &$failures ): void {
	if ( $condition ) {
		echo "PASS {$label}\n";
		return;
	}
	$failures[] = $label;
};

final class MetaFansGateTestEnforcer {
	public string $boundary = '';

	public function __construct( private readonly bool $allowed ) {}

	public function allowsBoundary( string $boundary ): bool {
		$this->boundary = $boundary;
		return $this->allowed;
	}
}

final class MetaFansGateTestManager {
	public function __construct( private readonly object $enforcer ) {}

	public function runtimeEnforcer(): object {
		return $this->enforcer;
	}
}

final class MetaFansGateThrowingManager {
	public function runtimeEnforcer(): object {
		throw new RuntimeException( 'unavailable' );
	}
}

unset( $GLOBALS['anylicense_license_managers'] );
$assert( ! ProductAccessGate::allows(), 'Core runtime fails closed without generated product authority' );

$GLOBALS['anylicense_license_managers'] = array();
$assert( ! ProductAccessGate::allows(), 'Core runtime fails closed without the MetaFans manager' );

$GLOBALS['anylicense_license_managers']['metafans'] = new MetaFansGateThrowingManager();
$assert( ! ProductAccessGate::allows(), 'Core runtime fails closed when authorization cannot be evaluated' );

$denied = new MetaFansGateTestEnforcer( false );
$GLOBALS['anylicense_license_managers']['metafans'] = new MetaFansGateTestManager( $denied );
$assert( ! ProductAccessGate::allows(), 'Core runtime stays locked when signed product authorization is denied' );
$assert( 'metafans_core_runtime' === $denied->boundary, 'Core runtime uses the stable MetaFans boundary' );

$allowed = new MetaFansGateTestEnforcer( true );
$GLOBALS['anylicense_license_managers']['metafans'] = new MetaFansGateTestManager( $allowed );
$assert( ProductAccessGate::allows(), 'Core runtime unlocks when MetaFans signed authorization is valid' );
$assert( 'metafans_core_runtime' === ProductAccessGate::boundary(), 'Core exposes the stable runtime boundary identifier' );

if ( $failures ) {
	fwrite( STDERR, "MetaFans Core product access gate failed:\n- " . implode( "\n- ", $failures ) . "\n" );
	exit( 1 );
}

echo "MetaFans Core product access gate: PASS\n";
