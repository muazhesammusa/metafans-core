<?php
namespace METAFANSCORE\Application\Upgrade;
defined( 'ABSPATH' ) || exit;
interface MigrationStateStore {
	public function load(): array;
	public function save( array $state ): void;
	public function acquire_lock(): ?string;
	public function release_lock( string $token ): void;
	public function now(): int;
}
