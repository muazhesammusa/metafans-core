<?php
/**
 * Legacy MetaFans self-updater compatibility surface.
 *
 * The historical updater downloaded an arbitrary remote ZIP and extracted it
 * directly into the theme root. MetaFans v6 retires that unsafe path. Theme
 * updates must use the normal WordPress/Envato lifecycle where package
 * validation, filesystem credentials and upgrade transactions are handled by
 * the platform.
 *
 * @package MetaFansCore
 */

defined( 'ABSPATH' ) || exit;

final class MetafansThemeUpdater {
	private static $instance = null;

	public function __construct() {
		add_action( 'tophive_core_dynamic_update', array( $this, 'themePlaceHolder' ) );
	}

	public function themePlaceHolder() {
		?>
		<h3 class="tophive-section-heading"><?php esc_html_e( 'Theme updates use the standard WordPress update workflow.', WP_MF_CORE_SLUG ); ?></h3>
		<p><?php esc_html_e( 'The legacy direct-download updater has been retired for security and upgrade integrity.', WP_MF_CORE_SLUG ); ?></p>
		<?php
	}

	public static function getInstance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

MetafansThemeUpdater::getInstance();
