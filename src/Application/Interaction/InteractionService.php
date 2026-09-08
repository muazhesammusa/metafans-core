<?php
/** Core-owned comments, reactions and media-interaction mutations. */
namespace METAFANSCORE\Application\Interaction;

use METAFANSCORE\Application\Activity\ActivityPrivacyService;
use METAFANSCORE\Application\Contracts\ActivityGateway;
use METAFANSCORE\Application\Contracts\NotificationGateway;
use METAFANSCORE\Domain\Social\MutationResult;

defined( 'ABSPATH' ) || exit;

final class InteractionService {
	private ActivityGateway $gateway;
	private ActivityPrivacyService $privacy;
	private NotificationGateway $notifications;

	public function __construct( ActivityGateway $gateway, ActivityPrivacyService $privacy, NotificationGateway $notifications ) {
		$this->gateway       = $gateway;
		$this->privacy       = $privacy;
		$this->notifications = $notifications;
	}

	private function comments( int $activity_id ): array {
		$value = $this->gateway->get_meta( $activity_id, 'tophive_activity_comments' );
		return is_array( $value ) ? array_values( $value ) : array();
	}

	private function base_reactions(): array {
		$row = array( 'count' => 0, 'users' => array() );
		return array(
			'like'  => $row,
			'love'  => $row,
			'haha'  => $row,
			'wow'   => $row,
			'angry' => $row,
			'sad'   => $row,
		);
	}

	private function index_by_id( array $rows, string $id ) {
		foreach ( $rows as $index => $row ) {
			if ( isset( $row['ID'] ) && (string) $row['ID'] === $id ) {
				return $index;
			}
		}
		return false;
	}

	private function can_interact( int $activity_id, int $actor_id ): bool {
		return $actor_id > 0 && $this->privacy->can_view( $activity_id, $actor_id );
	}

	private function locked_result( int $activity_id, string $scope, callable $callback, string $busy_code, string $busy_message ): MutationResult {
		$result = $this->gateway->with_lock( $activity_id, $scope, $callback );
		return $result instanceof MutationResult
			? $result
			: MutationResult::failure( $busy_code, $busy_message, 409 );
	}

	private function valid_reaction_type( string $type, bool $allow_decrement = false ): bool {
		$allowed = array( 'like', 'love', 'haha', 'wow', 'angry', 'sad' );
		return in_array( $type, $allowed, true ) || ( $allow_decrement && 'decrement' === $type );
	}

	public function add_comment( int $actor_id, int $activity_id, string $content, array $profile ): MutationResult {
		if ( ! $this->can_interact( $activity_id, $actor_id ) ) {
			return MutationResult::failure( 'activity_interaction_forbidden', __( 'You cannot interact with this activity.', 'metafans-core' ), 403 );
		}
		if ( '' === trim( wp_strip_all_tags( $content ) ) ) {
			return MutationResult::failure( 'empty_comment', __( 'Comment cannot be empty.', 'metafans-core' ), 400 );
		}

		$result = $this->locked_result(
			$activity_id,
			'comments',
			function () use ( $actor_id, $activity_id, $content, $profile ) {
				$comments   = $this->comments( $activity_id );
				$comments[] = array(
					'ID'          => strtoupper( wp_generate_password( 10, false, false ) ),
					'content'     => $content,
					'time'        => time(),
					'author'      => $actor_id,
					'author_name' => (string) ( $profile['name'] ?? '' ),
					'author_url'  => (string) ( $profile['url'] ?? '' ),
					'avatar'      => (string) ( $profile['avatar'] ?? '' ),
					'reactions'   => $this->base_reactions(),
					'replies'     => array(),
				);
				return $this->gateway->update_meta( $activity_id, 'tophive_activity_comments', $comments )
					? MutationResult::success()
					: MutationResult::failure( 'comment_persist_failed', __( 'Comment could not be saved.', 'metafans-core' ), 500 );
			},
			'comment_busy',
			__( 'Comments are busy. Try again.', 'metafans-core' )
		);

		if ( $result->is_success() ) {
			$this->notifications->activity_comment( $activity_id, $actor_id, 'update_reply' );
		}
		return $result;
	}

