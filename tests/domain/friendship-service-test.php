<?php
/**
 * Pure PHP characterization for the Phase 3 friendship service.
 */

define( 'ABSPATH', __DIR__ . '/' );

require_once dirname( __DIR__, 2 ) . '/src/Application/Contracts/FriendshipGateway.php';
require_once dirname( __DIR__, 2 ) . '/src/Domain/Friendship/FriendshipResult.php';
require_once dirname( __DIR__, 2 ) . '/src/Application/Friendship/FriendshipService.php';

use METAFANSCORE\Application\Contracts\FriendshipGateway;
use METAFANSCORE\Application\Friendship\FriendshipService;

final class FriendshipGatewayStub implements FriendshipGateway {
	public string $state = 'not_friends';
	public bool $available = true;
	public bool $member_exists = true;
	public array $calls = array();

	public function is_available(): bool { return $this->available; }
	public function member_exists( int $user_id ): bool { return $this->member_exists; }
	public function status( int $actor_id, int $target_id ): string { return $this->state; }
	public function request( int $actor_id, int $target_id ): bool { $this->calls[] = 'request'; $this->state = 'pending'; return true; }
	public function withdraw( int $actor_id, int $target_id ): bool { $this->calls[] = 'withdraw'; $this->state = 'not_friends'; return true; }
	public function remove( int $actor_id, int $target_id ): bool { $this->calls[] = 'remove'; $this->state = 'not_friends'; return true; }
	public function accept( int $actor_id, int $target_id ): bool { $this->calls[] = 'accept'; $this->state = 'is_friend'; return true; }
}

function assert_true( $condition, string $message ): void {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL {$message}\n" );
		exit( 1 );
	}
	fwrite( STDOUT, "PASS {$message}\n" );
}

$gateway = new FriendshipGatewayStub();
$service = new FriendshipService( $gateway );

$result = $service->toggle( 10, 20 );
assert_true( $result->is_success() && array( 'request' ) === $gateway->calls, 'not_friends requests friendship' );
assert_true( 'pending' === $result->state(), 'request returns resulting pending state' );

$gateway->calls = array();
$gateway->state = 'pending';
$result = $service->toggle( 10, 20 );
assert_true( $result->is_success() && array( 'withdraw' ) === $gateway->calls, 'pending withdraws outgoing request' );

$gateway->calls = array();
$gateway->state = 'is_friend';
$result = $service->toggle( 10, 20 );
assert_true( $result->is_success() && array( 'remove' ) === $gateway->calls, 'is_friend removes friendship' );

$gateway->calls = array();
$gateway->state = 'awaiting_response';
$result = $service->toggle( 10, 20 );
assert_true( $result->is_success() && array( 'accept' ) === $gateway->calls, 'awaiting_response accepts friendship' );

$gateway->calls = array();
$result = $service->toggle( 10, 10 );
assert_true( ! $result->is_success() && 'invalid_friendship_target' === $result->code() && array() === $gateway->calls, 'self friendship rejected before persistence' );

$gateway->member_exists = false;
$result = $service->toggle( 10, 999 );
assert_true( ! $result->is_success() && 'member_not_found' === $result->code(), 'missing target rejected' );

$gateway->member_exists = true;
$gateway->available = false;
$result = $service->toggle( 10, 20 );
assert_true( ! $result->is_success() && 'friendship_unavailable' === $result->code(), 'missing BuddyPress friendship component fails closed' );

fwrite( STDOUT, "PASS MetaFans Phase 3 friendship service contract\n" );
