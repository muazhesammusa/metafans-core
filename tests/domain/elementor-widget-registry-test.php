<?php

define( 'ABSPATH', __DIR__ );
define( 'WP_MF_CORE_SLUG', 'metafanscore' );

$GLOBALS['mf_phase7_actions'] = array();
function add_action( $hook, $callback ) {
	$GLOBALS['mf_phase7_actions'][] = $hook;
}
function esc_html__( $text, $domain = null ) {
	return $text;
}

require_once dirname( __DIR__, 2 ) . '/src/Elementor/WidgetRegistry.php';

use METAFANSCORE\Elementor\WidgetRegistry;

function mf_phase7_assert( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL {$message}\n" );
		exit( 1 );
	}
	fwrite( STDOUT, "PASS {$message}\n" );
}

WidgetRegistry::boot();
WidgetRegistry::boot();

mf_phase7_assert(
	$GLOBALS['mf_phase7_actions'] === array( 'elementor/widgets/register', 'elementor/elements/categories_registered' ),
	'Elementor registry boot is idempotent and registers current lifecycle hooks'
);

$manager = new class() {
	public $registered = array();
	public function register( $widget ) {
		$this->registered[] = $widget;
	}
};
WidgetRegistry::register_widgets( $manager );
mf_phase7_assert( 0 === count( $manager->registered ), 'Elementor-inactive registration fails closed without loading widget classes' );

$categories = new class() {
	public $categories = array();
	public function add_category( $slug, $args ) {
		$this->categories[ $slug ] = $args;
	}
};
WidgetRegistry::register_category( $categories );
mf_phase7_assert( isset( $categories->categories['metafanscore'] ), 'Elementor category registration remains available through registry' );

fwrite( STDOUT, "PASS MetaFans Phase 7 Elementor registry contract\n" );
