<?php

class MetafansCoreCustomizer_Module_Multiple_Headers extends MetafansCoreCustomizer_Module_Base {

	function __construct() {
		require_once WP_MF_CORE_PATH . 'src/Legacy/class-condition.php';
		require_once dirname( __FILE__ ) . '/inc/config.php';
		require_once dirname( __FILE__ ) . '/inc/class-filter-options.php';

		add_filter( 'tophive/customize/register-controls', array( $this, 'register_control' ) );
		add_action( 'init', array( $this, 'custom_post_type' ), 0 );

		add_action( 'wp_ajax_tophive/save-section', array( $this, 'ajax_save' ), 0 );
		add_action( 'wp_ajax_tophive/load-sections', array( $this, 'ajax_load_sections' ), 0 );

		if ( is_customize_preview() ) {
			add_action( 'customize_preview_init', array( $this, 'preview_scripts' ) );
			add_action( 'customize_preview_init', array( $this, 'update_ms_id' ) );
		}

		add_action( 'customize_save', array( $this, 'backup_customize_default' ), 3 );
		add_action( 'customize_save_after', array( $this, 'save_customize_settings' ) );
		add_action( 'customize_controls_init', array( $this, 'track_customizing_header' ) );
		// add_action( 'wp_loaded', array( $this, 'track_customizing_header' ) );
		// Change Admin bar URL.
		add_action( 'add_admin_bar_menus', array( $this, 'add_admin_bar_menus' ) );

	}

	/**
	 * Adds the "Customize" link to the Toolbar.
	 *
	 * @see wp_admin_bar_customize_menu
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance.
	 * @global WP_Customize_Manager $wp_customize
	 */
	function wp_admin_bar_customize_menu( $wp_admin_bar ) {
		global $wp_customize;

		// Don't show for users who can't access the customizer or when in the admin.
		if ( ! current_user_can( 'customize' ) || is_admin() ) {
			return;
		}

		// Don't show if the user cannot edit a given customize_changeset post currently being previewed.
		if ( is_customize_preview() && $wp_customize->changeset_post_id() && ! current_user_can( get_post_type_object( 'customize_changeset' )->cap->edit_post, $wp_customize->changeset_post_id() ) ) {
			return;
		}

		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		if ( is_customize_preview() && $wp_customize->changeset_uuid() ) {
			$current_url = remove_query_arg( 'customize_changeset_uuid', $current_url );
		}

		$msid = MetafansCoreCustomizer_Multiple_Options::get_instance()->is_apply();

		$customize_url = add_query_arg( 'url', urlencode( $current_url ), wp_customize_url() );
		if ( is_customize_preview() ) {
			$customize_url = add_query_arg( array( 'changeset_uuid' => $wp_customize->changeset_uuid() ), $customize_url );
		}

		if ( $msid ) {
			$customize_url = add_query_arg(
				array(
					'autofocus' => array( 'control' => 'multiple_headers' ),
					'msid' => $msid,
				),
				$customize_url
			);
		}

		$wp_admin_bar->add_menu(
			array(
				'id'    => 'customize',
				'title' => __( 'Customize', 'tophive-pro' ),
				'href'  => $customize_url,
				'meta'  => array(
					'class' => 'hide-if-no-customize',
				),
			)
		);
		add_action( 'wp_before_admin_bar_render', 'wp_customize_support_script' );
	}

	/**
	 * Add customize link.
	 *
	 * @since 0.0.7
	 *
	 * @return void
	 */
	function add_admin_bar_menus() {
		remove_action( 'admin_bar_menu', 'wp_admin_bar_customize_menu', 40 );
		add_action( 'admin_bar_menu', array( $this, 'wp_admin_bar_customize_menu' ), 40 );
	}

	/**
	 * Tracking which the header is customizing.
	 *
	 * @return void
	 */
	function track_customizing_header() {
		$id = 0;
		if ( isset( $_REQUEST['msid'] ) ) {
			$id = absint( $_REQUEST['msid'] );
			if ( $id > 0 ) {
				$post = get_post( $id );
				if ( ! $post || ! 'tophive_ms' == get_post_type( $post ) ) {
					$id = 0;
				}
			}
		}
		update_option( 'tophive_customizing_header', $id, false );
	}

	function backup_customize_default() {
		MetafansCoreCustomizer_Multiple_Options::get_instance()->no_apply();
		$theme = get_option( 'stylesheet' );
		$mods = get_option( "theme_mods_$theme" );
		update_option( 'tophive_backup_theme_mods', $mods );
	}

