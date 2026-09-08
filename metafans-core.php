<?php
/**
 * Plugin Name: Metafans Core | By Tophive
 * Plugin URI: https://tophivetheme.com/
 * Description: Metafans Wordpress theme core functionality
 * Version: 1.5
 * Author: Tophive
 * Author URI: https://themeforest.net/user/tophive
 * License: Envato
 * Text Domain: metafans-core
 *
 */

namespace METAFANSCORE;

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
		define( 'WP_MF_CORE_VERSION' , 	'1.0');
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

		remove_filter( 'bbp_get_reply_content', 'wp_make_content_images_responsive', 60 );
		remove_filter( 'bbp_get_topic_content', 'wp_make_content_images_responsive', 60 );
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
		if( did_action('elementor/loaded') ){
			add_action( 'elementor/widgets/widgets_registered', array(self::getInstance(), 'MetafansElementorWidgetInit') );
			add_action( 'elementor/elements/categories_registered', array(self::getInstance(), 'MetafansElementorCat') );
		}

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
	
	public static function MetafansElementorWidgetInit(){
		// $this->includesElem();
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new MetafansElementorTeam() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new MetafansElementorTeamCarousel() ); //HAS AN FIX
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new MetafansElementorBlog() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new MetafansElementorBlogCarousel() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new MetafansElementorCoursesGrid() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new MetafansElementorImageCarousel() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new MetafansElementorCoursesCarousel() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new MetafansElementorTestimonialCarousel() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new MetafansElementorCourseCategory() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new MetafansElementorInstructorFormPopup() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new MetafansElementorAdvanceSearch() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new MetafansElementorAdvanceFilter() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new MetafansElementorAdvancedTabs() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new MetafansElementorForumTabs() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new MetafansElementorBuddyPressGroups() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new MetafansElementorBBPressNewPost() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new MetafansElementorLoginSignup() );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new MetafansElementorMemberCount() );
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
		require_once('widgets/metafanswidgets/MetafansRecentPostsWidget.php');
		require_once('widgets/metafanswidgets/MetafansMailChimpWidget.php');
		require_once('widgets/metafanswidgets/MetafansBPGroupsInfo.php');
		require_once('widgets/metafanswidgets/MetafansBPProfileInfo.php');
		require_once('widgets/metafanswidgets/MetafansBPGroupMembers.php');
		require_once('widgets/metafanswidgets/MetafansBPProfileMedia.php');

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

require_once('MailChimp.php');
require_once('t/class-tophive-modules.php');
require_once('updater/theme-updater.php');

function autoload($class = '') {
    if (!strstr($class, 'METAFANSCORE')){
        return;
    }
    $result = str_replace('METAFANSCORE\\', '', $class);
    $result = str_replace('\\', '/', $result);
    require $result . '.php';
}
remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );
