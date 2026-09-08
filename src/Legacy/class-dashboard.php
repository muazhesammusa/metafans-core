<?php
if ( is_admin() ) {
	class MetafansCoreCustomizer_Dashboard {
		public $url;
		public $doing;

		function __construct() {
			// add_action( 'wp_ajax_tophive_pro_module', array( $this, 'ajax' ) );
			if ( is_admin() ) {
				add_action( 'tophive/dashboard/main', array( $this, 'box_modules' ) );
				add_action( 'tophive/backend/admin/theme-page', array( $this, 'box_assets' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
			}
			$this->url = admin_url( 'admin.php?page=tophive' );
			// Setting area.
			add_action( 'admin_init', array( $this, 'setup' ) );

		}


		function setup() {
			if ( isset( $_REQUEST['module'] ) ) {
				$module = sanitize_text_field( $_REQUEST['module'] );
				// Module must active to show settings
				if ( isset( MetafansCoreCustomizer()->installed_modules[ $module ] ) ) {
					if ( method_exists( $module, 'settings' ) ) {
						$this->doing = $module;
					}
				}

				if ( $this->doing ) {
					add_filter( 'tophive/dashboard/content_cb', array( $this, 'change_page_settings' ) );
				}
			}
		}

		function change_page_settings() {
			return array( $this, 'module_settings_page' );
		}

		function module_settings_page() {

			$module_info = MetafansCoreCustomizer()->modules[ $this->doing ];
			$module      = MetafansCoreCustomizer()->get_module( $this->doing, true );
			if ( ! $module || ! MetafansCoreCustomizer()->is_enabled_module( $this->doing ) ) {
				return false;
			}
			$fields = $module->settings();

			require_once get_template_directory() . '/inc/class-metabox-fields.php';
			$field_builder = new Metafans_Form_Fields();
			$field_builder->set_group_name( 'tophive_module_settings' );
			$field_builder->add_fields( $fields );
			$field_builder->using_tabs( false );

			$url = add_query_arg( array( 'module' => $this->doing ), $this->url );

			if ( isset( $_POST['tophive_pro_form_submit'] ) ) {
				$values = $field_builder->get_submitted_values();
				$module->save( $values );
				?>
				<div class="updated is-dismissible">
					<p>
						<?php _e( 'Your settings have been saved.', 'tophive-pro' ); ?>
					</p>
				</div>
				<?php
				if ( method_exists( $module, 'after_save' ) ) {
					$module->after_save( $this );
				}
				do_action( 'tophive-pro/after-module-saved', $this );

				$fields = $module->settings();
				$field_builder->reset_fields();
				$field_builder->add_fields( $fields );
			}

			$field_builder->set_values( $module->get_settings() );

			?>
			<div id="plugin-filter" class="cd-row metabox-holder">
				<hr class="wp-header-end">
				<div class="cd-main module-pro-settings" style="width: 100%;">
					<p>
						<a class="button button-secondary"
						   href="<?php echo esc_url( $this->url ); ?>"><?php _e( 'Back', 'tophive-pro' ); ?></a>
					</p>
					<div class="cd-box">
						<div class="cd-box-top"><?php printf( __( '%s Settings', 'tophive-pro' ), $module_info['name'] ); ?></div>
						<div class="cd-box-content">
							<?php
							if ( method_exists( $module, 'before_form' ) ) {
								$module->before_form();
							}
							?>
							<form method="post" action="<?php echo esc_url( $url ); ?>">
								<?php
								$field_builder->render();
								submit_button();
								?>
								<input type="hidden" value="1" name="tophive_pro_form_submit">
							</form>
							<?php
							if ( method_exists( $module, 'after_form' ) ) {
								$module->after_form();
							}
							?>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		function ajax() {
			check_admin_referer( 'tophive_pro_module', '_nonce' );
			$class_name = isset( $_REQUEST['name'] ) ? $_REQUEST['name'] : '';
			$doing      = isset( $_REQUEST['doing'] ) ? sanitize_text_field( $_REQUEST['doing'] ) : 1;
			$args       = array(
				'success' => false,
			);
			switch ( $doing ) {
				case 'toggle_module_assets';
					$class_name = sanitize_text_field( $class_name );
					$enable     = isset( $_REQUEST['enable'] ) ? sanitize_text_field( $_REQUEST['enable'] ) : 'off';
					if ( $enable != 'on' ) {
						$enable = 'off';
					}
					update_option( 'tophive_pro_assets_compress', $enable );
					$args['success'] = true;
					$args['__']      = $enable;
					break;
				case 'toggle_module';

					if ( is_array( $class_name ) ) {
						foreach ( $class_name as $sub_class_name => $enable ) {
							if ( $enable ) {
								MetafansCoreCustomizer()->enable_module( $sub_class_name );
							} else {
								MetafansCoreCustomizer()->disable_module( $sub_class_name );
							}
						}
					} else {
						$enable = isset( $_REQUEST['enable'] ) ? absint( $_REQUEST['enable'] ) : 1;
						if ( $enable ) {
							MetafansCoreCustomizer()->enable_module( $class_name );
						} else {
							MetafansCoreCustomizer()->disable_module( $class_name );
						}
					}

					$args['success'] = true;

					break;
			}

			MetafansCoreCustomizer_Assets::get_instance()->clear();
			wp_send_json( $args );
		}

		function scripts( $id ) {
			if ( $id != 'appearance_page_tophive' ) {
				return;
			}
			$suffix = tophive_metafans()->get_asset_suffix();
			wp_enqueue_script( 'tophive-pro-dashboard', MetafansCoreCustomizer()->get_url() . '/assets/js/admin/dashboard' . $suffix . '.js', array( 'jquery' ), false );
			wp_enqueue_style( 'tophive-pro-dashboard', MetafansCoreCustomizer()->get_url() . '/assets/css/admin/admin' . $suffix . '.css', array(), false );
			wp_localize_script(
				'tophive-pro-dashboard',
				'CD_Dashboard',
				array(
					'_nonce'          => wp_create_nonce( 'tophive_pro_module' ),
					'updating'        => __( 'Updating settings...', 'tophive-pro' ),
					'updated'         => __( 'Updated settings.', 'tophive-pro' ),
					'error'           => __( 'Error updating settings.', 'tophive-pro' ),
					'regenerate_done' => __( 'Regenerate assets completed', 'tophive-pro' ),
					'regenerate_url'  => add_query_arg(
						array(
							'regenerate_assets' => 1,
							'regenerate_nonce'  => wp_create_nonce( 'regenerate_nonce' ),
						),
						home_url( '/' )
					),
				)
			);

			wp_enqueue_script( 'tophive-metabox', esc_url( get_template_directory_uri() ) . '/assets/js/admin/metabox' . $suffix . '.js', array( 'jquery' ), Metafans::$version, true );
			wp_enqueue_style( 'tophive-metabox', esc_url( get_template_directory_uri() ) . '/assets/css/admin/metabox' . $suffix . '.css', false, Metafans::$version );

		}

		function render_module( $class_name, $args, $sub = false ) {
			$checked = MetafansCoreCustomizer()->is_enabled_module( $class_name );
			$reload  = isset( $args['reload'] ) && $args['reload'] ? '1' : 0;
			if ( $args['parent'] && ! $sub ) {
				return;
			}
			if ( $sub ) {
				$parent_args   = MetafansCoreCustomizer()->get_module( $args['parent'] );
				$parent_enable = MetafansCoreCustomizer()->is_enabled_module( $args['parent'] );
				if ( ! $parent_args['can_toggle'] ) {
					$checked            = 0;
					$parent_enable      = 0;
					$args['can_toggle'] = false;
				}
			} else {
				if ( ! $args['can_toggle'] ) {
					$checked = 0;
				}
				$parent_enable = true;
			}

			$list_only = false;
			if ( isset( $args['list_only'] ) ) {
				$list_only = $args['list_only'];
			}

			?>
			<div class="cd-module-item <?php echo $args['parent'] ? 'cd-sub-module' : 'cd-top-module'; ?>" data-parrent-module="<?php echo esc_attr( $args['parent'] ); ?>">
				<div class="cd-module-toggle">
					<?php if ( ! $list_only ) { ?>
						<div class="onoffswitch">
							<input type="checkbox" name="<?php echo $class_name; ?>"
								   data-reload="<?php echo $reload; ?>"
								   data-parent="<?php echo esc_attr( $args['parent'] ); ?>"
								   value="1" class="cd-onoff-module onoffswitch-checkbox"
								<?php
								if ( ! $args['can_toggle'] || ( $sub && ! $parent_enable ) ) {
									echo 'disabled="disabled"';
								}
								?>
								   id="onoff-module-<?php echo $class_name; ?>" <?php checked( $parent_enable ? $checked : 0, 1 ); ?>>
							<label class="onoffswitch-label" for="onoff-module-<?php echo $class_name; ?>">
								<span class="onoffswitch-inner"></span>
								<span class="onoffswitch-switch"></span>
							</label>
						</div>
					<?php } ?>
				</div>
				<div class="cd-module-info">
					<div class="cd-module-name"><?php echo $args['name'];

					if ( ! $args['can_toggle'] && $args['toggle_disable_notice'] ) {
						echo '<span class="cd-toggle-disable-notice">' . $args['toggle_disable_notice'] . '</span>';
					}

					?></div>
					<?php
					if ( $args['desc'] ) {
						echo '<div class="cd-module-desc">' . $args['desc'] . '</div>';
					}

					if ( ! $list_only ) {
						if ( method_exists( $class_name, 'settings' ) && MetafansCoreCustomizer()->is_enabled_module( $class_name ) ) {
							$link = add_query_arg( array( 'module' => $class_name ), $this->url );
							echo '<a class="cd-module-setting-link cd-module-link" href="' . esc_url( $link ) . '">' . __( 'Settings', 'tophive-pro' ) . '</a>';
						}
					}

					if ( $args['doc_link'] ) {
						if ( $args['doc_text'] ) {
							$text_link = $args['doc_text'];
						} else {
							$text_link = __( 'Learn more &rarr;', 'tophive-pro' );
						}
						echo '<a class="cd-module-doc-link" target="_blank" href="' . esc_url( $args['doc_link'] ) . '">' . $text_link . '</a>';
					}
					?>
				</div>
			</div>
			<?php

		}

		function box_modules() {
			?>
			<div class="cd-box">
				<div class="cd-box-top"><?php _e( 'Metafans Pro Modules', 'tophive-pro' ); ?></div>
				<div class="cd-box-content cd-modules">
					jascbjsvbsjvbsjdvhb
					<?php
					foreach ( MetafansCoreCustomizer()->modules as $class_name => $args ) {
						$this->render_module( $class_name, $args );
						if ( $args['sub_modules'] ) {
							foreach ( $args['sub_modules'] as $sub_class_name ) {
								$this->render_module( $sub_class_name, MetafansCoreCustomizer()->get_module( $sub_class_name ), true );
							}
						}
					}
					?>
				</div>
			</div>
			<?php
		}

		function box_assets() {

			$is_writable = true;
			$save_value  = get_option( 'tophive_pro_assets_compress', 'on' );
			if ( ! $is_writable ) {
				$save_value = '';
			}
			?>
			<div class="cd-box">
				<a href="#" class="tophive-regenerate-assets tophive-admin-small-button <?php echo $save_value == 'on' ? '' : 'tophive-hide-none'; ?>"><?php _e( 'Regenerate Assets', 'tophive-pro' ); ?></a>
				<p class="regeneration-completed"></p>
			</div>
			<?php
		}

	} // end class

	new MetafansCoreCustomizer_Dashboard();

}