	function save_customize_settings( $customizer ) {
		$msid = isset( $_POST['msid'] ) ? absint( $_POST['msid'] ) : false;
		$post = get_post( $msid );
		if ( $msid > 0 && $post ) {

			MetafansCoreCustomizer_Multiple_Options::get_instance()->no_apply();
			// Restore old old backup.
			$o_data  = get_post_meta( $msid, '_customize_fields', true );
			$restore_data = get_option( 'tophive_backup_theme_mods' );
			$new_data = json_decode( $post->post_content, true );

			if ( ! $new_data ) {
				$new_data = maybe_unserialize( $post->post_content );
			}

			if ( ! is_array( $new_data ) ) {
				$new_data = array();
			}

			if ( isset( $_POST['customize_fields'] ) ) {
				$customize_fields = wp_unslash( $_POST['customize_fields'] );
				$customize_fields = json_decode( $customize_fields, true );
				foreach ( $customize_fields as $field_id => $v ) {
					if ( ! isset( $o_data[ $field_id ] ) ) {
						$o_data[ $field_id ] = '';
					}
				}
			}

			update_post_meta( $msid, '_customize_fields', $o_data );

			$changeset_setting_ids = $customizer->unsanitized_post_values(
				array(
					'exclude_post_data' => true,
					'exclude_changeset' => false,
				)
			);

			if ( isset( $_POST['customized'] ) ) {
				$fields = json_decode( wp_unslash( $_POST['customized'] ), true );
				if ( is_array( $fields ) ) {
					foreach ( $fields as $setting_id => $v ) {
						$changeset_setting_ids[ $setting_id ] = 1;
					}
				}
			}

			$restore_data_keys = array();
			foreach ( $changeset_setting_ids as $setting_id => $_v ) {
				if ( isset( $o_data[ $setting_id ] ) ) {
					$setting = $customizer->get_setting( $setting_id );
					if ( $setting ) {
						$new_data[ $setting_id ] = $setting->post_value();
					}
					$restore_data_keys [ $setting_id ] = isset( $restore_data[ $setting_id ] ) ? $restore_data[ $setting_id ] : null;

					if ( ! isset( $o_data[ $setting_id ] ) ) {  // Need to update setting fields.
						$o_data[ $setting_id ] = null;
					}
				}
			}

			$current_lang = '';
			$default_lang = '';

			global $sitepress;
			if ( is_object( $sitepress ) ) {
				$current_lang = $sitepress->get_current_language();
				$default_lang = $sitepress->get_default_language();
			}

			// Save Current Settings.
			if ( $default_lang == $default_lang ) {
				$post_data = array(
					'ID' => $msid,
					'post_content' => maybe_serialize( $new_data ),
				);
				wp_update_post( $post_data );
			} else {
				update_post_meta( $msid, '_tophive_header_' . $current_lang, $new_data );
			}

			// Restore default theme mod.
			foreach ( $restore_data_keys as $k => $v ) {
				if ( is_null( $v ) ) {
					remove_theme_mod( $k );
				} else {
					set_theme_mod( $k, $v );
				}
			}

			// Update settings fields.
			update_post_meta( $msid, '_customize_fields', $o_data );

			MetafansCoreCustomizer_Multiple_Options::get_instance()->apply();
		}

	}

	function backup_original_data() {
		$msid = isset( $_REQUEST['msid'] ) ? absint( $_REQUEST['msid'] ) : '';
		if ( ! get_post_meta( $msid, '_check_backup', true ) ) {
			update_post_meta( $msid, '_check_backup', '1' );
		}
	}

	function update_ms_id() {
		$msid = isset( $_REQUEST['msid'] ) ? absint( $_REQUEST['msid'] ) : '';
		$uuid = isset( $_REQUEST['customize_changeset_uuid'] ) ? sanitize_text_field( $_REQUEST['customize_changeset_uuid'] ) : '';
		$builder_id = isset( $_REQUEST['builder_id'] ) ? sanitize_text_field( $_REQUEST['builder_id'] ) : 'header';
		if ( $msid ) {
			update_post_meta( $msid, '_changeset_uuid', $uuid );
		}
	}

	function preview_scripts() {
		wp_enqueue_script( 'tophive-multiple-sections', $this->get_url() . '/js/selective-refresh.js', array( 'customize-preview', 'customize-selective-refresh' ), false, true );
	}


