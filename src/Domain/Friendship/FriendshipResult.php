<?php
/**
 * Immutable result for friendship commands.
 *
 * @package MetaFansCore
 */

namespace METAFANSCORE\Domain\Friendship;

defined( 'ABSPATH' ) || exit;

final class FriendshipResult {
	private bool $success;
	private string $code;
	private string $message;
	private int $http_status;
	private int $actor_id;
	private int $target_id;
	private string $previous_state;
	private string $state;
	private string $operation;

	private function __construct(
		bool $success,
		string $code,
		string $message,
		int $http_status,
		int $actor_id,
		int $target_id,
		string $previous_state,
		string $state,
		string $operation
	) {
		$this->success        = $success;
		$this->code           = $code;
		$this->message        = $message;
		$this->http_status    = $http_status;
		$this->actor_id       = $actor_id;
		$this->target_id      = $target_id;
		$this->previous_state = $previous_state;
		$this->state          = $state;
		$this->operation      = $operation;
	}

	public static function success( int $actor_id, int $target_id, string $previous_state, string $state, string $operation ): self {
		return new self( true, 'friendship_updated', '', 200, $actor_id, $target_id, $previous_state, $state, $operation );
	}

	public static function failure( string $code, string $message, int $http_status, int $actor_id = 0, int $target_id = 0, string $state = '' ): self {
		return new self( false, $code, $message, $http_status, $actor_id, $target_id, $state, $state, 'none' );
	}

	public function is_success(): bool {
		return $this->success;
	}

	public function code(): string {
		return $this->code;
	}

	public function message(): string {
		return $this->message;
	}

	public function http_status(): int {
		return $this->http_status;
	}

	public function actor_id(): int {
		return $this->actor_id;
	}

	public function target_id(): int {
		return $this->target_id;
	}

	public function state(): string {
		return $this->state;
	}

	public function to_array(): array {
		return array(
			'result'         => $this->success,
			'code'           => $this->code,
			'state'          => $this->state,
			'previous_state' => $this->previous_state,
			'operation'      => $this->operation,
			'target_id'      => $this->target_id,
		);
	}
}
