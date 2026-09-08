<?php
require_once dirname( __FILE__ ) . '/class-condition-checker.php';

class MetafansCoreCustomizer_Conditional {
	static $meta_key = '_tophive_conditionals';
	static $_instance;
	private $cached = null;
	function __construct() {
		if ( is_admin() ) {
			add_action( 'customize_controls_print_footer_scripts', array( $this, 'form_tpl' ) );
		}

		add_action( 'wp_ajax_tophive-pro/ajax/condition-data', array( $this, 'ajax_data' ) );
		add_action( 'wp_ajax_tophive-pro/ajax/condition-save', array( $this, 'ajax_save' ) );

		add_action( 'customize_controls_init', array( $this, 'scripts' ) );

	}

	function scripts( $conditionals = null ) {
		$id = null;
		if ( ! $conditionals ) {
			$id = isset( $_REQUEST['msid'] ) ? absint( $_REQUEST['msid'] ) : 0;
			$conditionals = get_post_meta( $id, self::$meta_key, true );
			if ( ! is_array( $conditionals ) ) {
				$conditionals = array();
			}
		}

		wp_localize_script(
			'jquery',
			'MetafansCoreCustomizer_Conditional',
			array(
				'id' => $id,
				'_nonce' => wp_create_nonce( 'tophive-pro/ajax/condition-data' ),
				'conditionals' => $conditionals,
				'l10n' => array(
					'loading' => __( 'Loading...', 'tophive-pro' ),
					'save' => __( 'Save', 'tophive-pro' ),
				),
			)
		);
	}

	function get_condition_by_id( $post_id ) {
		return get_post_meta( $post_id, self::$meta_key, true );
	}

	function get_all_conditionals( $post_type = 'tophive_ms' ) {
		if ( ! $post_type ) {
			$post_type = 'tophive_ms';
		}
		$query = new WP_Query(
			array(
				'post_type'     => $post_type,
				'post_status' => 'any',
				'posts_per_page' => -1,
				'meta_key'  => self::$meta_key,
				'orderby' => 'ID',
				'order' => 'asc',
			)
		);
		$posts = $query->get_posts();
		$conditions = array();
		if ( count( $posts ) ) {
			foreach ( $posts as $p ) {
				$conditions[ $p->ID ] = $this->get_condition_by_id( $p->ID );
			}
		}
		return $conditions;
	}

	function ajax_save() {
		check_admin_referer( 'tophive-pro/ajax/condition-data', '_nonce' );
		if ( ! isset( $_POST['msid'] ) ) {
			wp_die( 'invalid' );
		}

		$id = absint( $_POST['msid'] );
		$conditionals = isset( $_POST['conditionals'] ) ? wp_unslash( $_POST['conditionals'] ) : array();
		update_post_meta( $id, self::$meta_key, $conditionals );

		$title = sanitize_text_field( wp_unslash( $_REQUEST['title'] ) );
		if ( ! $title ) {
			$title = 'Untitled-' . date_i18n( 'Y-m-d H:i:s' );
		}

		$post = array(
			'ID' => $id,
			'post_title' => $title,
		);
		wp_update_post( $post );

		// return url vail for this conditions.
		//$url = add_query_arg( array( 'msid' => $id ), $this->get_preview_url( $id ) );
		$url = $this->get_preview_url( $id );
		$current_lang = '';
		global $sitepress;
		if ( is_object( $sitepress ) ) {
			$current_lang = $sitepress->get_current_language();
		}
		$url = apply_filters( 'wpml_permalink', $url, $current_lang );
		wp_send_json(
			array(
				'success' => true,
				'title' => $title,
				'url' => $url,
			)
		);
		die();
	}

