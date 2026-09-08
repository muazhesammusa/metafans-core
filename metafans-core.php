<?php
/**
 * Plugin Name: Metafans Core | By Tophive
 * Plugin URI: https://tophivetheme.com/
 * Description: Metafans Wordpress theme core functionality
 * Version: 1.5
 * Requires at least: 6.8
 * Requires PHP: 8.1
 * Author: Tophive
 * Author URI: https://themeforest.net/user/tophive
 * License: Envato
 * Text Domain: metafans-core
 *
 */

namespace METAFANSCORE;

defined( 'ABSPATH' ) || exit;

use METAFANSCORE\widgets\elementor\MetafansElementorBase;
use METAFANSCORE\widgets\elementor\MetafansElementorTeam;
use METAFANSCORE\widgets\elementor\MetafansElementorTeamCarousel;
use METAFANSCORE\widgets\elementor\MetafansElementorBlog;
use METAFANSCORE\widgets\elementor\MetafansElementorBlogCarousel;
use METAFANSCORE\widgets\elementor\MetafansElementorCoursesGrid;
use METAFANSCORE\widgets\elementor\MetafansElementorImageCarousel;
use METAFANSCORE\widgets\elementor\MetafansElementorCoursesCarousel;
use METAFANSCORE\widgets\elementor\MetafansElementorTestimonialCarousel;
use METAFANSCORE\widgets\elementor\MetafansElementorInstructorFormPopup;
use METAFANSCORE\widgets\elementor\MetafansElementorAdvanceSearch;
use METAFANSCORE\widgets\elementor\MetafansElementorAdvanceFilter;
use METAFANSCORE\widgets\elementor\MetafansElementorAdvancedTabs;
use METAFANSCORE\widgets\elementor\MetafansElementorCourseCategory;
use METAFANSCORE\widgets\elementor\MetafansElementorForumTabs;
use METAFANSCORE\widgets\elementor\MetafansElementorLoginSignup;
use METAFANSCORE\widgets\elementor\MetafansElementorBuddyPressGroups;
use METAFANSCORE\widgets\elementor\MetafansElementorBBPressNewPost;
use METAFANSCORE\widgets\elementor\MetafansElementorMemberCount;
use METAFANSCORE\widgets\metafanswidgets\WidgetHelper;

class MetafansCore
{

	private static $instance = null;