	public function add_reply( int $actor_id, int $activity_id, string $comment_id, string $content ): MutationResult {
		if ( ! $this->can_interact( $activity_id, $actor_id ) ) {
			return MutationResult::failure( 'activity_interaction_forbidden', __( 'You cannot interact with this activity.', 'metafans-core' ), 403 );
		}
		if ( '' === trim( wp_strip_all_tags( $content ) ) ) {
			return MutationResult::failure( 'empty_reply', __( 'Reply cannot be empty.', 'metafans-core' ), 400 );
		}

		$result = $this->locked_result(
			$activity_id,
			'comments',
			function () use ( $actor_id, $activity_id, $comment_id, $content ) {
				$comments = $this->comments( $activity_id );
				$index    = $this->index_by_id( $comments, $comment_id );
				if ( false === $index ) {
					return MutationResult::failure( 'comment_not_found', __( 'Comment target not found.', 'metafans-core' ), 404 );
				}
				$comments[ $index ]['replies']   = is_array( $comments[ $index ]['replies'] ?? null ) ? array_values( $comments[ $index ]['replies'] ) : array();
				$comments[ $index ]['replies'][] = array(
					'reply_author'  => $actor_id,
					'reply_content' => $content,
					'reply_time'    => time(),
					'reactions'     => $this->base_reactions(),
				);
				return $this->gateway->update_meta( $activity_id, 'tophive_activity_comments', $comments )
					? MutationResult::success()
					: MutationResult::failure( 'reply_persist_failed', __( 'Reply could not be saved.', 'metafans-core' ), 500 );
			},
			'comment_busy',
			__( 'Comments are busy. Try again.', 'metafans-core' )
		);

		if ( $result->is_success() ) {
			$this->notifications->activity_comment( $activity_id, $actor_id, 'comment_reply' );
		}
		return $result;
	}

	public function delete_comment( int $actor_id, int $activity_id, string $comment_id, ?int $reply_index = null ): MutationResult {
		if ( ! $this->privacy->can_view( $activity_id, $actor_id ) ) {
			return MutationResult::failure( 'activity_not_visible', __( 'Activity is not available.', 'metafans-core' ), 403 );
		}

		return $this->locked_result(
			$activity_id,
			'comments',
			function () use ( $actor_id, $activity_id, $comment_id, $reply_index ) {
				$comments = $this->comments( $activity_id );
				$index    = $this->index_by_id( $comments, $comment_id );
				if ( false === $index ) {
					return MutationResult::failure( 'comment_not_found', __( 'Comment not found.', 'metafans-core' ), 404 );
				}
				$activity = $this->gateway->find( $activity_id );
				$owner    = (int) ( $comments[ $index ]['author'] ?? 0 );
				if ( null !== $reply_index ) {
					if ( ! isset( $comments[ $index ]['replies'][ $reply_index ] ) ) {
						return MutationResult::failure( 'reply_not_found', __( 'Reply not found.', 'metafans-core' ), 404 );
					}
					$owner = (int) ( $comments[ $index ]['replies'][ $reply_index ]['reply_author'] ?? 0 );
				}
				$can_delete = $owner === $actor_id || ( $activity && (int) $activity->user_id === $actor_id ) || $this->gateway->can_moderate( $actor_id );
				if ( ! $can_delete ) {
					return MutationResult::failure( 'comment_delete_forbidden', __( 'You are not allowed to delete this comment.', 'metafans-core' ), 403 );
				}
				if ( null === $reply_index ) {
					unset( $comments[ $index ] );
					$comments = array_values( $comments );
				} else {
					unset( $comments[ $index ]['replies'][ $reply_index ] );
					$comments[ $index ]['replies'] = array_values( $comments[ $index ]['replies'] );
				}
				return $this->gateway->update_meta( $activity_id, 'tophive_activity_comments', $comments )
					? MutationResult::success()
					: MutationResult::failure( 'comment_delete_failed', __( 'Comment could not be deleted.', 'metafans-core' ), 500 );
			},
			'comment_busy',
			__( 'Comments are busy. Try again.', 'metafans-core' )
		);
	}

