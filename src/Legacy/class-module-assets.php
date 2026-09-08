<?php

class MetafansCoreCustomizer_Assets {
	static $_instance;
	public $css_file_name = 'tophive-pro';
	public $js_file_name = 'tophive-pro';
	public $save_dir = '';
	public $save_url = '';
	public $home_url = '';
	public $folder = 'tophive-pro';
	static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
			$uploads = wp_get_upload_dir();
			self::$_instance->save_dir = $uploads['basedir'] . '/' . self::$_instance->folder;
			self::$_instance->save_url = $uploads['baseurl'] . '/' . self::$_instance->folder;
			if ( is_admin() && current_user_can( 'manage_options' ) ) {
				add_action( 'tophive-pro/module-status-changed', array( self::$_instance, 'clear' ) );
				if ( isset( $_GET['clear'] ) ) {
					self::$_instance->clear();
				}
			}
			add_action( 'wp', array( self::$_instance, 'maybe_regenerate_assets' ) );
		}
		return self::$_instance;
	}

	/**
	 * Maybe create assets folder.
	 *
	 * @since 0.0.5
	 *
	 * @return bool
	 */
	public function maybe_create_folder() {
		if ( ! is_dir( $this->save_dir ) ) {
			return wp_mkdir_p( $this->save_dir );
		} else {
			return is_writable( $this->save_dir );
		}
	}

	function maybe_regenerate_assets() {
		if ( current_user_can( 'manage_options' ) ) {
			if ( isset( $_GET['regenerate_assets'] ) && $_GET['regenerate_assets'] ) {
				$nonce = isset( $_GET['regenerate_nonce'] ) ? $_GET['regenerate_nonce'] : '';
				if ( wp_verify_nonce( $nonce, 'regenerate_nonce' ) ) {
					$this->clear();
				}
			}
		}
	}

	function get_home_url( $blog_id = null, $path = '', $scheme = null ) {

		if ( $this->home_url ) {
			return $this->home_url;
		}

		global $pagenow;
		$orig_scheme = $scheme;
		if ( empty( $blog_id ) || ! is_multisite() ) {
			$url = get_option( 'home' );
		} else {
			switch_to_blog( $blog_id );
			$url = get_option( 'home' );
			restore_current_blog();
		}

		if ( ! in_array( $scheme, array( 'http', 'https', 'relative' ) ) ) {
			if ( is_ssl() && ! is_admin() && 'wp-login.php' !== $pagenow ) {
				$scheme = 'https';
			} else {
				$scheme = parse_url( $url, PHP_URL_SCHEME );
			}
		}

		$url = set_url_scheme( $url, $scheme );

		if ( $path && is_string( $path ) ) {
			$url .= '/' . ltrim( $path, '/' );
		}

		$this->home_url = trailingslashit( $url );
		return $this->home_url;
	}

	function url_to_path( $url ) {
		$home = $this->get_home_url();
		if ( strpos( $url, $home ) !== false ) {
			$path = str_replace( $home, ABSPATH, $url );
		} else {
			$path = $url;
		}

		return $path;
	}

	/**
	 * Remove spaces and new line of css content.
	 *
	 * @since 0.0.1
	 * @since 0.0.4
	 *
	 * @param string $css
	 * @return string
	 */
	function min_css( $css ) {
		if ( trim( $css ) == '' ) {
			return;
		}
		$css = str_replace( array( "\r\n", "\r", "\n", "\t" ), '', $css );
		return $css;
	}

	function clear() {

		if ( ! function_exists( 'list_files' ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
		}
		$files = list_files( $this->save_dir );
		foreach ( $files as $key => $file ) {
			@unlink( $file );
		}

		delete_transient( 'tophive-pro-css-all' );
		delete_transient( 'tophive-pro-js-all' );
	}

	/**
	 * Put content to file.
	 *
	 * @param string   $file
	 * @param string   $contents
	 * @param int|bool $mode
	 * @param string   $version
	 * @return bool
	 */
	public function put_contents( $file, $contents, $mode = false, $version = false ) {
		global $wp_filesystem;
		if ( ! $filehandle = @fopen( $file, 'w+' ) ) {
			unlink( $file );
			return false;
		}
		$timezone_string = get_option( 'timezone_string' );
		if ( ! $timezone_string ) {
			$timezone_string = 'UTC+0';
		}
		$contents = '/*Created: ' . date_i18n( 'Y-m-d H:i:s' ) . " {$timezone_string}\nVersion: {$version}*/\n" . $contents;

		mbstring_binary_safe_encoding();

		$bytes_written = fwrite( $filehandle, $contents );
		if ( false === $bytes_written || strlen( $contents ) !== $bytes_written ) {
			fclose( $filehandle );
			unlink( $file );
			reset_mbstring_encoding();
			return false;
		}
		reset_mbstring_encoding();
		fclose( $filehandle );
		$wp_filesystem->chmod( $file, FS_CHMOD_FILE );
	}

	function compress_css( $files = array() ) {
		$enable = get_option( 'tophive_pro_assets_compress', 'on' );

		if ( ! $this->maybe_create_folder() ) {
			return $files;
		}

		if ( 'off' == $enable ) {
			return $files;
		}
		// Do not compress file when debug enabled.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return $files;
		}

		$compress = false;
		$hash = wp_hash( wp_json_encode( $files ) );
		$key = $this->css_file_name . '-' . $hash;
		$file_name = $key . '.css';
		$file = $this->save_dir . '/' . $file_name;

		$cache_data = get_transient( $file_name );
		// Ty to get cache.
		if ( false !== $cache_data && is_array( $cache_data ) ) {
			foreach ( (array) $cache_data['files'] as $id ) {
				unset( $files[ $id ] );
			}
			if ( file_exists( $this->save_dir . '/' . $file_name ) ) {
				$files[ $key ] = array(
					'url' => $this->save_url . '/' . $file_name,
					'ver' => $cache_data['ver'],
				);
				return $files;
			}
		}

		$code = '';
		global $wp_filesystem;
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		WP_Filesystem();

		$files_compress = array();
		foreach ( $files as $id => $url ) {
			$path = $this->url_to_path( $url );
			if ( $path != $url ) {
				$compress = true;
				$css = $wp_filesystem->get_contents( $path );
				$css = $this->min_css( $css );
				if ( $css ) {
					$css .= "\n";
				}
				$css = "/*Module: {$id}: {$url} */\r\n" . $css;
				$code .= $css;
				unset( $files[ $id ] );
				$files_compress[] = $id;
			}
		}

		$time = date( 'Ymdhis' );
		$this->put_contents( $file, $code, 0644, $time );

		if ( $compress ) {
			$files[ $key ] = array(
				'url' => $this->save_url . '/' . $file_name,
				'ver' => $time,
			);
		}

		set_transient(
			$file_name,
			array(
				'files' => $files_compress,
				'ver' => $time,
			),
			24 * HOUR_IN_SECONDS
		);

		return $files;
	}

	function compress_js( $files = array() ) {
		$enable = get_option( 'tophive_pro_assets_compress', 'on' );
		if ( 'off' == $enable ) {
			return $files;
		}
		if ( ! $this->maybe_create_folder() ) {
			return $files;
		}
		// Do not compress file when debug enabled.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return $files;
		}

		$compress = false;
		$hash = wp_hash( wp_json_encode( $files ) );
		$key = $this->css_file_name . '-' . $hash;
		$file_name = $key . '.js';
		$file = $this->save_dir . '/' . $file_name;

		$cache_data = get_transient( $file_name );
		// To to get cache.
		if ( false !== $cache_data && is_array( $cache_data ) ) {
			foreach ( (array) $cache_data['files'] as $id ) {
				unset( $files[ $id ] );
			}

			if ( file_exists( $this->save_dir . '/' . $file_name ) ) {
				$files[ $key ] = array(
					'url' => $this->save_url . '/' . $file_name,
					'ver' => $cache_data['ver'],
				);
				return $files;
			}
		}

		$code = '';
		global $wp_filesystem;
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		WP_Filesystem();
		$files_compress = array();
		foreach ( $files as $id => $arg ) {
			if ( is_array( $arg ) ) {
				$arg = wp_parse_args(
					$arg,
					array(
						'deps' => '',
						'url' => '',
						'ver' => '',
					)
				);

				$url = $arg['url'];
			} else {
				$url = $arg;
			}

			$path = $this->url_to_path( $url );

			if ( $path != $url ) {
				$compress = true;
				$js = $wp_filesystem->get_contents( $path );
				if ( $code ) {
					$js .= "\n";
				}
				$js = "\r\n/*Module: {$id}: $url */\r\n" . $js;
				$code .= $js;
				unset( $files[ $id ] );
				$files_compress[] = $id;
			}
		}

		$time = date( 'Ymdhis' );
		$this->put_contents( $file, $code, 0644, $time );

		if ( $compress ) {
			$files[ $key ] = array(
				'url' => $this->save_url . '/' . $file_name,
				'ver' => $time,
			);
		}

		set_transient(
			$file_name,
			array(
				'files' => $files_compress,
				'ver' => $time,
			),
			24 * HOUR_IN_SECONDS
		);
		return $files;
	}

}

MetafansCoreCustomizer_Assets::get_instance();
