<?php

class MetafansCoreCustomizer {
	static $_instance;
	static $path;
	static $url;
	static $file;
	/**
	 * @var string Option key to save module settings
	 */
	private $option_key = 'tophive_modules';
	/**
	 * @var string Path to module folder
	 */
	public $modules_path = 'modules';
	public $modules = array();
	public $installed_modules = array('Metafans_Customizer_Control_Multiple_Sections');
	public $css = array();
	public $js = array();
	public $local_scripts_args = array();

	/**
	 * Init
	 */
	function init() {
		$this->load();
		$this->init_admin();
		add_filter( 'tophive/is_pro_activated', '__return_true' );
		add_filter( 'tophive_is_builder_row_display', array( $this, 'check_footer_top_row' ), PHP_INT_MAX, 4 );
	}

	function check_footer_top_row( $show, $builder_id, $row_id, $post_id ) {
		if ( ! MetafansCoreCustomizer()->is_enabled_module( 'MetafansCoreCustomizer_Module_Header_Footer_Items' ) ) {
			if ( 'footer' == $builder_id && 'top' == $row_id ) {
				$show = false;
			}
		}
		return $show;
	}

	/**
	 * Load files
	 *
	 * @param array $files array file paths.
	 */
	function load_files( $files ) {
		foreach ( $files as $f ) {
			$file = self::$path . "{$f}.php";
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
	}

	/**
	 * Init admin
	 */
	function init_admin() {
		require_once __DIR__ . '/metabox.php';
		require_once __DIR__ . '/class-dashboard.php';

		add_filter( 'plugin_action_links_' . plugin_basename( self::$file ), array( $this, 'add_action_links' ) );

	}

	function add_action_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'themes.php?page=tophive' ) . '">' . __( 'Settings', 'tophive-pro' ) . '</a>',
		);

		return array_merge( $links, $plugin_links );
	}


	static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
			self::$file      = dirname( __DIR__, 2 ) . '/metafans-core.php';
			self::$path      = untrailingslashit( dirname( __DIR__, 2 ) . '/t' );
			self::$url       = untrailingslashit( plugins_url( 't', self::$file ) );
		}

		return self::$_instance;
	}

	/**
	 * Get plugin url
	 *
	 * @return string
	 */
	function get_url( $path = '' ) {
		return self::$url . $path;
	}

	/**
	 * Get plugin path
	 *
	 * @return string
	 */
	function get_path( $path = null ) {
		return self::$path . $path;
	}

	function theme_setup() {
		add_post_type_support( 'page', 'excerpt' );
	}

	function is_tophive_active() {
		return class_exists( 'Metafans' );
	}

	function load() {

		if ( ! $this->is_tophive_active() ) {
			return;
		}

		add_action( 'after_setup_theme', array( $this, 'theme_setup' ) );

		require_once __DIR__ . '/class-module-base.php';
		require_once __DIR__ . '/class-module-assets.php';

		$files = array(
			'/modules/header-transparent/header-transparent',
			'/modules/header-sticky/header-sticky',
			'/modules/scrolltop/scrolltop',
			'/modules/blog/blog',
			'/modules/advanced-styling/advanced-styling',
			'/modules/multiple-headers/multiple-headers',
			'/modules/multilingual/multilingual',
			'/modules/cookie-notice/cookie-notice',
			'/modules/custom-fonts/custom-fonts',
			'/modules/typekit/typekit',

			'/modules/woocommerce-booster/woocommerce-booster',

			'/modules/infinity/infinity',
		);

		$this->load_files( $files );
		$this->load_modules();
		add_action( 'tophive/load-scripts', array( $this, 'load_assets' ) );
	}

	function load_assets() {
		wp_enqueue_script( 'jquery' );
		do_action( 'tophive-pro/scripts' );
		add_filter( 'tophive/theme/js', array( $this, 'get_js' ) );
		add_filter( 'tophive/theme/css', array( $this, 'get_css' ) );
		add_action( 'tophive/theme/scripts', array( $this, 'scripts' ) );
	}

	function register_sub_module( $parent_class_name, $class_name, $args = array() ) {
		if ( ! isset( $this->modules[ $parent_class_name ] ) ) {
			$this->register_module( $parent_class_name, array() );
		}

		$this->register_module( $class_name, $args );
		$this->modules[ $class_name ]['parent']               = $parent_class_name;
		$this->modules[ $parent_class_name ]['sub_modules'][] = $class_name;
	}

	function boolval( $var ) {
		return ! ! $var;
	}

	function register_module( $class_name, $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'name'                  => '',
				'desc'                  => '',
				'doc_link'              => '',
				'doc_text'              => '',
				'reload'                => false,
				'enable_default'        => '',
				'parent'                => false, // class name of parent module
				'sub_modules'           => array(),
				'toggle_disable_notice' => '',
			)
		);

		if ( ! isset( $args['can_toggle'] ) ) {
			$args['can_toggle'] = true;
		} else {
			if ( function_exists( 'boolval' ) ) {
				$args['can_toggle'] = boolval( $args['can_toggle'] );
			} else {
				$args['can_toggle'] = $this->boolval( $args['can_toggle'] );
			}
		}

		// Merge sub module if exists
		if ( isset( $this->modules[ $class_name ] ) && ! empty( $this->modules[ $class_name ] ) && ! empty( $args['sub_modules'] ) ) {
			$args['sub_modules'] = array_merge( $this->modules[ $class_name ], $args['sub_modules'] );
		}

		$args['_class']               = $class_name;
		$this->modules[ $class_name ] = $args;
	}

	/**
	 * Get Module Settings
	 *
	 * @return array|mixed|void
	 */
	function get_module_settings() {
		$modules = get_option( $this->option_key );
		if ( ! is_array( $modules ) ) {
			$modules = array();
		}

		return $modules;
	}

	/**
	 * Enable a module
	 *
	 * @param $class_name
	 */
	function enable_module( $class_name ) {
		$modules                = $this->get_module_settings();
		$modules[ $class_name ] = 1;
		update_option( $this->option_key, $modules );
		do_action( 'tophive-pro/module-status-changed', 'enable', $class_name );
	}

	/**
	 * Disable module name
	 *
	 * @param $class_name
	 */
	function disable_module( $class_name ) {
		$modules                = $this->get_module_settings();
		$modules[ $class_name ] = 0;
		update_option( $this->option_key, $modules );
		do_action( 'tophive-pro/module-status-changed', 'disable', $class_name );
	}

	/**
	 * Check if module enabled ?
	 *
	 * @param $class_name
	 *
	 * @return bool
	 */
	function is_enabled_module( $class_name ) {
		$modules = $this->get_module_settings();
		if ( ! isset( $modules[ $class_name ] ) ) {
			if ( isset( $this->modules[ $class_name ] ) && $this->modules[ $class_name ]['enable_default'] ) {
				return true;
			} else {
				return false;
			}
		}

		if ( $modules[ $class_name ] ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get Registered Module
	 *
	 * @param      $class_name
	 * @param bool       $only_activated
	 *
	 * @return bool|mixed
	 */
	function get_module( $class_name, $only_activated = false ) {
		if ( $only_activated ) {
			if ( isset( $this->installed_modules[ $class_name ] ) ) {
				return $this->installed_modules[ $class_name ];
			}
		}
		if ( isset( $this->modules[ $class_name ] ) ) {
			return $this->modules[ $class_name ];
		}

		return false;
	}

	/**
	 * Load modules
	 */
	public function load_modules() {
		foreach ( $this->modules as $class_name => $f ) {
			// if ( MetafansCoreCustomizer()->is_enabled_module( $class_name ) ) {
				if ( method_exists( $class_name, 'get_instance' ) ) {
					$this->installed_modules[ $class_name ] = $class_name::get_instance();
				} else {
					$this->installed_modules[ $class_name ] = new $class_name();
				}
			// }
		}
	}

	/**
	 * Localize script settings
	 */
	function scripts() {
		wp_localize_script( 'tophive-themejs', 'MetafansCoreCustomizer_JS', $this->local_scripts_args );
	}

	/**
	 * Get JS files
	 *
	 *  Maybe compress files
	 *
	 * @param araray $files
	 *
	 * @return array
	 */
	function get_js( $files ) {
		$js_files = MetafansCoreCustomizer_Assets::get_instance()->compress_js( $this->js );

		return array_merge( $files, $js_files );
	}

	/**
	 * Get CSS files
	 *
	 * Maybe compress files
	 *
	 * @param array $files
	 *
	 * @return array
	 */
	function get_css( $files ) {
		$css_files = MetafansCoreCustomizer_Assets::get_instance()->compress_css( $this->css );

		return array_merge( $files, $css_files );
	}

	function add_css( $id, $url ) {
		$this->css[ $id ] = $url;
	}

	function add_js( $id, $url ) {
		$this->js[ $id ] = $url;
	}

	function add_local_scripts_args( $key, $value ) {
		$this->local_scripts_args[ $key ] = $value;
	}

}

/**
 * Alias if class MetafansCoreCustomizer
 *
 * @see MetafansCoreCustomizer
 * @return MetafansCoreCustomizer
 */
function MetafansCoreCustomizer() {
	return MetafansCoreCustomizer::get_instance();
}

/**
 * Run plugin
 */
function MetafansCoreCustomizer_Init() {
	$c = MetafansCoreCustomizer::get_instance();
	$c->init();
}

add_action( 'tophive/init', 'MetafansCoreCustomizer_Init', 1 );



