<?php
/**
 * Canonical MetaFans reaction-set normalization.
 *
 * @package MetaFansCore
 */

namespace METAFANSCORE\Domain\Social;

defined( 'ABSPATH' ) || exit;

final class ReactionSet {
	public const TYPES = array( 'like', 'love', 'haha', 'wow', 'angry', 'sad' );

	public static function empty_set(): array {
		$out = array();
		foreach ( self::TYPES as $type ) {
			$out[ $type ] = array( 'count' => 0, 'users' => array() );
		}
		return $out;
	}

	/**
	 * Normalize counts, invalid IDs, duplicates and cross-type duplicate membership.
	 */
	public static function normalize( array $reactions ): array {
		$normalized = self::empty_set();
		$claimed    = array();

		foreach ( self::TYPES as $type ) {
			$row   = isset( $reactions[ $type ] ) && is_array( $reactions[ $type ] ) ? $reactions[ $type ] : array();
			$users = array();
			foreach ( (array) ( $row['users'] ?? array() ) as $user_id ) {
				$user_id = abs( (int) $user_id );
				if ( $user_id < 1 || isset( $claimed[ $user_id ] ) ) {
					continue;
				}
				$claimed[ $user_id ] = true;
				$users[]              = $user_id;
			}
			$normalized[ $type ] = array(
				'count' => count( $users ),
				'users' => $users,
			);
		}

		return $normalized;
	}

	public static function toggle( array $reactions, int $actor_id, string $type, bool $same_is_noop = false ): array {
		$reactions = self::normalize( $reactions );
		if ( $actor_id < 1 ) {
			return $reactions;
		}

		$current = null;
		foreach ( self::TYPES as $candidate ) {
			if ( in_array( $actor_id, $reactions[ $candidate ]['users'], true ) ) {
				$current = $candidate;
				break;
			}
		}

		if ( $same_is_noop && $current === $type ) {
			return $reactions;
		}

		if ( null !== $current ) {
			$users = array_values( array_diff( $reactions[ $current ]['users'], array( $actor_id ) ) );
			$reactions[ $current ] = array( 'count' => count( $users ), 'users' => $users );
		}

		if ( 'decrement' !== $type && in_array( $type, self::TYPES, true ) && $current !== $type ) {
			$users = $reactions[ $type ]['users'];
			$users[] = $actor_id;
			$users = array_values( array_unique( $users ) );
			$reactions[ $type ] = array( 'count' => count( $users ), 'users' => $users );
		}

		return $reactions;
	}

	public static function normalize_media_row( array $row ): array {
		$people = array();
		foreach ( (array) ( $row['people_reacted'] ?? array() ) as $user_id ) {
			$user_id = abs( (int) $user_id );
			if ( $user_id > 0 && ! in_array( $user_id, $people, true ) ) {
				$people[] = $user_id;
			}
		}
		return array(
			'count'          => count( $people ),
			'people_reacted' => $people,
		);
	}
}