	public static function constants()
	{
		define( 'WP_MF_CORE_VERSION', '1.5' );
		define( 'WP_MF_CORE_PREFIX' , 	'thcore');
		define( 'WP_MF_CORE_SLUG' , 	'metafanscore');

		// Need to add extra links on plugin activation
		define( 'WP_MF_CORE_BASENAME', plugin_basename( __FILE__ ));

		define( 'WP_MF_CORE_ROOT', __FILE__);
		define( 'WP_MF_CORE_ROOT_DIR', dirname(WP_MF_CORE_ROOT));

		define( 'WP_MF_CORE_PATH', plugin_dir_path(WP_MF_CORE_ROOT));
		define( 'WP_MF_CORE_URL', plugin_dir_url(WP_MF_CORE_ROOT));

		define( 'WP_MF_CORE_JS_URL', 	trailingslashit(WP_MF_CORE_URL . 'js'));
		define( 'WP_MF_CORE_CSS_URL', 	trailingslashit(WP_MF_CORE_URL . 'css'));
		define( 'WP_MF_CORE_FONTS_URL', 	trailingslashit(WP_MF_CORE_URL . 'fonts'));
		define( 'WP_MF_CORE_IMAGES_URL', trailingslashit(WP_MF_CORE_URL . 'images'));
	}
	public static function init(){
		self::constants();
		add_action( 'wp_enqueue_scripts', array(self::getInstance(), 'frontendassets'));
		add_filter('user_contactmethods', array(self::getInstance(), 'tophiveCutsomContacts'));
		add_action( 'show_user_profile', array( self::getInstance(), 'tophive_profile_designation') );
		add_action( 'edit_user_profile', array( self::getInstance(),'tophive_profile_designation') );
		add_action( 'personal_options_update', array( self::getInstance(), 'tophive_save_profile_designation') );
		add_action( 'edit_user_profile_update', array( self::getInstance(), 'tophive_save_profile_designation') );
		add_action( 'widgets_init', array(self::getInstance(), 'widgetRegistrar'));
		add_action( 'admin_enqueue_scripts', array(self::getInstance(), 'adminassets'));

		// Request Background Image
		add_action( 'wp_ajax_nopriv_course_grid_pull_cats', array(MetafansElementorBase::getInstance(), 'deliverCoursesAjaxRequest') );
		add_action( 'wp_ajax_course_grid_pull_cats', array(MetafansElementorBase::getInstance(), 'deliverCoursesAjaxRequest') );

		add_action( 'wp_ajax_pull_course_paged', array(MetafansElementorBase::getInstance(), 'deliverCoursesAjaxRequest') );
		add_action( 'wp_ajax_nopriv_pull_course_paged', array(MetafansElementorBase::getInstance(), 'deliverCoursesAjaxRequest') );

		add_action( 'wp_ajax_pull_posts_paged', array(MetafansElementorBase::getInstance(), 'deliverPostsAjaxRequest') );
		add_action( 'wp_ajax_nopriv_pull_posts_paged', array(MetafansElementorBase::getInstance(), 'deliverPostsAjaxRequest') );

		add_action( 'wp_ajax_th_advanced_search', array(MetafansElementorBase::getInstance(), 'tophiveAdvancedSearch') );
		add_action( 'wp_ajax_nopriv_th_advanced_search', array(MetafansElementorBase::getInstance(), 'tophiveAdvancedSearch') );

		add_action( 'wp_ajax_th_post_topic', array(MetafansElementorBase::getInstance(), 'tophivePostTopicSubmit') );
		
		add_action('wp_ajax_mailchimpsubscribe', array(WidgetHelper::getInstance(), 'TH_ajax_subscribe'));
		add_action('wp_ajax_nopriv_mailchimpsubscribe', array(WidgetHelper::getInstance(), 'TH_ajax_subscribe'));
		add_action( 'elementor/widgets/register', array( self::getInstance(), 'MetafansElementorWidgetInit' ) );
		add_action( 'elementor/elements/categories_registered', array( self::getInstance(), 'MetafansElementorCat' ) );

		remove_action( 'wp_head', 'feed_links_extra', 3 );
		remove_action( 'wp_head', 'learn_press_print_custom_styles' );
	}
	
	function tophiveCutsomContacts($contactmethods) {
	     unset($contactmethods['aim']);
	     unset($contactmethods['yim']);
	     unset($contactmethods['jabber']);
	     $contactmethods['facebook'] = 'Facebook';
	     $contactmethods['youtube'] = 'Youtube';
	     $contactmethods['twitter'] = 'Twitter';
	     $contactmethods['linkedin'] = 'LinkedIn';
	     $contactmethods['slack'] = 'Slack';
	     return $contactmethods;
	}
	function tophive_profile_designation( $user ) {
		?>
		  <h3><?php echo esc_html__('Extra profile information', 'tophive'); ?></h3>
		  <table class="form-table">
		    <tr>
		      <th><label for="designation"><?php echo esc_html__('Designation', 'tophive'); ?></label></th>
		      <td>
		        <input type="text" name="designation" id="designation" class="regular-text" 
		            value="<?php echo esc_attr( get_the_author_meta( 'designation', $user->ID ) ); ?>" /><br />
		        <span class="description"><?php echo esc_html__('Please enter your designation.', 'tophive'); ?></span>
		    </td>
		    </tr>
		  </table>
		<?php
	}
	public function tophive_save_profile_designation($user_id){
		$saved = false;
		if ( current_user_can( 'edit_user', $user_id ) ) {
		    update_user_meta( $user_id, 'designation', isset( $_POST['designation'] ) ? sanitize_text_field( wp_unslash( $_POST['designation'] ) ) : '' );
		    $saved = true;
		}
		return true;
	}
	
