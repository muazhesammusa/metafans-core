<?php

declare(strict_types=1);

$coreRoot = dirname(__DIR__, 2);
$bootstrap = file_get_contents($coreRoot . '/metafans-core.php') ?: '';
$gate = file_get_contents($coreRoot . '/src/Support/ProductAccessGate.php') ?: '';
$failures = [];

$assert = static function (bool $condition, string $label) use (&$failures): void {
    if ($condition) {
        echo "PASS {$label}\n";
        return;
    }
    $failures[] = $label;
};

$assert(!str_contains($bootstrap, '/updater/theme-updater.php'), 'legacy theme updater bootstrap removed');
$assert(!is_dir($coreRoot . '/updater'), 'legacy theme updater directory removed');
$assert(str_contains($bootstrap, "add_action( 'plugins_loaded', array( MetafansCore::getInstance(), 'bootstrap' ) );"), 'MetaFans Core uses minimal early bootstrap');
$assert(str_contains($bootstrap, "add_action( 'after_setup_theme', array( self::getInstance(), 'init' ), 30 );"), 'MetaFans Core defers protected runtime until the theme authority exists');
$assert(str_contains($bootstrap, 'ProductAccessGate::allows()'), 'MetaFans Core runtime is guarded by the shared product authority');
$assert(str_contains($bootstrap, "require_once __DIR__ . '/MailChimp.php';"), 'authorized runtime still loads MailChimp support');
$assert(str_contains($bootstrap, "require_once __DIR__ . '/src/Legacy/CustomizerModuleRuntime.php';"), 'authorized runtime still loads customizer modules');
$assert(str_contains($gate, "\$GLOBALS['anylicense_license_managers']"), 'Core bridge consumes the generated manager registry');
$assert(str_contains($gate, "private const PRODUCT_SLUG = 'metafans';"), 'Core bridge is bound to the MetaFans product');
$assert(str_contains($gate, 'allowsBoundary( self::BOUNDARY )'), 'Core bridge consumes canonical signed runtime authorization');

if ($failures !== []) {
    fwrite(STDERR, "MetaFans Core license clean baseline failed:\n- " . implode("\n- ", $failures) . "\n");
    exit(1);
}

echo "MetaFans Core license clean baseline: PASS\n";
