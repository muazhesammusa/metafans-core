<?php

namespace METAFANSCORE\Elementor;

defined( 'ABSPATH' ) || exit;

/**
 * Registers MetaFans Elementor widgets behind one maintained integration boundary.
 *
 * Widget classes stay lazy: they are autoloaded only from Elementor's own
 * widgets/register lifecycle, after Widget_Base is available.
 */
final class WidgetRegistry {

	private static $booted = false;

	public static function boot() {
		if ( self::$booted ) {
			return;
		}

		self::$booted = true;
		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widgets' ) );
		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'register_category' ) );
	}

	public static function register_widgets( $widgets_manager ) {
		if ( ! class_exists( '\\Elementor\\Widget_Base' ) || ! is_object( $widgets_manager ) || ! method_exists( $widgets_manager, 'register' ) ) {
			return;
		}

		foreach ( self::generic_widgets() as $widget_class ) {
			self::register_widget( $widgets_manager, $widget_class );
		}

		if ( self::learnpress_available() ) {
			foreach ( self::learnpress_widgets() as $widget_class ) {
				self::register_widget( $widgets_manager, $widget_class );
			}
		}

		if ( self::buddypress_available() ) {
			self::register_widget( $widgets_manager, self::widget_class( 'MetafansElementorBuddyPressGroups' ) );
		}

		if ( self::bbpress_available() ) {
			self::register_widget( $widgets_manager, self::widget_class( 'MetafansElementorForumTabs' ) );
			self::register_widget( $widgets_manager, self::widget_class( 'MetafansElementorBBPressNewPost' ) );
		}
	}

	public static function register_category( $elements_manager ) {
		if ( ! is_object( $elements_manager ) || ! method_exists( $elements_manager, 'add_category' ) ) {
			return;
		}

		$elements_manager->add_category(
			WP_MF_CORE_SLUG,
			array(
				'title' => esc_html__( 'Metafans Widgets', 'metafans-core' ),
				'icon'  => 'eicon-t-letter',
			)
		);
	}

	private static function generic_widgets() {
		return array_map(
			array( __CLASS__, 'widget_class' ),
			array(
				'MetafansElementorTeam',
				'MetafansElementorTeamCarousel',
				'MetafansElementorBlog',
				'MetafansElementorBlogCarousel',
				'MetafansElementorImageCarousel',
				'MetafansElementorTestimonialCarousel',
				'MetafansElementorAdvancedTabs',
				'MetafansElementorLoginSignup',
				'MetafansElementorMemberCount',
			)
		);
	}

	private static function learnpress_widgets() {
		return array_map(
			array( __CLASS__, 'widget_class' ),
			array(
				'MetafansElementorCoursesGrid',
				'MetafansElementorCoursesCarousel',
				'MetafansElementorCourseCategory',
				'MetafansElementorInstructorFormPopup',
				'MetafansElementorAdvanceSearch',
				'MetafansElementorAdvanceFilter',
			)
		);
	}

	private static function widget_class( $short_name ) {
		return 'METAFANSCORE\\widgets\\elementor\\' . $short_name;
	}

	private static function register_widget( $widgets_manager, $widget_class ) {
		if ( class_exists( $widget_class ) ) {
			$widgets_manager->register( new $widget_class() );
		}
	}

	private static function learnpress_available() {
		return class_exists( 'LearnPress' ) || function_exists( 'learn_press_get_course' );
	}

	private static function buddypress_available() {
		return class_exists( 'BuddyPress' ) || function_exists( 'bp_is_active' );
	}

	private static function bbpress_available() {
		return class_exists( 'bbPress' ) || function_exists( 'bbp_get_forum' );
	}
}