	function ajax_data() {

		$name = isset( $_GET['name'] ) ? sanitize_text_field( $_GET['name'] ) : '';
		$sub_name = isset( $_GET['sub_name'] ) ? sanitize_text_field( $_GET['sub_name'] ) : '';
		$search = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
		// $html = sprintf( '<option value="%1$s">%2$s</option>', '', __( 'All', 'tophive-pro' ) );
		$options = array();
		$options[] = array(
			'id' => '-1',
			'text' => __( 'All', 'tophive-pro' ),

		);
		if ( $name == 'archive' ) {
			$info = explode( '/', $sub_name );
			if ( count( $info ) > 1 ) {
				$tax = $info[1];
				$terms = get_terms(
					array(
						'taxonomy' => $tax,
						'search' => $search,
						'orderby'           => 'name',
						'order'             => 'ASC',
						'number'             => 10,
					)
				);
				foreach ( $terms as $t ) {
					$options[] = array(
						'id' => $t->term_id,
						'text' => $t->name,
					);
				}
			}
		} elseif ( $name == 'singular' ) {
			$info = explode( '/', $sub_name );
			if ( count( $info ) > 1 ) {
				if ( $info[1] == 'all' || $info[1] == 'child_of' ) {
					$args = array(
						'posts_per_page' => 10,
						'post_type'      => $info[0],
						'orderby'        => 'title',
						'order'          => 'asc',
						's' => $search,
					);

					$query = new WP_Query( $args );
					if ( $query->have_posts() ) {
						foreach ( $query->get_posts() as $p ) {
							$options[] = array(
								'id'   => $p->ID,
								'text' => $p->post_title,
							);
						}
					}
				} elseif ( $info[1] == 'in' ) {
					$terms = get_terms(
						array(
							'taxonomy' => $info[2],
							'search' => $search,
							'orderby' => 'name',
							'order'  => 'ASC',
						)
					);
					foreach ( $terms as $t ) {
						$options[] = array(
							'id' => $t->term_id,
							'text' => $t->name,
						);
					}
				}
			}
		}

		wp_send_json(
			array(
				'results' => $options,
				'pagination' => false,
			)
		);

		die();

	}