	function ajax_save() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_send_json_error( __( 'Access denied', 'tophive-pro' ) );
		}

		check_ajax_referer( 'tophive_multiple_sections', '_nonce' );
		$do_remove = isset( $_REQUEST['do_remove'] ) ? true : false;

		if ( $do_remove ) {
			$msid = absint( $_REQUEST['msid'] );
			wp_delete_post( $msid, true );
			die( 'removed' );
		}

		MetafansCoreCustomizer_Multiple_Options::get_instance()->no_apply();

		$data = $_POST;
		$data = wp_unslash( $data );
		$builder_id = isset( $data['builder_id'] ) ? sanitize_text_field( $data['builder_id'] ) : '';
		$control_id = isset( $data['control_id'] ) ? sanitize_text_field( $data['control_id'] ) : '';
		$copy = isset( $data['copy'] ) ? sanitize_text_field( $data['copy'] ) : '';
		$current_msid = isset( $data['current_msid'] ) ? absint( $data['current_msid'] ) : '';

		$post_data = array(
			'post_title' => isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : '',
			'post_type' => 'tophive_ms',
			'post_status' => 'publish',
			'post_content' => 'publish',
		);
		if ( ! $post_data['post_title'] ) {
			$post_data['post_title'] = 'Untitled-' . date_i18n( 'Y-m-d H:i:s' );
		}

		$fields = isset( $data['customize_fields'] ) ? json_decode( $data['customize_fields'], true ) : array();
		foreach ( $fields as $k => $v ) {
			$_v = get_theme_mod( $k );
			if ( $_v ) {
				$fields[ $k ] = get_theme_mod( $k );
			} else {
				$fields[ $k ] = null;
			}
		}
		// $fields
		if ( ! $copy ) {
			$post_data['post_content'] = '';
		} else {
			if ( $current_msid ) {
				$copy_post = get_post( $current_msid );
				if ( $copy_post ) {
					$fields = json_decode( $copy_post->post_content, true );

					// For new version use serialize.
					if ( ! $fields ) {
						$fields = maybe_unserialize( $fields );
					}
				}
			}
			// For new version use serialize.
			$post_data['post_content'] = maybe_serialize( $fields );
		}

		if ( isset( $_POST['id'] ) && absint( $_POST['id'] ) > 0 ) {
			$id = absint( $_POST['id'] );
			$post_data['ID'] = $id;
			wp_update_post( $post_data );
		} else {
			$id = wp_insert_post( $post_data );
		}

		if ( ! is_wp_error( $id ) ) {
			// Backup Update.
			update_post_meta( $id, '_customize_fields', $fields );
			update_post_meta( $id, '_builder_id', $builder_id );
		} else {
			die( '' );
		}

		MetafansCoreCustomizer_Multiple_Options::get_instance()->apply();

		$this->render_list_item(
			array(
				'id' => $id,
				'title' => $post_data['post_title'],
				'url' => MetafansCoreCustomizer_Conditional::get_instance()->get_preview_url( $id ),
			),
			$control_id
		);

		die();
	}

	function render_list_item( $item_data, $control_id = '', $current = 0 ) {
		$class = '';
		if ( $current == $item_data['id'] ) {
			$text = __( 'Editing', 'tophive-pro' );
			$class = 'li-boxed-editing';
		} else {
			$text = __( 'Edit', 'tophive-pro' );
		}

		echo '<li class="li-boxed ' . $class . '" data-id="' . esc_attr( $item_data['id'] ) . '"><span class="li-boxed-label">' . esc_html( $item_data['title'] ) . '</span>
                    <a href="#" data-id="' . esc_attr( $item_data['id'] ) . '" data-url="' . esc_attr( $item_data['url'] ) . '" data-control-id="' . esc_attr( $control_id ) . '" class="load-tpl">' . $text . '</a>';

		if ( $item_data['id'] ) {
			echo '<a href="#" data-control-id="' . esc_attr( $control_id ) . '" data-id="' . esc_attr( $item_data['id'] ) . '" class="remove-tpl">' . __( 'Remove', 'tophive-pro' ) . '</a>';
		}

		echo '</li>';
	}

	function get_templates( $id = false ) {
		$posts = get_posts(
			array(
				'post_type' => 'tophive_ms',
				'orderby' => 'ID',
				'order' => 'ASC',
				'post_status' => 'any',
				'posts_per_page' => -1,
			)
		);

		$data = array();
		foreach ( $posts as $p ) {
			$url = MetafansCoreCustomizer_Conditional::get_instance()->get_preview_url( $p->ID );
			$url = ( $url ) ? $url : '';

			$current_lang = '';
			global $sitepress;
			if ( is_object( $sitepress ) ) {
				$current_lang = $sitepress->get_current_language();
			}
			$url = apply_filters( 'wpml_permalink', $url, $current_lang );

			$data[ $p->ID ] = array(
				'id' => $p->ID,
				'title' => $p->post_title,
				'url' => $url,
			);
		}
		return $data;
	}

	function ajax_load_sections() {
		if ( ! isset( $_GET['builder_id'], $_GET['control_id'] ) ) {
			die();
		}

		$msid = absint( $_GET['msid'] );
		$builder_id = absint( $_GET['builder_id'] );
		$control_id = sanitize_text_field( $_GET['control_id'] );
		$this->render_list_item(
			array(
				'id' => 0,
				'title' => __( 'Default', 'tophive-pro' ),
				'url' => home_url( '/' ),
			),
			$control_id,
			$msid
		);
		foreach ( $this->get_templates( $builder_id ) as $item_data ) {
			$this->render_list_item( $item_data, $control_id, $msid );
		}
		die();
	}

	function register_control( $controls ) {
		$controls['multiple_sections'] = dirname( __FILE__ ) . '/inc/class-multiple-sections-control.php';
		return $controls;
	}

	/**
	 *  Register Custom Post Type.
	 *
	 * @return void
	 */
	function custom_post_type() {

		$args = array(
			'label'                 => __( 'Header', 'tophive-pro' ),
			'hierarchical'          => false,
			'public'                => false,
			'show_ui'               => false,
			'show_in_menu'          => false,
			'menu_position'         => 5,
			'show_in_admin_bar'     => false,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => false,
			'capability_type'       => 'post',
			'supports'           => array( 'title', 'editor' ),
		);
		register_post_type( 'tophive_ms', $args );

	}


}
new MetafansCoreCustomizer_Module_Multiple_Headers();