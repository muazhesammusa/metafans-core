<?php
namespace METAFANSCORE\Application\Upgrade;
defined( 'ABSPATH' ) || exit;
final class MigrationStepResult {
	private bool $complete;
	private ?string $cursor;
	private function __construct( bool $complete, ?string $cursor ) { $this->complete = $complete; $this->cursor = $cursor; }
	public static function complete(): self { return new self( true, null ); }
	public static function continue_from( string $cursor ): self { return new self( false, $cursor ); }
	public function is_complete(): bool { return $this->complete; }
	public function cursor(): ?string { return $this->cursor; }
}
