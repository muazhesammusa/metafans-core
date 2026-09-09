<?php
namespace METAFANSCORE\Application\Upgrade;
use Throwable;
defined( 'ABSPATH' ) || exit;
final class MigrationRunner {
	private MigrationStateStore $store;
	private array $migrations;
	public function __construct( MigrationStateStore $store, array $migrations ) { $this->store = $store; $this->migrations = $migrations; }
	public function status(): array { return $this->normalize_state( $this->store->load() ); }
	public function has_pending(): bool {
		$state = $this->status();
		foreach ( $this->migrations as $migration ) if ( ! in_array( $migration->id(), $state['completed'], true ) ) return true;
		return false;
	}
	public function run( int $max_steps = 25, bool $allow_destructive = false ): array {
		$max_steps = max( 1, min( 1000, $max_steps ) );
		$token = $this->store->acquire_lock();
		if ( null === $token ) return array_merge( $this->status(), array( 'result' => 'locked' ) );
		try {
			$state = $this->status();
			$steps = 0;
			foreach ( $this->migrations as $migration ) {
				if ( in_array( $migration->id(), $state['completed'], true ) ) continue;
				if ( $migration->is_destructive() && ! $allow_destructive ) {
					$state['current'] = $migration->id(); $state['target_version'] = $migration->target_version(); $state['updated_at'] = $this->store->now();
					$this->store->save( $state );
					return array_merge( $state, array( 'result' => 'requires_manual' ) );
				}
				$cursor = $state['current'] === $migration->id() ? $state['cursor'] : null;
				$state['current'] = $migration->id(); $state['target_version'] = $migration->target_version(); $state['failed'] = null; $state['updated_at'] = $this->store->now();
				$this->store->save( $state );
				while ( $steps < $max_steps ) {
					try { $result = $migration->run( $cursor ); }
					catch ( Throwable $error ) {
						$state['cursor'] = $cursor;
						$state['failed'] = array( 'migration' => $migration->id(), 'message' => $error->getMessage() );
						$state['updated_at'] = $this->store->now(); $this->store->save( $state );
						return array_merge( $state, array( 'result' => 'failed' ) );
					}
					++$steps;
					if ( $result->is_complete() ) {
						$state['completed'][] = $migration->id();
						$state['completed'] = array_values( array_unique( $state['completed'] ) );
						$state['current'] = null; $state['cursor'] = null; $state['failed'] = null; $state['updated_at'] = $this->store->now(); $this->store->save( $state );
						break;
					}
					$cursor = $result->cursor(); $state['cursor'] = $cursor; $state['updated_at'] = $this->store->now(); $this->store->save( $state );
				}
				if ( $steps >= $max_steps && $state['current'] ) return array_merge( $state, array( 'result' => 'in_progress' ) );
			}
			return array_merge( $state, array( 'result' => 'complete' ) );
		} finally { $this->store->release_lock( $token ); }
	}
	private function normalize_state( array $state ): array {
		$state = array_merge( array( 'completed'=>array(), 'current'=>null, 'cursor'=>null, 'failed'=>null, 'target_version'=>null, 'updated_at'=>0 ), $state );
		$state['completed'] = array_values( array_filter( array_map( 'strval', (array) $state['completed'] ) ) );
		return $state;
	}
}
