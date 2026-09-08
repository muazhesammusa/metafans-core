<?php
/** Generic social-domain mutation result. */
namespace METAFANSCORE\Domain\Social;
defined( 'ABSPATH' ) || exit;
final class MutationResult {
    private bool $success; private string $code; private string $message; private int $status; private array $data;
    private function __construct( bool $success, string $code, string $message, int $status, array $data = array() ) { $this->success=$success; $this->code=$code; $this->message=$message; $this->status=$status; $this->data=$data; }
    public static function success( array $data = array(), string $message = '' ): self { return new self( true, 'ok', $message, 200, $data ); }
    public static function failure( string $code, string $message, int $status = 400, array $data = array() ): self { return new self( false, $code, $message, $status, $data ); }
    public function is_success(): bool { return $this->success; }
    public function code(): string { return $this->code; }
    public function message(): string { return $this->message; }
    public function http_status(): int { return $this->status; }
    public function data(): array { return $this->data; }
}
