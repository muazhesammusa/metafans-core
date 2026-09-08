<?php
MetafansCoreCustomizer()->register_module(
	'MetafansCoreCustomizer_Module_Hooks',
	array(
		'name' => __( 'Metafans Hooks', 'tophive-pro' ),
		'desc' => __( 'Add custom hook scripts', 'tophive-pro' ),
		'enable_default' => true,
		'reload' => true,
	)
);


/**
 * Class MetafansCoreCustomizer_Module_Hooks
 */
class MetafansCoreCustomizer_Module_Hooks extends MetafansCoreCustomizer_Module_Base {

	static $hooks = null;
	static $elementor_activated = false;
	public $post_type = 'tophive_hook';
	static $_instance = null;

	function __construct() {
	}

	/**
	 * Use instance method instead __construct
	 * This method auto load
	 *
	 * @return MetafansCoreCustomizer_Module_Hooks|null
	 */
	static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();

			add_action( 'init', array( self::$_instance, 'post_type' ), 1 );
			add_action( 'admin_bar_menu', array( self::$_instance, 'admin_bar_menu' ), 99 );

			if ( function_exists( 'add_post_type_support' ) ) {
				add_post_type_support( self::$_instance->post_type, 'elementor' );
			}

			require_once MetafansCoreCustomizer()->get_path() . '/inc/class-condition.php';
			require_once MetafansCoreCustomizer()->get_path() . '/inc/class-condition-checker.php';

			if ( is_admin() ) {
				require_once dirname( __FILE__ ) . '/admin.php';
				new MetafansCoreCustomizer_Module_Hooks_Admin();
			}

			self::$elementor_activated = class_exists( '\Elementor\Plugin' );

			add_action( 'wp', array( self::$_instance, 'show_hooks' ), 5 );
			add_action( 'wp', array( self::$_instance, 'apply_hooks' ), 20 );

