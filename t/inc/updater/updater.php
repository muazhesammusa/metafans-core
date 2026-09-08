<?php

class MetafansCoreCustomizer_Updater
{
    public $api_url = '';
    public $option_key = 'tophive_pro_license_data';
    public $args;
    public $file;
    public $name;
    public $renewal_url = 'checkout/?edd_license_key=%1$s&download_id=%2$s';
    public $enter_key_url;
    public $updater ;

    function __construct($item_id, $api_url = '', $plugin_file = null)
    {

        $this->renewal_url =  $this->api_url.'/'. $this->renewal_url;
        $this->api_url = $api_url;

        $this->file = $plugin_file;
        require_once dirname(__FILE__) . '/EDD_SL_Plugin_Updater.php';
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . '/wp-admin/includes/plugin.php';
        }

        $data = get_plugin_data($this->file);
        /**
         * @see https://codex.wordpress.org/Function_Reference/get_plugin_data
         */
        $this->args = $data;
        $this->args['item_id'] = $item_id;
        $this->args['plugin_basename'] = plugin_basename( $plugin_file );
        $this->args['plugin_slug'] = basename( $plugin_file, '.php' );

        $this->enter_key_url = self_admin_url('themes.php?page=tophive');

        add_action('wp_ajax_tophive-pro/updater/ajax', array($this, 'ajax'));
        do_action('MetafansCoreCustomizer_Updater_Init', $this);
        add_action('tophive/dashboard/sidebar', array($this, 'box_license'), 1);
        remove_action( 'after_plugin_row_' . $this->args['plugin_basename'], 'wp_plugin_update_row', 10 );
        add_action( 'after_plugin_row_' . $this->args['plugin_basename'], array( $this, 'show_update_notification' ), 10, 2 );
        add_action( 'after_plugin_row_' . $this->args['plugin_basename'], array( $this, 'remove_wp_notice' ), 1, 2 );

        // add_action('wp_loaded', array($this, 'int_auto_update'), 10);
        $this->int_auto_update();