	private function toggle_reaction_set( array $reactions, int $actor_id, string $type, bool $same_is_noop = false ): array {
		$current = null;
		foreach ( $reactions as $key => $row ) {
			$users = array_map( 'absint', (array) ( $row['users'] ?? array() ) );
			if ( in_array( $actor_id, $users, true ) ) {
				$current = $key;
				break;
			}
		}
		if ( $same_is_noop && $current === $type ) {
			return $reactions;
		}
		if ( $current ) {
			$users                            = array_values( array_diff( array_map( 'absint', (array) ( $reactions[ $current ]['users'] ?? array() ) ), array( $actor_id ) ) );
			$reactions[ $current ]['users']   = $users;
			$reactions[ $current ]['count']   = count( $users );
		}
		if ( 'decrement' !== $type && $current !== $type ) {
			if ( ! isset( $reactions[ $type ] ) ) {
				$reactions[ $type ] = array( 'count' => 0, 'users' => array() );
			}
			$users                          = array_values( array_unique( array_merge( array_map( 'absint', (array) $reactions[ $type ]['users'] ), array( $actor_id ) ) ) );
			$reactions[ $type ]['users']   = $users;
			$reactions[ $type ]['count']   = count( $users );
		}
		return $reactions;
	}

	public function react_activity( int $actor_id, int $activity_id, string $type ): MutationResult {
		if ( ! $this->can_interact( $activity_id, $actor_id ) ) {
			return MutationResult::failure( 'activity_interaction_forbidden', __( 'You cannot interact with this activity.', 'metafans-core' ), 403 );
		}
		if ( ! $this->valid_reaction_type( $type, true ) ) {
			return MutationResult::failure( 'invalid_reaction', __( 'Invalid reaction.', 'metafans-core' ), 400 );
		}
		return $this->locked_result(
			$activity_id,
			'activity_reaction',
			function () use ( $actor_id, $activity_id, $type ) {
				$reactions = $this->gateway->get_meta( $activity_id, 'tophive_activity_reactions' );
				$reactions = is_array( $reactions ) ? $reactions : $this->base_reactions();
				$reactions = $this->toggle_reaction_set( $reactions, $actor_id, $type, true );
				return $this->gateway->update_meta( $activity_id, 'tophive_activity_reactions', $reactions )
					? MutationResult::success()
					: MutationResult::failure( 'reaction_persist_failed', __( 'Reaction could not be saved.', 'metafans-core' ), 500 );
			},
			'reaction_busy',
			__( 'Reaction is busy. Try again.', 'metafans-core' )
		);
	}

	public function react_comment( int $actor_id, int $activity_id, string $comment_id, string $type, ?int $reply_index = null ): MutationResult {
		if ( ! $this->can_interact( $activity_id, $actor_id ) ) {
			return MutationResult::failure( 'activity_interaction_forbidden', __( 'You cannot interact with this activity.', 'metafans-core' ), 403 );
		}
		if ( ! $this->valid_reaction_type( $type, true ) ) {
			return MutationResult::failure( 'invalid_reaction', __( 'Invalid reaction.', 'metafans-core' ), 400 );
		}
		return $this->locked_result(
			$activity_id,
			'comment_reaction',
			function () use ( $actor_id, $activity_id, $comment_id, $type, $reply_index ) {
				$comments = $this->comments( $activity_id );
				$index    = $this->index_by_id( $comments, $comment_id );
				if ( false === $index ) {
					return MutationResult::failure( 'comment_not_found', __( 'Comment not found.', 'metafans-core' ), 404 );
				}
				if ( null !== $reply_index ) {
					if ( ! isset( $comments[ $index ]['replies'][ $reply_index ] ) ) {
						return MutationResult::failure( 'reply_not_found', __( 'Reply not found.', 'metafans-core' ), 404 );
					}
					$comments[ $index ]['replies'][ $reply_index ]['reactions'] = $this->toggle_reaction_set( (array) $comments[ $index ]['replies'][ $reply_index ]['reactions'], $actor_id, $type );
				} else {
					$comments[ $index ]['reactions'] = $this->toggle_reaction_set( (array) $comments[ $index ]['reactions'], $actor_id, $type );
				}
				return $this->gateway->update_meta( $activity_id, 'tophive_activity_comments', $comments )
					? MutationResult::success()
					: MutationResult::failure( 'comment_reaction_failed', __( 'Reaction could not be saved.', 'metafans-core' ), 500 );
			},
			'reaction_busy',
			__( 'Reaction is busy. Try again.', 'metafans-core' )
		);
	}

	private function media_rows( int $activity_id ): array {
		$value = $this->gateway->get_meta( $activity_id, 'activity_media' );
		return is_array( $value ) ? array_values( $value ) : array();
	}

