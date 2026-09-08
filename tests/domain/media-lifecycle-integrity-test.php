<?php
/** Pure PHP characterization for Phase 8 staged media lifecycle integrity. */
define( 'ABSPATH', __DIR__ . '/' );

require_once dirname( __DIR__, 2 ) . '/src/Application/Contracts/MediaGateway.php';
require_once dirname( __DIR__, 2 ) . '/src/Infrastructure/WordPress/WordPressMediaGateway.php';

use METAFANSCORE\Infrastructure\WordPress\WordPressMediaGateway;

final class P8_Media_State {
	public static array $posts = array();
	public static array $meta = array();
	public static array $updates = array();
	public static array $deletes = array();
	public static array $deleted_attachments = array();
	public static array $last_query = array();
}

function get_post( $id ) {
	return P8_Media_State::$posts[ (int) $id ] ?? null;
}
function get_post_meta( $id, $key, $single = true ) {
	return P8_Media_State::$meta[ (int) $id ][ $key ] ?? '';
}
function update_post_meta( $id, $key, $value ) {
	P8_Media_State::$meta[ (int) $id ][ $key ] = $value;
	P8_Media_State::$updates[] = array( (int) $id, $key, $value );
	return true;
}
function delete_post_meta( $id, $key ) {
	unset( P8_Media_State::$meta[ (int) $id ][ $key ] );
	P8_Media_State::$deletes[] = array( (int) $id, $key );
	return true;
}
function get_posts( $args ) {
	P8_Media_State::$last_query = $args;
	return array( 60, 61, 62 );
}
function wp_delete_attachment( $id, $force = false ) {
	P8_Media_State::$deleted_attachments[] = (int) $id;
	return true;
}

function p8_media_assert( $condition, string $message ): void {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL {$message}\n" );
		exit( 1 );
	}
	fwrite( STDOUT, "PASS {$message}\n" );
}

P8_Media_State::$posts[55] = (object) array( 'ID' => 55, 'post_type' => 'attachment', 'post_author' => 10 );
P8_Media_State::$meta[55] = array(
	'_metafans_community_upload_owner' => 10,
	'_metafans_community_upload_state' => 'staged',
	'_metafans_community_upload_staged_at' => 123,
);

$gateway = new WordPressMediaGateway();
$gateway->mark_attached( 55, 10, 100 );
p8_media_assert( 'attached' === P8_Media_State::$meta[55]['_metafans_community_upload_state'], 'owned staged attachment transitions to attached' );
p8_media_assert( 100 === P8_Media_State::$meta[55]['_metafans_community_activity_id'], 'attached media records canonical activity link' );
p8_media_assert( ! isset( P8_Media_State::$meta[55]['_metafans_community_upload_staged_at'] ), 'attached media clears staged timestamp' );

P8_Media_State::$posts[56] = (object) array( 'ID' => 56, 'post_type' => 'attachment', 'post_author' => 10 );
P8_Media_State::$meta[56] = array(
	'_metafans_community_upload_owner' => 10,
	'_metafans_community_upload_state' => 'attached',
);
$before = count( P8_Media_State::$updates );
$gateway->mark_attached( 56, 10, 101 );
p8_media_assert( $before === count( P8_Media_State::$updates ), 'non-staged attachment cannot be rebound through staged lifecycle' );

P8_Media_State::$posts[57] = (object) array( 'ID' => 57, 'post_type' => 'attachment', 'post_author' => 10 );
P8_Media_State::$meta[57] = array(
	'_metafans_community_upload_owner' => 20,
	'_metafans_community_upload_state' => 'staged',
);
$before = count( P8_Media_State::$updates );
$gateway->mark_attached( 57, 10, 102 );
p8_media_assert( $before === count( P8_Media_State::$updates ), 'staged upload owner mismatch fails closed' );

P8_Media_State::$meta[60] = array( '_metafans_community_upload_state' => 'staged', '_metafans_community_activity_id' => '' );
P8_Media_State::$meta[61] = array( '_metafans_community_upload_state' => 'attached', '_metafans_community_activity_id' => '' );
P8_Media_State::$meta[62] = array( '_metafans_community_upload_state' => 'staged', '_metafans_community_activity_id' => 999 );
$deleted = $gateway->cleanup_orphans( 1000, 500 );
p8_media_assert( 100 === P8_Media_State::$last_query['posts_per_page'], 'orphan cleanup hard-caps each batch at 100 attachments' );
p8_media_assert( true === P8_Media_State::$last_query['no_found_rows'], 'orphan cleanup disables unnecessary total counts' );
p8_media_assert( 1 === $deleted && array( 60 ) === P8_Media_State::$deleted_attachments, 'orphan cleanup rechecks state and never deletes attached media' );

echo "PASS MetaFans Phase 8 media lifecycle integrity contract\n";
