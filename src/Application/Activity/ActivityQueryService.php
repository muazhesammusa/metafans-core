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
	private const MAX_PAGE_SIZE = 50;
	private const MAX_SEARCH_PAGES = 10;

	private ActivityGateway $gateway;
	private ActivityPrivacyService $privacy;

	public function __construct( ActivityGateway $gateway, ActivityPrivacyService $privacy ) {
		$this->gateway = $gateway;
		$this->privacy = $privacy;
	}

	public function find_visible( int $activity_id, int $viewer_id = 0 ): ?object {
		$activity = $this->gateway->find( $activity_id );
		return $activity && $this->privacy->can_view_activity( $activity, $viewer_id ) ? $activity : null;
	}

	public function visible_page( array $args, int $viewer_id = 0 ): array {
		$args['page']        = isset( $args['page'] ) ? max( 1, (int) $args['page'] ) : 1;
		$args['per_page']    = isset( $args['per_page'] ) ? min( self::MAX_PAGE_SIZE, max( 1, (int) $args['per_page'] ) ) : 10;
		$args['show_hidden'] = false;
		$args['spam']        = 'ham_only';
		return $this->privacy->filter_visible( $this->gateway->query( $args ), $viewer_id );
	}

	public function search_visible( string $search_terms, int $limit = 10, int $offset = 0, int $viewer_id = 0 ): array {
		$search_terms = trim( strip_tags( $search_terms ) );
		$limit        = min( self::MAX_PAGE_SIZE, max( 1, $limit ) );
		$offset       = max( 0, $offset );
		if ( '' === $search_terms || ! $this->gateway->is_available() ) {
			return array();
		}

		$visible  = array();
		$page     = 1;
		$per_page = min( self::MAX_PAGE_SIZE, max( 20, $limit + $offset ) );

		while ( count( $visible ) < ( $offset + $limit ) && $page <= self::MAX_SEARCH_PAGES ) {
			$activities = $this->gateway->search( $search_terms, $page, $per_page );
			if ( ! $activities ) {
				break;
			}
			$visible = array_merge( $visible, $this->privacy->filter_visible( $activities, $viewer_id ) );
			if ( count( $activities ) < $per_page ) {
				break;
			}
			$page++;
		}

		return array_slice( $visible, $offset, $limit );
	}
}