	private function media_index( array $rows, string $id ) {
		foreach ( $rows as $index => $row ) {
			if ( (string) ( $row['id'] ?? '' ) === $id || (string) ( $row['attachment_id'] ?? '' ) === $id ) {
				return $index;
			}
		}
		return false;
	}

	public function add_media_comment( int $actor_id, int $activity_id, string $media_id, string $text ): MutationResult {
		if ( ! $this->can_interact( $activity_id, $actor_id ) ) {
			return MutationResult::failure( 'activity_interaction_forbidden', __( 'You cannot interact with this activity.', 'metafans-core' ), 403 );
		}
		if ( '' === trim( $media_id ) ) {
			return MutationResult::failure( 'invalid_media', __( 'Media target is required.', 'metafans-core' ), 400 );
		}
		if ( '' === trim( wp_strip_all_tags( $text ) ) ) {
			return MutationResult::failure( 'empty_media_comment', __( 'Comment cannot be empty.', 'metafans-core' ), 400 );
		}
		return $this->locked_result(
			$activity_id,
			'media_comments',
			function () use ( $actor_id, $activity_id, $media_id, $text ) {
				$rows  = $this->media_rows( $activity_id );
				$index = $this->media_index( $rows, $media_id );
				if ( false === $index ) {
					return MutationResult::failure( 'media_not_found', __( 'Media not found.', 'metafans-core' ), 404 );
				}
				$rows[ $index ]['comments']   = is_array( $rows[ $index ]['comments'] ?? null ) ? array_values( $rows[ $index ]['comments'] ) : array();
				$rows[ $index ]['comments'][] = array(
					'comment_text'   => $text,
					'comment_author' => $actor_id,
					'comment_time'   => time(),
				);
				return $this->gateway->update_meta( $activity_id, 'activity_media', $rows )
					? MutationResult::success()
					: MutationResult::failure( 'media_comment_failed', __( 'Media comment could not be saved.', 'metafans-core' ), 500 );
			},
			'media_busy',
			__( 'Media interaction is busy. Try again.', 'metafans-core' )
		);
	}

	public function react_media( int $actor_id, int $activity_id, string $media_id, string $type ): MutationResult {
		if ( ! $this->can_interact( $activity_id, $actor_id ) ) {
			return MutationResult::failure( 'activity_interaction_forbidden', __( 'You cannot interact with this activity.', 'metafans-core' ), 403 );
		}
		if ( '' === trim( $media_id ) ) {
			return MutationResult::failure( 'invalid_media', __( 'Media target is required.', 'metafans-core' ), 400 );
		}
		if ( ! $this->valid_reaction_type( $type ) ) {
			return MutationResult::failure( 'invalid_reaction', __( 'Invalid reaction.', 'metafans-core' ), 400 );
		}
		return $this->locked_result(
			$activity_id,
			'media_reaction',
			function () use ( $actor_id, $activity_id, $media_id, $type ) {
				$rows  = $this->media_rows( $activity_id );
				$index = $this->media_index( $rows, $media_id );
				if ( false === $index ) {
					return MutationResult::failure( 'media_not_found', __( 'Media not found.', 'metafans-core' ), 404 );
				}
				$rows[ $index ]['reactions'] = is_array( $rows[ $index ]['reactions'] ?? null ) ? $rows[ $index ]['reactions'] : array();
				$key                          = 'like' === $type ? 'likes' : $type;
				$row                          = $rows[ $index ]['reactions'][ $key ] ?? array( 'count' => 0, 'people_reacted' => array() );
				$people                       = array_values( array_unique( array_map( 'absint', (array) ( $row['people_reacted'] ?? array() ) ) ) );
				if ( in_array( $actor_id, $people, true ) ) {
					$people = array_values( array_diff( $people, array( $actor_id ) ) );
					$active = false;
				} else {
					$people[] = $actor_id;
					$active   = true;
				}
				$rows[ $index ]['reactions'][ $key ] = array(
					'count'          => count( $people ),
					'people_reacted' => $people,
				);
				if ( ! $this->gateway->update_meta( $activity_id, 'activity_media', $rows ) ) {
					return MutationResult::failure( 'media_reaction_failed', __( 'Media reaction could not be saved.', 'metafans-core' ), 500 );
				}
				return MutationResult::success( array( 'active' => $active ) );
			},
			'reaction_busy',
			__( 'Reaction is busy. Try again.', 'metafans-core' )
		);
	}
}