	static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	function get_preview_url( $condition_id ) {
		if ( ! is_array( $condition_id ) ) {
			$conditions = $this->get_condition_by_id( $condition_id );
			if ( empty( $conditions ) || ! is_array( $conditions ) ) {
				return '';
			}
		} else {
			$conditions = $condition_id;
		}

		$condition = current( $conditions );
		$condition = wp_parse_args(
			$condition,
			array(
				'type' => '',
				'name' => '',
				'sub_name' => '',
				'sub_id' => '',
			)
		);

		switch ( $condition['name'] ) {
			case 'archive':
				$posts = get_posts(
					array(
						'numberposts' => 1,
					)
				);

				$p = current( $posts[0] );

				switch ( $condition['sub_name'] ) {
					case 'author':
						if ( count( $posts ) ) {
							return get_author_posts_url( $p );
						}
						break;
					case 'date':
						if ( count( $posts ) ) {
							return get_year_link( date( 'Y', strtotime( $p->post_date ) ) );
						}
						break;
					case 'search':
						return add_query_arg( array( 's' => 'a' ), home_url( '/' ) );
						break;
					default:
						if ( $condition['sub_name'] ) {
							$info = explode( '/', $condition['sub_name'] );
							if ( count( $info ) > 1 ) {
								if ( $info[0] == 'post_type_archive' ) {
									return add_query_arg( array( 'post_type' => $info[1] ), home_url( '/' ) );
								}
								if ( $condition['sub_id'] ) {
									$t = get_term( $condition['sub_id'], $info[1] );
									if ( $t && ! is_wp_error( $t ) ) {
										return get_term_link( $t );
									}
								} else {
									$terms = get_terms(
										array(
											'taxonomy' => $info[1],
											'number' => 1,
										)
									);

									if ( ! is_wp_error( $terms ) && count( $terms ) ) {
										return get_term_link( current( $terms ) );
									}
								}
							}
						}
						break;

				}

				if ( count( $posts ) ) {
					return get_year_link( date( 'Y', strtotime( $p->post_date ) ) );
				}

				break;
			case 'singular':
				switch ( $condition['sub_name'] ) {
					case 'front_page':
						return home_url( '/' );
						break;
					case 'attachment':
						$posts = get_posts(
							array(
								'numberposts' => 1,
								'post_type' => 'attachment',
							)
						);
						if ( count( $posts ) ) {
							return get_permalink( $posts[0] );
						}
						break;
					case 'not_found404':
						if ( get_option( 'permalink_structure' ) ) {
							return home_url( '/the_link_that_you_never_found' );
						} else {
							$posts = get_posts(
								array(
									'numberposts' => 1,
									'post_type' => 'any',
									'orderby' => 'ID',
									'order' => 'DESC',
								)
							);
							$not_fount_id = 1;
							if ( count( $posts ) ) {
								$not_fount_id = $posts[0]->ID;
							}
							return home_url( '/?p=' . $not_fount_id );
						}
						break;
					default:
						$info = explode( '/', $condition['sub_name'] );
						if ( count( $info ) > 1 ) {
							switch ( $info[1] ) {
								case 'all':
									if ( $condition['sub_id'] && $condition['sub_id'] > 0 ) {
										$post = get_post( $condition['sub_id'] );
										if ( $post ) {
											return get_permalink( $post );
										}
									}
									break;
								case 'in':
									$posts = get_posts(
										array(
											'numberposts' => 1,
											'post_type' => $info[0],
										)
									);
									if ( count( $posts ) ) {
										return get_permalink( $posts[0] );
									}

									if ( isset( $info[2] ) && $info[2] ) {

										if ( $condition['sub_id'] < 1 ) {
											$condition['sub_id'] = 0;
											$operator = 'NOT IN';
										} else {
											$operator = 'IN';
											$condition['sub_id'] = absint( $condition['sub_id'] );
										}

										$query = new WP_Query(
											array(
												'post_type' => $info[0],
												'posts_per_page' => 1,
												'tax_query' => array(
													array(
														'taxonomy' => $info[2],
														'field'    => 'term_id',
														'operator' => $operator,
														'terms'    => array( $condition['sub_id'] ),
													),
												),
											)
										);

										$posts = $query->get_posts();
										if ( count( $posts ) ) {
											return get_permalink( $posts[0] );
										}
									}

									break;
							}

							$posts = get_posts(
								array(
									'numberposts' => 1,
									'post_type' => $info[0],
								)
							);

							if ( count( $posts ) ) {
								return get_permalink( $posts[0] );
							}
						}
				}

				$posts = get_posts(
					array(
						'numberposts' => 1,
					)
				);
				if ( count( $posts ) ) {
					return get_permalink( $posts[0] );
				}

				break;
			default:
				return home_url( '/' );
				break;
		}

		return home_url( '/' );

	}

	function is( $condition_post_type = null ) {

		if ( is_customize_preview() || is_admin() ) {
			if ( isset( $_REQUEST['msid'] ) ) {
				$id = absint( $_REQUEST['msid'] );
				return $id;
			}
		}

		if ( ! is_null( $this->cached ) ) {
			return $this->cached;
		}

		$all_conditions = $this->get_all_conditionals( $condition_post_type );
		if ( empty( $all_conditions ) ) {
			return 0;
		}
		foreach ( $all_conditions as $condition_id => $_conditions ) {
			if ( ! empty( $_conditions ) && is_array( $_conditions ) ) {
				foreach ( $_conditions as $_cond ) {
					if ( MetafansCoreCustomizer_Conditional_Checker::get_instance()->is( $_cond ) ) {
						$this->cached = $condition_id;
						return $condition_id;
					}
				}
			}
		}
		return false;
	}

	function is_all() {
		return true;
	}

	function get_post_types() {
		$post_type_args = array(
			'show_in_nav_menus' => true,
		);

		if ( ! empty( $args['post_type'] ) ) {
			$post_type_args['name'] = $args['post_type'];
		}

		$_post_types = get_post_types( $post_type_args, 'objects' );

		$post_types  = [];

		foreach ( $_post_types as $post_type => $object ) {
			$post_types[ $post_type ] = $object->label;
		}

		return $post_types;
	}

