<?php
namespace METAFANSCORE\Application\Upgrade;
defined( 'ABSPATH' ) || exit;
interface Migration {
	public function id(): string;
	public function target_version(): string;
	public function is_destructive(): bool;
	public function run( ?string $cursor ): MigrationStepResult;
}
