<?php

class MetafansCoreCustomizer_Conditional_Checker {

	static $_instance;

	static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	function is_single( $type ) {
		if ( 'post' == $type ) {
			return is_single();
		} elseif ( 'page' == $type ) {
			return is_page();
		} else {
			return is_singular( $type );
		}
	}

	private function is_singular( $condition = null ) {
		$post_id = get_the_ID();

		if ( 'not_found404' == $condition['sub_name'] ) {
			return is_404();
		}

		if ( 'front_page' == $condition['sub_name'] ) {
			return is_front_page() && ! is_home();
		}

		$is = is_singular() && ! is_front_page();

		if ( $condition['sub_name'] ) {
			$data = explode( '/', $condition['sub_name'] );
			switch ( count( $data ) ) {
				case 1:
					$is = $this->is_single( $data[0] );
					break;
				case 2:
				case 3:
					$type = $data[1];
					switch ( $type ) {
						case 'in':
							$tax = $data[2];
							if ( $condition['sub_id'] > 0 ) {
								$is = $this->is_single( $data[0] ) && has_term( $condition['sub_id'], $tax, $post_id );
							} else {
								$is = $this->is_single( $data[0] );
							}
							break;
						case 'child_of':
							if ( $condition['sub_id'] > 0 ) {
								$is = $this->is_single( $data[0] );
								if ( $is ) {
									global $post;
									if ( ! $post->post_parent == $condition['sub_id'] ) {
										$is = false;
									}
								}
							}
							break;
						default: // all.
							$id = $post_id;
							if ( $condition['sub_id'] > 0 ) {
								if ( $id ) {
									$is = $this->is_single( $data[0] ) && $id == $condition['sub_id'];
									// Check if is blog page.
									if ( ! $is && is_home() ) {
										$is = get_option( 'page_for_posts' ) == $condition['sub_id'];
									}
								} else {
									$is = false;
								}
							} else {
								$is = $this->is_single( $data[0] );
							}
					}
					break;

			}
		}

		return $is;
	}
	function is_archive( $condition = null ) {
		$is = is_archive();
		switch ( $condition['sub_name'] ) {
			case 'front_page':
				return is_front_page() && ! is_home();
				break;
			case 'author':
				return is_author();
				break;
			case 'date':
				return is_author();
				break;
			default:
				// post_type_archive/product
				$info = explode( '/', $condition['sub_name'] );
				$n = count( $info );
				$term = get_queried_object();

				if ( $n > 0 ) {
					if ( $info[0] == 'post_type_archive' && ! is_tax() ) {
						$is = is_post_type_archive( $info[1] );
					} else {
						if ( $n > 1 && $condition['sub_id'] > 0 ) {
							if ( $info[1] == 'post_tag' ) {
								$cb = 'is_tag';
							} else {
								$cb = 'is_' . $info[1];
							}
							$is_tax = is_tax( $info[1] );
							if ( function_exists( $cb ) ) {
								$is_tax = call_user_func_array( $cb, array() );
							}
							if ( $condition['sub_id'] > 0 ) {
								return $term->term_id == $condition['sub_id'];
							} else {
								return $is_tax;
							}
						} else {
							if ( $n > 1 ) {
								$is = is_tax( $info[1] );
								if ( $info[1] == 'post_tag' ) {
									$cb = 'is_tag';
								} else {
									$cb = 'is_' . $info[1];
								}
								if ( function_exists( $cb ) ) {
									$is = call_user_func_array( $cb, array() );
								}
							}
						}
					}
				}
		}

		return $is;

	}
	function is( $condition ) {
		$is = false;

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
				 $is = $this->is_archive( $condition );
				break;
			case 'singular':
					$is = $this->is_singular( $condition );
				break;
			default:
				$is = true;
				break;
		}

		if ( $condition['type'] == 'exclude' ) {
			$is = ! $is;
		}

		return $is;
	}

	function is_match( $conditions ) {

		if ( empty( $conditions ) ) {
			return true;
		}
		if ( ! is_array( $conditions ) ) {
			return false;
		}
		foreach ( $conditions as $condition ) {
			if ( $this->is( $condition ) ) {
				return true;
			}
		}
		return false;
	}

	function check_user_role( $role ) {
		if ( ! $role || $role == 'all' ) {
			return true; // true for everyone
		}
		if ( 'logged_in' == $role ) {
			return is_user_logged_in();
		}
		if ( 'logged_out' == $role ) {
			return ! is_user_logged_in();
		}

		// Advanced check
		if ( ! is_user_logged_in() ) {
			return false;
		}

		return current_user_can( $role );

	}

}