	function get_all_single() {
		$post_types = get_post_types(
			array(
				'show_in_nav_menus' => true,
			),
			'objects'
		);

		$options = array();

		foreach ( $post_types as $post_type => $post_type_object ) {
			// $post_types_options[ 'single/' . $post_type ] =  sprintf( 'All %s', get_post_type_object( $post_type )->labels->singular_name );
			$post_type_taxonomies = get_object_taxonomies( $post_type, 'objects' );
			$post_type_taxonomies = wp_filter_object_list(
				$post_type_taxonomies,
				array(
					'public' => true,
					'show_in_nav_menus' => true,
				)
			);

			$tax = array();
			if ( count( $post_type_taxonomies ) ) {
				foreach ( $post_type_taxonomies as $slug => $object ) {
					$tax[ $post_type . '/in/' . $slug ] = sprintf( __( 'In %s', 'tophive-pro' ), $object->labels->singular_name );
				}
			}

			if ( $post_type_object->hierarchical ) {
				$tax[ $post_type . '/child_of' ] = __( 'Child of', 'tophive-pro' );
			}

			if ( count( $tax ) ) {
				$options[ $post_type ] = array(
					'label' => $post_type_object->labels->singular_name,
					'type' => $post_type,
					'options' => array_merge(
						array( $post_type . '/all' => sprintf( __( 'All %s', 'tophive-pro' ), $post_type_object->labels->name ) ),
						$tax
					),
				);
			} else {
				$options[ $post_type . '/all' ] = $post_type_object->labels->singular_name;
			}
		}

		return $options;
	}

	function get_all_archive() {

		$taxonomies = [];
		foreach ( $this->get_post_types() as $post_type => $label ) {
			$post_type_object = get_post_type_object( $post_type );
			$post_type_taxonomies = get_object_taxonomies( $post_type, 'objects' );
			$post_type_taxonomies = wp_filter_object_list(
				$post_type_taxonomies,
				array(
					'public' => true,
					'show_in_nav_menus' => true,
				)
			);
			$tax = array();

			if ( $post_type_object->has_archive ) {
				$tax[ 'post_type_archive/' . $post_type ] = $post_type_object->label;
			}

			if ( count( $post_type_taxonomies ) ) {
				foreach ( $post_type_taxonomies as $slug => $object ) {
					$tax[ $post_type . '/' . $slug ] = $object->label;
				}
			}

			if ( count( $tax ) ) {
				$taxonomies[ $post_type ] = array(
					'label' => $post_type_object->labels->name . ' ',
					'post_type' => $post_type,
					'options' => $tax,
				);
			}
		}

		return $taxonomies;
	}