	/**
	 * Register Elementor widgets through the current widgets manager API.
	 *
	 * Integration-specific widgets are registered only when their owning plugin
	 * is active so an optional plugin being disabled cannot fatal Elementor.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 * @return void
	 */
	public static function MetafansElementorWidgetInit( $widgets_manager ) {
		if ( ! is_object( $widgets_manager ) || ! method_exists( $widgets_manager, 'register' ) ) {
			return;
		}

		$generic_widgets = array(
			MetafansElementorTeam::class,
			MetafansElementorTeamCarousel::class,
			MetafansElementorBlog::class,
			MetafansElementorBlogCarousel::class,
			MetafansElementorImageCarousel::class,
			MetafansElementorTestimonialCarousel::class,
			MetafansElementorAdvancedTabs::class,
			MetafansElementorLoginSignup::class,
			MetafansElementorMemberCount::class,
		);

		foreach ( $generic_widgets as $widget_class ) {
			self::register_elementor_widget( $widgets_manager, $widget_class );
		}

		if ( class_exists( 'LearnPress' ) || function_exists( 'learn_press_get_course' ) ) {
			foreach ( array(
				MetafansElementorCoursesGrid::class,
				MetafansElementorCoursesCarousel::class,
				MetafansElementorCourseCategory::class,
				MetafansElementorInstructorFormPopup::class,
				MetafansElementorAdvanceSearch::class,
				MetafansElementorAdvanceFilter::class,
			) as $widget_class ) {
				self::register_elementor_widget( $widgets_manager, $widget_class );
			}
		}

		if ( class_exists( 'BuddyPress' ) || function_exists( 'bp_is_active' ) ) {
			self::register_elementor_widget( $widgets_manager, MetafansElementorBuddyPressGroups::class );
		}

		if ( class_exists( 'bbPress' ) || function_exists( 'bbp_get_forum' ) ) {
			self::register_elementor_widget( $widgets_manager, MetafansElementorForumTabs::class );
			self::register_elementor_widget( $widgets_manager, MetafansElementorBBPressNewPost::class );
		}
	}

	/**
	 * Register one widget without assuming the optional integration class exists.
	 *
	 * @param object $widgets_manager Elementor widgets manager.
	 * @param string $widget_class    Fully qualified widget class.
	 * @return void
	 */
	private static function register_elementor_widget( $widgets_manager, $widget_class ) {
		if ( class_exists( $widget_class ) ) {
			$widgets_manager->register( new $widget_class() );
		}
	}

	public static function MetafansElementorCat( $elements_manager ) {
		$elements_manager->add_category(
			WP_MF_CORE_SLUG,
			[
				'title' => esc_html__( 'Metafans Widgets', WP_MF_CORE_SLUG ),
				'icon' => 'eicon-t-letter',
			]
		);
	}
	public static function inlineStyles(){
	}

