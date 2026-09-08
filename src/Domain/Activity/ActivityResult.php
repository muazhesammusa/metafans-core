<?php
/**
 * Immutable activity command result.
 *
 * @package MetaFansCore
 */

namespace METAFANSCORE\Domain\Activity;

defined( 'ABSPATH' ) || exit;

final class ActivityResult {
	private bool $success;
	private string $code;
	private string $message;
	private int $http_status;
	private int $activity_id;

	private function __construct( bool $success, string $code, string $message, int $http_status, int $activity_id ) {
		$this->success     = $success;
		$this->code        = $code;
		$this->message     = $message;
		$this->http_status = $http_status;
		$this->activity_id = $activity_id;
	}

	public static function success( int $activity_id, string $code = 'activity_updated' ): self {
		return new self( true, $code, '', 200, $activity_id );
	}

	public static function failure( string $code, string $message, int $http_status = 400, int $activity_id = 0 ): self {
		return new self( false, $code, $message, $http_status, $activity_id );
	}

	public function is_success(): bool { return $this->success; }
	public function code(): string { return $this->code; }
	public function message(): string { return $this->message; }
	public function http_status(): int { return $this->http_status; }
	public function activity_id(): int { return $this->activity_id; }
}