			if ( self::$elementor_activated ) {
				add_filter( 'get_post_metadata', array( self::$_instance, 'hook_template_for_elementor' ), 15, 4 );
			}
		}

		return self::$_instance;
	}

	/**
	 * Show hook when request
	 */
	function show_hooks() {
		if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
			if ( isset( $_GET['tophive_preview_hook'] ) && $_GET['tophive_preview_hook'] == 1 ) {
				$hooks = $this->get_hook_locations();
				add_action( 'wp_head', array( self::$_instance, 'hook_style' ) );
				unset( $hooks['head_tag'] ); // do not show this tab because it in <head> tag
				foreach ( $hooks as $group ) {
					foreach ( $group['hooks'] as $hook => $name ) {
						add_action( $hook, array( self::$_instance, 'show_hook' ), 1 );
					}
				}
			}
		}
	}

	/**
	 * Admin Bar Menu
	 *
	 * @param  array $wp_admin_bar Admin bar menus.
	 * @return array               Admin bar menus.
	 */
	function admin_bar_menu( $wp_admin_bar = array() ) {
		if ( is_admin() ) {
			return;
		}
		$title  = __( 'Show Hooks', 'tophive-pro' );

		$href = add_query_arg( 'tophive_preview_hook', '1' );
		if ( isset( $_GET['tophive_preview_hook'] ) && 1 == $_GET['tophive_preview_hook'] ) {
			$title  = __( 'Hide Hooks', 'tophive-pro' );
			$href = remove_query_arg( 'tophive_preview_hook' );
		}

		$wp_admin_bar->add_menu(
			array(
				'title'     => $title,
				'id'        => 'tophive_preview_hook',
				'parent'    => false,
				'href'      => $href,
			)
		);

	}

	/**
	 * Get hook label by ID
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	function get_hook_by_id( $id ) {
		$hooks = $this->get_hook_locations();
		foreach ( $hooks as $group ) {
			if ( isset( $group['hooks'] ) && isset( $group['hooks'][ $id ] ) ) {
				return $group['hooks'][ $id ];
			}
		}

		return $id;
	}

	function hook_style() {
		?>
		<style id="tophive-preview-hooks-css" type="text/css">
			.hook--id {
				border: 1px dashed #edc048 !important;
				padding: 5px 10px; margin: 5px !important;
				text-transform: none !important;
				font-size: 11px !important;
				background: #fff !important;
				color: #333 !important;
				font-weight: normal!important;
				display: inline-block !important;
				width: 100%;
				max-width: 100%;
				overflow: auto;
			}
			.posts-layout .entry-article-part.entry-article-footer{
				display: block !important;
			}
		</style>
		<?php
	}

	/**
	 * Show hook positions
	 */
	function show_hook() {
		$filter = current_filter();
		?>
		<div class="hook--id entry--item">
			<?php
			$name = $this->get_hook_by_id( $filter );
			if ( $name != $filter ) {
				echo esc_html( $name . " ({$filter})" );
			} else {
				echo esc_html( $filter );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Always use elementor_canvas template for hook
	 *
	 * @param $value
	 * @param $post_id
	 * @param $meta_key
	 * @param $single
	 *
	 * @return string
	 */
	function hook_template_for_elementor( $value, $post_id, $meta_key, $single ) {
		if ( get_post_type( $post_id ) == $this->post_type && $meta_key == '_wp_page_template' ) {
			$value = 'elementor_canvas';
		}
		return $value;
	}

	/**
	 * Get hook settings
	 *
	 * @param null $p
	 *
	 * @return array|mixed
	 */
	function get_settings( $p = null ) {
		if ( ! $p ) {
			global $post;
			$p = $post;
		}

		$post_id = is_numeric( $p ) ? $p : $p->ID;
		$data = get_post_meta( $post_id, '_tophive_hook', true );

		$data = wp_parse_args(
			$data,
			array(
				'editor' => '',
				'hook' => '',
				'custom_hook' => '',
				'priority' => '',
				'conditionals' => '',
				'code' => '',
				'user_role' => '',
				'enable_php' => '',
			)
		);
		$data['_id'] = $post_id;
		$data['priority'] = trim( $data['priority'] );
		if ( strlen( $data['priority'] ) > 0 ) {
			$data['priority'] = intval( $data['priority'] );
		} else {
			$data['priority'] = null;
		}

		$data = apply_filters( 'tophive-pro/hooks/get_settings', $data, $post_id );

		return $data;
	}

	/**
	 *
	 */
	function apply_hooks() {
		$is_edit_mode = false;
		if ( isset( $_REQUEST['action'] ) && in_array(
			$_REQUEST['action'],
			array(
				'elementor',
				// Templates
				'elementor_get_templates',
				'elementor_save_template',
				'elementor_get_template',
				'elementor_delete_template',
				'elementor_export_template',
				'elementor_import_template',
			)
		)
		) {
			$is_edit_mode = true;
		}
		if ( is_admin() ) {
			$is_edit_mode = true;
		}

		if ( $is_edit_mode ) {
			return;
		}

		foreach ( $this->get_hooks() as $data ) {

			if ( $data['hook'] ) {
				if ( MetafansCoreCustomizer_Conditional_Checker::get_instance()->check_user_role( $data['user_role'] ) && MetafansCoreCustomizer_Conditional_Checker::get_instance()->is_match( $data['conditionals'] ) ) {

					$hook_id = $data['ID'];

					$hook = '__custom' === $data['hook'] ? $data['custom_hook'] : $data['hook'];

					$function = function() use ( $hook_id ) {
						MetafansCoreCustomizer_Module_Hooks::do_action( $hook_id );
					};

					add_action( $hook, $function, $data['priority'] );

				}
			}
		}

	}

	/**
	 *
	 * Render Css for elementor
	 *
	 * @param $post_id
	 *
	 * @return string
	 */
	public static function render_elementor_item_css( $post_id ) {
		if ( post_password_required( $post_id ) ) {
			return '';
		}

		if ( ! Elementor\Plugin::$instance->db->is_built_with_elementor( $post_id ) ) {
			return '';
		}

		$document = Elementor\Plugin::$instance->documents->get_doc_for_frontend( $post_id );

		// Change the current post, so widgets can use `documents->get_current`.
		Elementor\Plugin::$instance->documents->switch_to_document( $document );

		$data = $document->get_elements_data();

		$data = apply_filters( 'elementor/frontend/builder_content_data', $data, $post_id );

		if ( empty( $data ) ) {
			return '';
		}

		if ( class_exists( 'Elementor\Core\Files\CSS\Post' ) ) {
			if ( $document->is_autosave() ) {
				$css_file = new Elementor\Core\Files\CSS\Post_Preview( $document->get_post()->ID );
			} else {
				$css_file = new  Elementor\Core\Files\CSS\Post( $post_id );
			}
		} else {
			if ( $document->is_autosave() ) {
				$css_file = new Elementor\Post_Preview_CSS( $document->get_post()->ID );
			} else {
				$css_file = new Elementor\Post_CSS_File( $post_id );
			}
		}



		$css_file->enqueue();
		$css_file->print_css();
	}

	/**
	 * @see do_action();
	 */
	static function do_action( $id ) {
		if ( get_the_ID() == $id ) {
			return;
		}
		$hooks = self::$hooks;

		if ( isset( $hooks[ $id ] ) ) {

			if ( 'code' == $hooks[ $id ]['editor'] ) {
				$content = do_shortcode( $hooks[ $id ]['code'] );
				// MetaFans v6 no longer executes arbitrary PHP stored in the database.
				echo wp_kses_post( $content );
			} else {
				if ( self::$elementor_activated && get_post_meta( $id, '_elementor_edit_mode', true ) == 'builder' ) {
					self::render_elementor_item_css( $id );
					$content = \Elementor\Plugin::instance()->frontend->get_builder_content( $id, false );
					echo $content;
				} else {
					$p = get_post( $id );
					if ( $p ) {
						echo apply_filters( 'tophive_the_content', $p->post_content );
					}
				}
			}
		}
	}

	function get_hooks() {
		if ( ! is_null( self::$hooks ) ) {
			return self::$hooks;
		}
		$args = array(
			'posts_per_page' => - 1,
			'post_type' => $this->post_type,
		);

		$hooks_query = new WP_Query( $args );
		$hooks = array();
		foreach ( (array) $hooks_query->get_posts() as $p ) {
			$hooks[ $p->ID ] = $this->get_settings( $p->ID );
			$hooks[ $p->ID ]['ID'] = $p->ID;
			$hooks[ $p->ID ]['content'] = $p->post_content;
		}
		self::$hooks = $hooks;
		return $hooks;
	}

	/**
	 * Get list Hook support by default
	 *
	 * @return array
	 */
	function get_hook_locations() {
		$hooks = array(
			'head_tag' => array(
				'group_label' => esc_html__( 'Inside <head> tag', 'tophive-pro' ),
				'hooks' => array(
					'wp_head' => 'wp_head',
					'tophive/load-scripts' => __( 'Load scripts', 'tophive-pro' ),
					'tophive/theme/scripts' => __( 'After scripts added', 'tophive-pro' ),
				),
			),

			'header' => array(
				'group_label' => __( 'Header', 'tophive-pro' ),
				'hooks' => array(
					'customizer/before-header' => 'customizer/before-header',
					'customizer/after-header' => 'customizer/after-header',
					'customizer/after-logo-img' => 'customizer/after-logo-img',
				),
			),

			'page_headear_cover' => array(
				'group_label' => __( 'Page Header Cover', 'tophive-pro' ),
				'hooks' => array(
					'tophive/page-cover/before' => 'tophive/page-cover/before',
					'tophive/page-cover/after' => 'tophive/page-cover/before',
				),
			),

			'page_headear_titlebar' => array(
				'group_label' => __( 'Page Header Titlebar', 'tophive-pro' ),
				'hooks' => array(
					'tophive/titlebar/before' => 'tophive/titlebar/before',
					'tophive/titlebar/after' => 'tophive/titlebar/before',
				),
			),

			'main' => array(
				'group_label' => __( 'Main', 'tophive-pro' ),
				'hooks' => array(
					'tophive/before-site-content' => 'tophive/before-site-content',
					'tophive/after-site-content' => 'tophive/after-site-content',
				),
			),

			'content' => array(
				'group_label' => __( 'Content', 'tophive-pro' ),
				'hooks' => array(
					'tophive/main/before' => 'tophive/main/before',
					'tophive/main/after' => 'tophive/main/after',
					'tophive/content/before' => 'tophive/content/before',
					'tophive/content/after' => 'tophive/content/after',
				),
			),

			'blog_post' => array(
				'group_label' => __( 'Blog Posts', 'tophive-pro' ),
				'hooks' => array(),
			),

			'single_post' => array(
				'group_label' => __( 'Single Post', 'tophive-pro' ),
				'hooks' => array(),
			),

			'sidebar' => array(
				'group_label' => __( 'Sidebar', 'tophive-pro' ),
				'hooks' => array(

					'tophive/sidebar-primary/before' => 'tophive/sidebar-primary/before',
					'tophive/sidebar-primary/after' => 'tophive/sidebar-primary/after',

					'tophive/sidebar-secondary/before' => 'tophive/sidebar-secondary/before',
					'tophive/sidebar-secondary/after' => 'tophive/sidebar-secondary/after',

					'dynamic_sidebar_before' => 'dynamic_sidebar_before',
					'dynamic_sidebar' => 'dynamic_sidebar',
					'dynamic_sidebar_after' => 'dynamic_sidebar_after',
				),
			),

			'footer' => array(
				'group_label' => __( 'Footer', 'tophive-pro' ),
				'hooks' => array(
					'tophive/before-footer' => 'tophive/before-footer',
					'tophive/after-footer'  => 'tophive/after-footer',
					'wp_footer' => 'wp_footer',
				),
			),

		);

		$single_fields = array( 'title', 'meta', 'thumbnail', 'excerpt', 'readmore', 'content', 'categories', 'tags', 'author_bio', 'navigation', 'comment_form' );
		foreach ( $single_fields as $f ) {
			if ( 'readmore' != $f ) {
				$hooks['single_post']['hooks'][ 'tophive/single/field_' . $f . '/before' ] = 'tophive/single/field_' . $f . '/before';
				$hooks['single_post']['hooks'][ 'tophive/single/field_' . $f . '/after' ]  = 'tophive/single/field_' . $f . '/after';
			}

			$hooks['blog_post']['hooks'][ 'tophive/loop/field_' . $f . '/before' ] = 'tophive/loop/field_' . $f . '/before';
			$hooks['blog_post']['hooks'][ 'tophive/loop/field_' . $f . '/after' ] = 'tophive/loop/field_' . $f . '/after';
		}

		// Footer Builder Item
		$list_items = Metafans_Customize_Layout_Builder()->get_builder_items( 'footer' );
		foreach ( $list_items as $id => $item ) {
			$hooks['footer']['hooks'][ "tophive/builder/footer/before-item/{$id}" ] = "tophive/builder/footer/before-item/{$id}";
			$hooks['footer']['hooks'][ "tophive/builder/footer/after-item/{$id}" ] = "tophive/builder/footer/after-item/{$id}";
		}

		if ( tophive_metafans()->is_woocommerce_active() ) {
			$hooks['woocommerce_loop'] = array(
				'group_label' => __( 'WooCommerce Loop', 'tophive-pro' ),
				'hooks' => array(
					'woocommerce_before_shop_loop' => 'woocommerce_before_shop_loop',
					'woocommerce_after_shop_loop' => 'woocommerce_after_shop_loop',
					'woocommerce_no_products_found' => 'woocommerce_no_products_found',

					// Shop loop
					'tophive_wc_product_loop' => 'tophive_wc_product_loop',
					'woocommerce_before_shop_loop_item' => 'woocommerce_before_shop_loop_item',
					'woocommerce_after_shop_loop_item' => 'woocommerce_after_shop_loop_item',
					'tophive_after_loop_product_media' => 'tophive_after_loop_product_media',
					'woocommerce_before_shop_loop_item_title' => 'woocommerce_before_shop_loop_item_title',
					'woocommerce_shop_loop_item_title' => 'woocommerce_shop_loop_item_title',
					'woocommerce_after_shop_loop_item_title' => 'woocommerce_after_shop_loop_item_title',

				),
			);

			$hooks['woocommerce_single'] = array(
				'group_label' => __( 'WooCommerce Single Product', 'tophive-pro' ),
				'hooks' => array(
					// Single Product
					'woocommerce_before_single_product' => 'woocommerce_before_single_product',
					'woocommerce_after_single_product' => 'woocommerce_after_single_product',
					'woocommerce_before_single_product_summary' => 'woocommerce_before_single_product_summary',
					'woocommerce_single_product_summary' => 'woocommerce_single_product_summary',
					'woocommerce_after_single_product_summary' => 'woocommerce_after_single_product_summary',
				),
			);

		}

		return $hooks;
	}

	function get_basic_roles() {
		$basic = array(
			'all' => __( 'All', 'tophive-pro' ),
			'logged_in' => __( 'Logged in', 'tophive-pro' ),
			'logged_out' => __( 'Logged out', 'tophive-pro' ),
		);
		return $basic;
	}

	function get_roles() {
		$editable_roles = get_editable_roles();
		foreach ( $editable_roles as $role => $details ) {
			$sub['role'] = esc_attr( $role );
			$sub['name'] = translate_user_role( $details['name'] );
			$roles[ $role ] = $sub;
		}
		return $roles;
	}

	/**
	 * Register a Hook post type.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/register_post_type
	 */
	function post_type() {

		$labels = array(
			'name'               => _x( 'Metafans Hooks', 'post type general name', 'tophive-pro' ),
			'singular_name'      => _x( 'Metafans Hook', 'post type singular name', 'tophive-pro' ),
			'menu_name'          => _x( 'Metafans Hook', 'admin menu', 'tophive-pro' ),
			'name_admin_bar'     => _x( 'Metafans Hook', 'add new on admin bar', 'tophive-pro' ),
			'add_new'            => _x( 'Add Hook', 'Hook', 'tophive-pro' ),
			'add_new_item'       => __( 'Add New Hook', 'tophive-pro' ),
			'new_item'           => __( 'New Hook', 'tophive-pro' ),
			'edit_item'          => __( 'Edit Hook', 'tophive-pro' ),
			'view_item'          => __( 'View Hook', 'tophive-pro' ),
			'all_items'          => __( 'Metafans Hooks', 'tophive-pro' ),
			'search_items'       => __( 'Search Metafans Hooks', 'tophive-pro' ),
			'parent_item_colon'  => __( 'Parent Metafans Hooks:', 'tophive-pro' ),
			'not_found'          => __( 'No hooks found.', 'tophive-pro' ),
			'not_found_in_trash' => __( 'No hooks found in trash.', 'tophive-pro' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => 'themes.php',
			'show_in_nav_menus'  => false,
			'query_var'          => false,
			'rewrite'            => false,
			'capability_type'    => 'page',
			'has_archive'        => false,
			'hierarchical'       => false,
			'exclude_from_search' => true,
			'menu_position'      => false,
			'supports'           => array( 'title', 'editor' ),
		);

		register_post_type( $this->post_type, $args );
	}

}
