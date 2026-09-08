<?php

/**
 * Class MetafansCoreCustomizer_Module_Base
 * Extends by other class
 */
class MetafansCoreCustomizer_Module_Base {

	public $is_assets = false;
	private $option_name = 'tophive_pro_settings';

	function get_id() {
		$class_name = get_class( $this );
		$class_name = str_replace( 'MetafansCoreCustomizer_Module_', '', $class_name );
		$module_id = strtolower( $class_name );
		return str_replace( '_', '-', $module_id );
	}
	function get_url() {
		$module_id = $this->get_id();
		return MetafansCoreCustomizer()->get_url( '/' . MetafansCoreCustomizer()->modules_path ) . "/{$module_id}";
	}

	function get_patch() {
		$module_id = $this->get_id();
		return MetafansCoreCustomizer()->get_path( '/' . MetafansCoreCustomizer()->modules_path ) . "/{$module_id}";
	}

	function get_file_info( $file ) {
		$files = explode( '.', $file );
		$n = count( $files );
		if ( $n == 1 ) {
			return array(
				'name' => $file,
				'ext' => '',
			);
		}
		$ext = $files[ $n - 1 ];
		unset( $files[ $n - 1 ] );
		return array(
			'name' => join( '.', $files ),
			'ext' => $ext,
		);
	}

	function add_css( $file = null, $id = null, $full_path = false ) {
		if ( ! $id ) {
			$id = str_replace( '.css', '', basename( $file ) );
		}
		$file_name = $file;
		$file_name_rtl = null;
		if ( $file && $full_path ) {
			$full_url = $file;
		} else {
			$suffix = tophive_metafans()->get_asset_suffix();
			if ( ! $file ) {
				$id = $this->get_id();
				$file_name = 'style' . $suffix . '.css';
				if ( is_rtl() ) {
					$file_name_rtl = 'style-rtl' . $suffix . '.css';
				}
			} else {
				$file_info = $this->get_file_info( $file );
				if ( $suffix && false === strpos( $file_info['name'], $suffix ) ) {
					$file_name = $file_info['name'] . $suffix . '.' . $file_info['ext'];
					if ( is_rtl() ) {
						$file_name_rtl = $file_info['name'] . '-rtl' . $suffix . '.' . $file_info['ext'];
					}
				} elseif ( is_rtl() ) {
					$file_name_rtl = $file_info['name'] . '-rtl' . '.' . $file_info['ext'];
				}
			}

			$path = $this->get_patch();
			$url  = $this->get_url();

			$sub_folder = $this->is_assets ? '/assets/css/' : '/css/';

			if ( is_rtl() && $file_name_rtl ) {
				$file_rtl_path = $path . "{$sub_folder}{$file_name_rtl}";
				if ( file_exists( $file_rtl_path ) ) {
					$full_url = $url . "{$sub_folder}{$file_name_rtl}";
					MetafansCoreCustomizer()->add_css( $id, $full_url );
					return;
				}
			}

			$full_url = $url . "{$sub_folder}{$file_name}";
		}

		MetafansCoreCustomizer()->add_css( $id, $full_url );
	}

	function add_js( $file = null, $id = null, $full_path = false ) {
		if ( ! $id ) {
			$id = str_replace( '.js', '', basename( $file ) );
		}
		if ( $file && $full_path ) {
			$full_url = $file;
		} else {
			$suffix = tophive_metafans()->get_asset_suffix();
			if ( ! $file ) {
				$id = $this->get_id();
				$file = 'script' . $suffix . '.js';
			} else {
				$file_info = $this->get_file_info( $file );
				if ( $suffix && ! strpos( $file_info['name'], $suffix ) ) {
					$file = $file_info['name'] . $suffix . '.' . $file_info['ext'];
				}
			}

			$sub_folder = $this->is_assets ? '/assets/js/' : '/js/';
			$full_url = $this->get_url() . "{$sub_folder}{$file}";
		}

		MetafansCoreCustomizer()->add_js( $id, $full_url );
	}

	function add_local_js_args( $key, $value ) {
		MetafansCoreCustomizer()->add_local_scripts_args( $key, $value );
	}

	function set_key_value( $key, $value ) {
		$class_name = get_class( $this );
		wp_cache_delete( $this->option_name, 'options' );
		$data = get_option( $this->option_name );
		if ( ! is_array( $data ) ) {
			$data = array();
		}
		if ( ! isset( $data[ $class_name ] ) || ! is_array( $data[ $class_name ] ) ) {
			$data[ $class_name ] = array();
		}
		$data[ $class_name ][ $key ] = $value;
		update_option( $this->option_name, $data );
	}

	function get_settings() {
		$class_name = get_class( $this );
		$data = get_option( $this->option_name );
		if ( ! is_array( $data ) ) {
			return array();
		}
		if ( ! isset( $data[ $class_name ] ) ) {
			return array();
		}
		return $data[ $class_name ];
	}

	function save( $new_data = array() ) {
		$class_name = get_class( $this );
		$data = get_option( $this->option_name );
		if ( ! is_array( $data ) ) {
			$data = array();
		}
		$data[ $class_name ] = $new_data;
		update_option( $this->option_name, $data );
	}

}
