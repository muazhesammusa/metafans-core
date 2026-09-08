<?php
/**
 * Bounded viewer-aware activity queries.
 *
 * @package MetaFansCore
 */

namespace METAFANSCORE\Application\Activity;

use METAFANSCORE\Application\Contracts\ActivityGateway;

defined( 'ABSPATH' ) || exit;

final class ActivityQueryService {
	private ActivityGateway $gateway;
	private ActivityPrivacyService $privacy;

	public function __construct( ActivityGateway $gateway, ActivityPrivacyService $privacy ) {
		$this->gateway = $gateway;
		$this->privacy = $privacy;
	}

	public function find_visible( int $activity_id, int $viewer_id = 0 ): ?object {
		return $this->privacy->can_view( $activity_id, $viewer_id ) ? $this->gateway->find( $activity_id ) : null;
	}

	public function visible_page( array $args, int $viewer_id = 0 ): array {
		$args['page']        = isset( $args['page'] ) ? max( 1, (int) $args['page'] ) : 1;
		$args['per_page']    = isset( $args['per_page'] ) ? min( 50, max( 1, (int) $args['per_page'] ) ) : 10;
		$args['show_hidden'] = false;
		$args['spam']        = 'ham_only';
		$activities = $this->gateway->query( $args );
		return array_values( array_filter( $activities, function ( $activity ) use ( $viewer_id ) {
			return ! empty( $activity->id ) && $this->privacy->can_view( (int) $activity->id, $viewer_id );
		} ) );
	}

	public function search_visible( string $search_terms, int $limit = 10, int $offset = 0, int $viewer_id = 0 ): array {
		$search_terms = trim( strip_tags( $search_terms ) );
		$limit        = min( 50, max( 1, $limit ) );
		$offset       = max( 0, $offset );
		if ( '' === $search_terms || ! $this->gateway->is_available() ) {
			return array();
		}

		$visible   = array();
		$page      = 1;
		$per_page  = min( 50, max( 20, $limit + $offset ) );
		$max_pages = 10;

		while ( count( $visible ) < ( $offset + $limit ) && $page <= $max_pages ) {
			$activities = $this->gateway->search( $search_terms, $page, $per_page );
			if ( ! $activities ) {
				break;
			}
			foreach ( $activities as $activity ) {
				if ( ! empty( $activity->id ) && $this->privacy->can_view( (int) $activity->id, $viewer_id ) ) {
					$visible[] = $activity;
				}
			}
			if ( count( $activities ) < $per_page ) {
				break;
			}
			$page++;
		}

		return array_slice( $visible, $offset, $limit );
	}
}