        if ( isset( $_REQUEST['force-check'] ) && current_user_can( 'update_plugins' ) ) {
            delete_transient( $this->updater->cache_key );
            delete_option( $this->updater->cache_key );
        }

    }

    /**
     * show update nofication row -- needed for multisite subsites, because WP won't tell you otherwise!
     *
     * @see wp_plugin_update_row
     * @param string  $file
     * @param array   $plugin
     */
    public function show_update_notification( $file, $plugin ) {

        if ( is_network_admin() ) {
            return;
        }

        if( ! current_user_can( 'update_plugins' ) ) {
            return;
        }

        if( is_multisite() ) {
            return;
        }

        if ( $this->args['plugin_basename'] != $file ) {
            return;
        }

        if (  empty( $this->updater ) ) {
            return ;
        }

        // Remove our filter on the site transient
        remove_filter( 'pre_set_site_transient_update_plugins', array(  $this->updater, 'check_update' ), 10 );

        $update_cache = get_site_transient( 'update_plugins' );

        $update_cache = is_object( $update_cache ) ? $update_cache : new stdClass();

        if ( empty( $update_cache->response ) || empty( $update_cache->response[ $this->args['plugin_basename'] ] ) ) {

            $version_info = $this->updater->get_cached_version_info();
            if ( false === $version_info ) {
                $message = $this->get_message();
                $_data = array( 'slug' => $this->updater->slug, 'beta' => $this->updater->beta );
                if ( $message['type'] != 'success' ) {
                    $_data['license'] = '';
                }
                $version_info = $this->updater->api_request( 'plugin_latest_version', $_data );
                $this->updater->set_version_info_cache( $version_info );
            }

            if ( ! is_object( $version_info ) ) {
                return;
            }

            if ( version_compare( $this->args['Version'], $version_info->new_version, '<' ) ) {
                $update_cache->response[ $this->args['plugin_basename'] ] = $version_info;
            }

            $update_cache->last_checked = current_time( 'timestamp' );
            $update_cache->checked[ $this->args['plugin_basename'] ] = $this->args['Version'];

            set_site_transient( 'update_plugins', $update_cache );

        } else {

            $version_info = $update_cache->response[ $this->args['plugin_basename'] ];

        }

        // Restore our filter
        add_filter( 'pre_set_site_transient_update_plugins', array( $this->updater, 'check_update' ) );

        if ( ! empty( $update_cache->response[ $this->args['plugin_basename'] ] ) && version_compare( $this->args['Version'], $version_info->new_version, '<' ) ) {

            if ( is_network_admin() || ! is_multisite() ) {
                if (is_network_admin()) {
                    $active_class = is_plugin_active_for_network($file) ? ' active' : '';
                } else {
                    $active_class = is_plugin_active($file) ? ' active' : '';
                }

                $message = $this->get_message();
                $data = $this->get_save_data();
                $license_data = $data['data'];
                $licence = isset( $data['license'] ) ? $data['license'] : '';

                // build a plugin list row, with update notification
                echo '<tr class="plugin-update-tr '.$active_class.'" id="' . $this->args['plugin_slug'] . '-update" data-slug="' . $this->args['plugin_slug'] . '" data-plugin="' . $file . '">';
                echo '<td colspan="3" class="plugin-update colspanchange">';
                echo '<div class="update-message notice inline notice-warning notice-alt">';
                echo '<p>';
                $changelog_link = self_admin_url('index.php?edd_sl_action=view_plugin_changelog&plugin=' . $this->args['plugin_basename'] . '&slug=' . $this->args['plugin_slug'] . '&TB_iframe=true&width=772&height=911');

                if ($message['type']=='success') {
                    if (empty($version_info->download_link)) {
                        printf(
                            __('There is a new version of %1$s available. %2$sView version %3$s details%4$s.', 'tophive-pro'),
                            esc_html($version_info->name),
                            '<a target="_blank" class="thickbox" href="' . esc_url($changelog_link) . '">',
                            esc_html($version_info->new_version),
                            '</a>'
                        );
                    } else {
                        printf(
                            __('There is a new version of %1$s available. %2$sView version %3$s details%4$s or %5$supdate now%6$s.', 'tophive-pro'),
                            esc_html($version_info->name),
                            '<a target="_blank" class="thickbox" href="' . esc_url($changelog_link) . '">',
                            esc_html($version_info->new_version),
                            '</a>',
                            '<a class="update-link" href="' . esc_url(wp_nonce_url(self_admin_url('update.php?action=upgrade-plugin&plugin=') . $this->args['plugin_basename'], 'upgrade-plugin_' . $this->args['plugin_basename'] )) . '">',
                            '</a>'
                        );
                    }
                } else {
                    $enter_key_url = $this->enter_key_url;
                    $msg_type = 'none';
                    if ( ! $license_data ) {
                        $msg_type = 'notice';
                    } else {
                        if (false === $license_data['success']) {
                            if ( $license_data['error'] == 'expired' ) {
                                $msg_type = 'expired';
                            }
                        }
                    }

                    if(  $msg_type == 'expired' ){
                        $renewal_url = sprintf( $licence, $this->args['item_id'] );
                        printf(__('There is a new version of %1$s available. %2$s.', 'tophive-pro'),
                            $version_info->name,
                            '<a target="_blank" class="thickbox" href="' . esc_url($changelog_link) . '">' . sprintf(__('View version %1$s details', 'tophive-pro'), $version_info->new_version) . '</a>'
                        );

                        echo '<br/>';
                        printf(__('<strong>Your License Has Expired</strong> — Updates are only available to those with an active license. %2$s or %3$s.', 'tophive-pro'),
                            $version_info->name,
                            '<strong><a target="_blank" href="' . esc_url($renewal_url) . '">' . __('Click here to Renewal', 'tophive-pro') . '</a></strong>',
                            '<a target="_blank"  href="' . esc_url($enter_key_url) . '">' . __('Check my license again ', 'tophive-pro') . '</a>'
                        );
                    } else {
                        printf(__('There is a new version of %1$s available. %2$s. Automatic update is unavailable for this plugin. %3$s', 'tophive-pro'),
                            $version_info->name,
                            '<a target="_blank" class="thickbox" href="' . esc_url($changelog_link) . '">' . sprintf(__('View version %1$s details', 'tophive-pro'), $version_info->new_version) . '</a>',
                            '<strong><a target="_blank" href="' . esc_url($enter_key_url) . '">' . __('Enter valid license key for automatic updates', 'tophive-pro') . '</a></strong>'
                        );
                    }

                }

                do_action("in_plugin_update_message-{$file}", $plugin, $version_info);
                echo '</p>';
                echo '</div></td></tr>';

            }

        } // end if version compare
    }

    function remove_wp_notice(){
        remove_action( 'after_plugin_row_' . $this->args['plugin_basename'], 'wp_plugin_update_row', 10 );
    }

    function ajax()
    {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Permission denied.', 'tophive-pro' ) ), 403 );
        }
        if ( isset( $_REQUEST['tophive_action'] ) ) {
            check_ajax_referer($this->option_key, '_nonce');
            $key = sanitize_text_field($_REQUEST['license']);
            if ( $_REQUEST['tophive_action']  == 'deactivate' ) {
                $r = $this->deactivate_license( $key );
                $type = 'successs';
                if ( $r ) {
                    $message = '<p>'.__('License deactivated.', 'tophive-pro').'<p>';
                } else {
                    $type = 'error';
                    $message = '<p class="error">'.__( 'An error occurred, please try again.', 'tophive-pro' ).'<p>';
                }
                wp_send_json( array(
                    'type' => $type,
                    'message' => '',
                    'html' => $message
                ) );
            } else {
                $this->active($key);
            }
            wp_send_json( $this->get_message() );
        }

        die();
    }

    function int_auto_update()
    {
        $data = $this->get_save_data();
        $license_key = '';
        if ( $data['license'] ) {
            $license_key =  $data['license'];
        }
        $this->updater = new Metafans_EDD_SL_Plugin_Updater($this->api_url, $this->file,
            array(
                'version' => $this->args['Version'],               // current version number
                'license' => $license_key,        // license key (used get_option above to retrieve from DB)
                'item_id' => $this->args['item_id'],      // ID of the product
                'author'  => $this->args['Author'],      // Author of this plugin
                'beta'    => false,
            )
        );
    }

    function get_remote($action = '', $api_params = array())
    {
        $api_params['edd_action'] = $action;
        // Call the custom API.
        $response = wp_remote_post($this->api_url, array('timeout' => 15, 'sslverify' => true, 'body' => $api_params));
        if (is_wp_error($response) && 200 !== wp_remote_retrieve_response_code($response)) {
            return false;
        }

        $license_data = json_decode(wp_remote_retrieve_body($response), true);
        if ( empty( $license_data ) || ! is_array( $license_data ) ) {
            return false;
        }
        return $license_data;
    }

    function check_license($license = '')
    {
        $api_params = array(
            'edd_action' => 'check_license',
            'license'    => $license,
            'item_name'  => urlencode($this->args['Name']),
            'url'        => home_url()
        );
        $license_data = $this->get_remote('check_license', $api_params);
        return $license_data;
    }

    function get_message( ){
        $data = $this->get_save_data();
        $license_data = $data['data'];
        if ( ! $data['license'] ) {
            return array(
                'type' => '',
                'message' => '',
                'html' => ''
            );
        }

        $type = 'error';
        $message =  __('Invalid license.', 'tophive-pro');
        if ( ! $license_data['success'] ) {
            switch ($license_data['error']) {
                case 'expired' :
                    if ( $license_data['license'] == 'invalid' ) {
                        $message = __('Invalid license.', 'tophive-pro' );
                    } else {
                        $message = __('Your license key has expired.', 'tophive-pro');
                    }
                    break;
                case 'disabled' :
                case 'revoked' :
                    $message = __('Your license key has been disabled.', 'tophive-pro' );
                    break;
                case 'license_not_activable' :
                    $message = __('License not activable', 'tophive-pro' );
                    break;
                case 'missing' :
                    $message = __('Invalid license.', 'tophive-pro' );
                    break;
                case 'invalid' :
                case 'site_inactive' :
                    $message = __('Your license is not active for this URL.', 'tophive-pro' );
                    break;

                case 'item_name_mismatch' :
                    $message = sprintf(__('This appears to be an invalid license key for %s.', 'tophive-pro' ), $this->args['Name']);
                    break;
                case 'no_activations_left':
                    $message = __('Your license key has reached its activation limit.', 'tophive-pro' );
                    break;
                default :
                    $message = __('An error occurred, please try again.', 'tophive-pro' );
                    break;
            }
        } else {
            $type = 'success';

            if ( $license_data['expires'] == 'lifetime' ) {
                $message =  __('Your license will never expire.', 'tophive-pro' );
            } else {
                $message = sprintf(
                    __('Your license key will expire on %s.', 'tophive-pro' ),
                    date_i18n(get_option('date_format'), strtotime($license_data['expires'], current_time('timestamp')))
                );
            }

            if ( current_time('timestamp') > strtotime( $license_data['expires'], current_time('timestamp') ) ) {
                $type = 'error';
            }
        }

        return array(
            'type' => $type,
            'message' => $message,
            'html' => $message ? '<p class="'.esc_attr( $type ).'">'.$message.'</p>' : ''
        );
    }

    function active($license)
    {

        // data to send in our API request
        $api_params = array(
            'edd_action' => 'activate_license',
            'license'    => $license,
            'item_name'  => urlencode($this->args['Name']), // the name of our product in EDD
            'url'        => home_url()
        );

        $license_data = $this->get_remote('activate_license', $api_params);
        $data = array(
            'license' => $license,
            'data' => $license_data
        );

        update_option( $this->option_key, $data );
        return $license_data;

    }

    function get_save_data(){
        $data = get_option($this->option_key);
        if ( ! is_array( $data ) ) {
            $data = array();
        }
        $data = wp_parse_args( $data, array(
            'license' => '',
            'data' => array()
        ) );
        return $data;
    }

    function deactivate_license( $license = null )
    {
        // retrieve the license from the database
        if ( ! $license ) {
            $license_data = $this->get_save_data();
            $license = $license_data['license'];
        }

        $api_params = array(
            'edd_action' => 'deactivate_license',
            'license'    => $license,
            'item_name'  => urlencode($this->args['Name']), // the name of our product in EDD
            'url'        => home_url()
        );

        $license_data = $this->get_remote('deactivate_license', $api_params);
        if ( $license_data && isset( $license_data['license'] ) && $license_data['license'] == 'deactivated' ) {
            delete_option( $this->option_key );
            return true;
        } else {
            return false;
        }

    }

    function box_license()
    {
        $data = $this->get_save_data();
        $message = false;
        if ( $data['license'] ) {
            $message = $this->get_message( $data['data'] );
        }
        ?>
        <div class="cd-box">
            <div class="cd-box-top"><?php _e('Metafans Pro License', 'tophive-pro'); ?></div>
            <div class="cd-box-content cd-modules">
                <div class="cd-box-content cd-license-form">
                    <p><?php _e('To receive updates, please enter your valid Metafans Pro license key.', 'tophive-pro'); ?></p>
                    <p>
                        <input name="tophive-license" value="<?php echo esc_attr( $data['license'] ); ?>" type="hidden" class="tophive-license-input-hidden regular-text">
                        <input name="tophive-license-placeholder" placeholder="<?php esc_attr_e( 'Enter your license key', 'tophive-pro' ); ?>" type="text" class="tophive-license-input regular-text">
                    </p>
                    <div class="cutomify-license-info tophive-msg">
                        <?php
                        if ( $message ) {
                            echo $message['html'];
                        }
                        ?>
                    </div>
                    <p class="">
                        <a href="#" class="button button-primary tophive-license-save"><?php _e('Activate', 'tophive-pro'); ?></a>
                        <a href="#" class="<?php echo $message['type'] == 'success' ? '' : 'cd-hide'; ?> button button-secondary tophive-license-deactivate"><?php _e('Deactivate', 'tophive-pro'); ?></a>
                    </p>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                var tophive_updater_nonce = <?php echo json_encode(wp_create_nonce( $this->option_key )); ?>;
                var placeholder = <?php echo json_encode( __( 'Enter your license key', 'tophive-pro' ) );  ?>;

                var secretKey = function( key ){
                    if ( ! key ) {
                        return placeholder;
                    }
                    var string = key;
                    var l = string.length;
                    var show = 5;
                    var sub = string.substr(-show);
                    var s = '';
                    for( var i = 0; i < l - show ; i++ ){
                        s+= "\267";//  dot character
                    }
                    s+=sub;
                    return s;
                };

                var hidden_key =  $('.cd-license-form .tophive-license-input-hidden' ).val();
                $('.cd-license-form  .tophive-license-input' ).attr( 'placeholder', secretKey( hidden_key ) ).val('');

                $('.tophive-license-save').on('click', function (e) {
                    var button = $(this);
                    var form = $(this).closest('.cd-license-form');
                    e.preventDefault();
                    var hidden_key =  $('.tophive-license-input-hidden', form).val();
                    var key = $('.tophive-license-input', form).val() || '';
                    if ( ! key ) {
                        key = hidden_key;
                    }
                    $('.tophive-license-input-hidden', form).val( key );
                    $('.tophive-license-input', form).attr( 'value', '' );
                    $('.tophive-license-input', form).attr( 'placeholder', secretKey( key ) );

                    button.addClass( 'updating-message' );
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'tophive-pro/updater/ajax',
                            license: key,
                            tophive_action: 'save',
                            _nonce: tophive_updater_nonce
                        },
                        success: function (res) {
                            button.removeClass( 'updating-message' );
                            if ( res.type === 'success' ) {
                                $( '.tophive-license-deactivate', form ).removeClass( 'cd-hide' );
                            } else {
                                $('.tophive-license-input', form).attr( 'placeholder', secretKey( '' ) );
                                $( '.tophive-license-deactivate', form ).addClass( 'cd-hide' );
                            }
                            $( '.cutomify-license-info', form ).html( res.html );
                        }
                    });
                });

                $('.tophive-license-deactivate').on('click', function (e) {
                    var form = $(this).closest('.cd-license-form');
                    var button = $( this );
                    e.preventDefault();
                    var key = $('.tophive-license-input-hidden', form ).val() || '';
                    $('.tophive-license-input', form ).attr( 'placeholder', secretKey('') ).val('');
                    button.addClass( 'updating-message' );
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'tophive-pro/updater/ajax',
                            license: key,
                            tophive_action: 'deactivate',
                            _nonce: tophive_updater_nonce
                        },
                        success: function (res) {
                            button.removeClass( 'updating-message' );
                            if ( res.type === 'success' ) {
                                $( '.tophive-license-deactivate', form ).removeClass( 'cd-hide' );
                            } else {
                                $( '.tophive-license-deactivate', form ).addClass( 'cd-hide' );
                            }
                            $( '.cutomify-license-info', form ).html( res.html );
                        }
                    });
                });

            });
        </script>
        <?php
    }
}

if ( is_admin() ) {
    new MetafansCoreCustomizer_Updater(MetafansCoreCustomizer::$item_id, MetafansCoreCustomizer::$api_url, MetafansCoreCustomizer::$file );
}
