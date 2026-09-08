<?php
/**
 * Activity content normalization and URL preview enrichment.
 *
 * @package MetaFansCore
 */

namespace METAFANSCORE\Application\Activity;

defined( 'ABSPATH' ) || exit;

final class ActivityContentPreparer {
	public function prepare( string $raw_content, string $preview_url = '' ): string {
		$content = apply_filters( 'bp_activity_post_update_content', wp_kses_post( $raw_content ) );
		if ( '' === trim( wp_strip_all_tags( $content ) ) ) {
			$content = '<span></span>';
		}

		$content = $this->linkify( $content );
		if ( $preview_url ) {
			$content .= $this->preview_markup( $preview_url );
		}

		return wp_kses( $content, apply_filters( 'bp_activity_allowed_tags', wp_kses_allowed_html( 'post' ) ) );
	}

	private function linkify( string $content ): string {
		$content = preg_replace( '/(?<!src=["\'])(http(s)?:\/\/(www\.)?[\/a-zA-Z0-9%\?\.\-]*)(?=$|<|\s)/', '<a href="$0" target="_blank" title="$0">$0</a>', $content );
		$content = preg_replace( '/(?<!\S)#([0-9a-zA-Z_]+)/', '<a href="?hashtag=$1&s=$1">#$1</a>', $content );
		return function_exists( 'convert_smilies' ) ? convert_smilies( $content ) : $content;
	}

	private function preview_markup( string $url ): string {
		$url = esc_url_raw( $url );
		if ( ! $url ) {
			return '';
		}

		$type = $this->detect_media_url_type( $url );
		if ( in_array( $type['video_type'], array( 'youtube', 'vimeo' ), true ) ) {
			$embed = $this->video_embed_url( $url );
			return $embed ? '<div class="whats-new-live-preview"><span class="cross inline-cross">&times;</span><div class="video-embed preview-thumb"><iframe src="' . esc_url( $embed ) . '"></iframe></div></div>' : '';
		}
		if ( 'soundcloud' === $type['video_type'] ) {
			$embed = 'https://w.soundcloud.com/player/?url=' . rawurlencode( $type['video_id'] );
			return '<div class="activity-soundcloud-embed"><iframe src="' . esc_url( $embed ) . '"></iframe></div>';
		}

		$response = wp_safe_remote_get( $url, array( 'user-agent' => 'Mozilla/5.0 MetaFans/6', 'timeout' => 8, 'redirection' => 3 ) );
		if ( is_wp_error( $response ) ) {
			return '';
		}
		$body = wp_remote_retrieve_body( $response );
		if ( ! $body || ! class_exists( '\DOMDocument' ) ) {
			return '';
		}

		$document = new \DOMDocument();
		libxml_use_internal_errors( true );
		$loaded = $document->loadHTML( $body );
		libxml_clear_errors();
		if ( ! $loaded ) {
			return '';
		}
		$xpath = new \DOMXPath( $document );
		$title = $this->first_node_value( $xpath, '//title' );
		$image = $this->first_meta_value( $xpath, 'og:image' );
		$site  = $this->first_meta_value( $xpath, 'og:site_name' );

		return '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer" class="link_open_new_tab"><div class="whats-new-live-preview"><span class="cross inline-cross">&times;</span><div class="preview-thumb">' . ( $image ? '<img src="' . esc_url( $image ) . '" alt="" />' : '' ) . '</div><div class="preview-content"><span>' . esc_html( $site ) . '</span><span>' . esc_html( $title ) . '</span></div></div></a>';
	}

	private function first_node_value( \DOMXPath $xpath, string $query ): string {
		$nodes = $xpath->query( $query );
		return $nodes && $nodes->length ? trim( (string) $nodes->item( 0 )->textContent ) : '';
	}

	private function first_meta_value( \DOMXPath $xpath, string $property ): string {
		$nodes = $xpath->query( '//meta[@property="' . $property . '"]' );
		return $nodes && $nodes->length ? trim( (string) $nodes->item( 0 )->getAttribute( 'content' ) ) : '';
	}

	private function detect_media_url_type( string $url ): array {
		if ( preg_match( '/(?:youtube\.com\/.*[?&]v=|youtu\.be\/)([\w-]+)/i', $url, $match ) ) {
			return array( 'video_type' => 'youtube', 'video_id' => $match[1] );
		}
		if ( preg_match( '/vimeo\.com\/(?:video\/)?([0-9]+)/i', $url, $match ) ) {
			return array( 'video_type' => 'vimeo', 'video_id' => $match[1] );
		}
		if ( false !== strpos( $url, 'soundcloud.com/' ) ) {
			return array( 'video_type' => 'soundcloud', 'video_id' => $url );
		}
		return array( 'video_type' => 'none', 'video_id' => '' );
	}

	private function video_embed_url( string $url ): string {
		$type = $this->detect_media_url_type( $url );
		if ( 'youtube' === $type['video_type'] ) {
			return 'https://www.youtube.com/embed/' . rawurlencode( $type['video_id'] );
		}
		if ( 'vimeo' === $type['video_type'] ) {
			return 'https://player.vimeo.com/video/' . rawurlencode( $type['video_id'] );
		}
		return '';
	}
}