	function form_tpl() {
		$id = isset( $_REQUEST['msid'] ) ? absint( $_REQUEST['msid'] ) : '';
		$title = '';
		if ( $id > 0 ) {
			$title = get_the_title( $id );
		}
		?>
		<script type="text/html" id="tmpl-tophive-condition">
			<div class="cond-wrapper">
				<div class="cond-title">
					<input type="text" value="<?php echo esc_attr( $title ); ?>" class="cond-name" >
				</div>
				<div class="cond-list">

				</div>
			   <div class="cond-actions">
				   <a href="#" class="button button-secondary cond-add"><?php _e( 'Add Condition', 'tophive-pro' ); ?></a>
				   <a href="#" class="button button-primary button cond-save"><?php _e( 'Save', 'tophive-pro' ); ?></a>
			   </div>
			</div>
		</script>
		<script type="text/html" id="tmpl-tophive-condition-row">
			<div class="cond-item-wrapper">
			   <div class="cond-item">
				   <div class="cond-col cond--type hide">
					   <select class="cond-type" data-setting="type">
						   <option <# if ( data.type === 'include' ) { #> selected="selected" <# } #>  value="include"><?php _e( 'Include', 'tophive-pro' ); ?></option>
					   </select>
				   </div>
				   <div class="cond-col cond-lv-1">
					   <select data-setting="name">
						   <option value=""><?php _e( 'Choose...', 'tophive-pro' ); ?></option>
						   <optgroup label="General">
							   <option <# if ( data.name === 'general' ) { #> selected="selected" <# } #> value="general"><?php _e( 'Entire Site', 'tophive-pro' ); ?></option>
							   <option <# if ( data.name === 'archive' ) { #> selected="selected" <# } #> value="archive"><?php _e( 'Archives', 'tophive-pro' ); ?></option>
							   <option <# if ( data.name === 'singular' ) { #> selected="selected" <# } #> value="singular"><?php _e( 'Singular', 'tophive-pro' ); ?></option>
						   </optgroup>
					   </select>
				   </div>

				   <div class="cond-col cond-lv-2 <# if ( data.name !== 'archive' && data.name !== 'singular' ) { #> hide <# } #>">
					   <select class="cond--archive <# if ( data.name !== 'archive' ) { #> hide <# } #>" data-setting="sub_name">
						   <option value=""><?php _e( 'All Archives', 'tophive-pro' ); ?></option>
						   <option <# if ( data.sub_name === 'author' ) { #> selected="selected" <# } #> value="author"><?php _e( 'Author Archive', 'tophive-pro' ); ?></option>
						   <option <# if ( data.sub_name === 'date' ) { #> selected="selected" <# } #> value="date"><?php _e( 'Date Archive', 'tophive-pro' ); ?></option>
						   <option <# if ( data.sub_name === 'search' ) { #> selected="selected" <# } #> value="search"><?php _e( 'Search Results', 'tophive-pro' ); ?></option>
						   <?php
							foreach ( $this->get_all_archive() as $k => $options ) {
								if ( is_string( $options ) ) {
									echo '<option <# if ( data.sub_name === \'' . $k . '\' ) { #> selected="selected" <# } #>  value="' . $k . '">' . $options . '</option>';
								} else {
									echo '<optgroup label="' . esc_attr( $options['label'] ) . '">';
									foreach ( $options['options'] as $_k => $l ) {
										echo '<option <# if ( data.sub_name === \'' . $_k . '\' ) { #> selected="selected" <# } #>  value="' . $_k . '">' . $l . '</option>';
									}
									echo '</optgroup>';
								}
							}
							?>
					   </select>

					   <select class="cond--singular <# if ( data.name !== 'singular' ) { #> hide <# } #>" data-setting="sub_name">
						   <option value=""><?php _e( 'All Singular', 'tophive-pro' ); ?></option>
						   <option <# if ( data.sub_name === 'front_page' ) { #> selected="selected" <# } #> value="front_page"><?php _e( 'Front Page', 'tophive-pro' ); ?></option>
						   <?php
							foreach ( $this->get_all_single() as $k => $options ) {
								if ( is_string( $options ) ) {
									echo '<option <# if ( data.sub_name === \'' . $k . '\' ) { #> selected="selected" <# } #> value="' . $k . '">' . $options . '</option>';
								} else {
									echo '<optgroup label="' . esc_attr( $options['label'] ) . '">';
									foreach ( $options['options'] as $_k => $l ) {
										echo '<option <# if ( data.sub_name === \'' . $_k . '\' ) { #> selected="selected" <# } #> value="' . $_k . '">' . $l . '</option>';
									}
									echo '</optgroup>';
								}
							}
							?>
						   <option <# if ( data.sub_name === 'attachment' ) { #> selected="selected" <# } #>  value="attachment"><?php echo get_post_type_object( 'attachment' )->label; ?></option>
						   <option <# if ( data.sub_name === 'not_found404' ) { #> selected="selected" <# } #> value="not_found404"><?php _e( '404 Page', 'tophive-pro' ); ?></option>
					   </select>

				   </div>
				   <div class="cond-col cond-lv-3 <# if ( ! data.sub_id &&  data.sub_name &&  data.sub_name.indexOf('/') < 1 ) { #> hide <# } #>">
					   <select class="cond-sub-id" name="_sub_id" data-setting="sub_id">
						   <# if ( data.sub_id ) { #>
							   <option selected="selected" value="{{ data.sub_id }}">{{ data.sub_id_label }}</option>
						   <# } else { #>
							   <option selected="selected" value=""><?php _e( 'All', 'tophive-pro' ); ?></option>
						   <# } #>
					   </select>
				   </div
			   </div>
				<a href="#" class="cond-col cond-remove"><span class="dashicons dashicons-no-alt"></span></a>
			</div>
		</script>

		<?php
	}
}
MetafansCoreCustomizer_Conditional::get_instance();