	public static function frontendassets(){
		
        wp_register_style( 'th-style', false  );
		wp_enqueue_script('th-elementor-lazy-js',WP_MF_CORE_URL . 'widgets/elementor/assets/jquery.lazy.min.js',array('jquery')
		);
		wp_enqueue_script( 'th-widget-js', WP_MF_CORE_URL . 'widgets/metafanswidgets/assets/js/frontend.js' );
		wp_enqueue_style( 'th-wp-widget-styles', WP_MF_CORE_URL . 'widgets/wordpress/assets/styles.css' );
		wp_enqueue_style( 'th-elementor-css', WP_MF_CORE_URL . 'widgets/elementor/assets/style.css' );
		wp_enqueue_style( 'th-widget-css', WP_MF_CORE_URL . 'widgets/metafanswidgets/assets/css/frontend.css' );
		add_action( 'wp_ajax_course_grid_pull_cats', array( MetafansElementorBase::getInstance(), 'AjaxCourseRequest' ) );
		add_action( 'wp_ajax_nopriv_course_grid_pull_cats', array( MetafansElementorBase::getInstance(), 'AjaxCourseRequest' ) );
		wp_enqueue_script( 'rich-text-quill', WP_MF_CORE_URL . 'widgets/elementor/assets/quill.min.js', array(), '4.0.6' );

		wp_enqueue_style( 'rich-text-quill-css', WP_MF_CORE_URL . 'widgets/elementor/assets/quill.snow.css' );
		wp_enqueue_script( 'th-elementor-js', WP_MF_CORE_URL . 'widgets/elementor/assets/script.js', array( 'jquery' ) );
		wp_localize_script(
			'th-elementor-js',
			'th_elem_ajax_obj',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'metafans_core_topic' ),
			)
		);
	}
	public static function widgetRegistrar(){
		require_once WP_MF_CORE_PATH . 'widgets/metafanswidgets/MetafansRecentPostsWidget.php';
		require_once WP_MF_CORE_PATH . 'widgets/metafanswidgets/MetafansMailChimpWidget.php';
		require_once WP_MF_CORE_PATH . 'widgets/metafanswidgets/MetafansBPGroupsInfo.php';
		require_once WP_MF_CORE_PATH . 'widgets/metafanswidgets/MetafansBPProfileInfo.php';
		require_once WP_MF_CORE_PATH . 'widgets/metafanswidgets/MetafansBPGroupMembers.php';
		require_once WP_MF_CORE_PATH . 'widgets/metafanswidgets/MetafansBPProfileMedia.php';

		register_widget( 'MetafansRecentPostsWidget' );
		register_widget( 'MetafansMailChimpWidget' );
		register_widget( 'MetafansBPGroupsInfo' );
		register_widget( 'MetafansBPGroupMembers' );
		register_widget( 'MetafansBPProfileInfo' );
		register_widget( 'MetafansBPProfileMedia' );
	}
	public static function adminassets(){
		wp_enqueue_media();
	    wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'tophive-select2', WP_MF_CORE_URL . 'widgets/elementor/assets/select2.min.js', array(), '4.0.6' );
		
		wp_enqueue_script( 'enhanced-colorpicker', WP_MF_CORE_URL . 'widgets/metafanswidgets/assets/js/colorpicker.js', array( 'wp-color-picker' ), '1.0', true );
		wp_enqueue_script( 'tophive-widgets-scripts', WP_MF_CORE_URL . 'widgets/metafanswidgets/assets/js/admin.js', array(), '4.0.6' );
		wp_enqueue_script( 'tophive-elementor', WP_MF_CORE_URL . 'widgets/elementor/assets/script.js', array('jquery'), '1.0.0' );

		wp_enqueue_style( 'wp-color-picker' );        
		wp_enqueue_style( 'enhanced-colorpicker', WP_MF_CORE_URL . 'widgets/metafanswidgets/assets/css/colorpicker.css' );
		wp_enqueue_style( 'tophive-select2', WP_MF_CORE_URL . 'widgets/elementor/assets/select2.min.css' );
		wp_enqueue_style( 'tophive-widgets-style', WP_MF_CORE_URL . 'widgets/metafanswidgets/assets/css/admin.css' );
		
	}
	
	/**
	 * Load plugin textdomain.
	 *
	 * @since 1.0.0
	 */
	public static function MetafansLoadTextdomain() { 
	  load_plugin_textdomain( 'metafans-core', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
	}

	public static function getInstance(){
		if (empty(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

spl_autoload_register(__NAMESPACE__ . '\\autoload');


add_action( 'plugins_loaded', array( MetafansCore::getInstance(), 'init' ) );

require_once __DIR__ . '/MailChimp.php';
require_once __DIR__ . '/t/class-tophive-modules.php';
require_once __DIR__ . '/updater/theme-updater.php';

function autoload( $class = '' ) {
	if ( 0 !== strpos( $class, __NAMESPACE__ . '\\' ) ) {
		return;
	}

	$relative = str_replace( __NAMESPACE__ . '\\', '', $class );
	$relative = str_replace( '\\', '/', $relative );
	$file     = __DIR__ . '/' . $relative . '.php';

	if ( is_readable( $file ) ) {
		require_once $file;
	}
}
remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );
