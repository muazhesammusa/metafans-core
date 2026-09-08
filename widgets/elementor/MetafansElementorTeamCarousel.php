<?php 
	

	namespace METAFANSCORE\widgets\elementor;
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	/**
	 **
	 * Metafans elementor team class
	 * Since Version 1.0.0
	 * @package wordpress
	 * @subpackage MasterClass
	 *
	 *
	 */
	class MetafansElementorTeamCarousel extends \Elementor\Widget_base
	{
		public function get_title(){
			return esc_html__( 'Team Carousel', WP_MF_CORE_SLUG );
		}
		public function get_name(){
			return 'th-team-carousel-block';
		}
		public function get_icon(){
			return 'eicon-person';
		}
		public function get_categories(){
	        return [ WP_MF_CORE_SLUG ];
	    }
		public function get_keywords() {
			return [ 'team', 'teachers', 'carousel' ];
		}
		protected function register_controls(){

			/*
			** Team Content Section
			*
			* @register_controls
			*/

		$this->start_controls_section(
				'th_team_carousel_section',
				[
					'label' => esc_html__( 'Team Carousel', WP_MF_CORE_SLUG ),
					'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
				]
			);

			$repeater = new \Elementor\Repeater();
			$repeater->add_control(
				'th_team_carousel_image',
				[
					'label' => esc_html__( 'Choose Image', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'default' => [
						'url' => \Elementor\Utils::get_placeholder_image_src(),
					],
				]
			);
			$this->add_control(
				'th_team_carousel_posts_layout',
				[
					'label' => esc_html__( 'Layout', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'options' => [
						'' 	=> esc_html__( 'Full Width', WP_MF_CORE_SLUG ),
						' ec-overflow-hidden' 	=> esc_html__( 'Fixed Width', WP_MF_CORE_SLUG ),
					],
					'default' => '',
				]
			);
			$repeater->add_control(
				'th_team_carousel_title',
				[
					'label' => esc_html__( 'Name', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => esc_html__( 'John Doe', WP_MF_CORE_SLUG ),
					'placeholder' => esc_html__( 'Type your name here', WP_MF_CORE_SLUG ),
				]
			);
			$repeater->add_control(
				'th_team_carousel_designation',
				[
					'label' => esc_html__( 'Designation', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => esc_html__( 'Designation', WP_MF_CORE_SLUG ),
					'placeholder' => esc_html__( 'Designation', WP_MF_CORE_SLUG ),
				]
			);
			$repeater->add_control(
				'th_team_carousel_description',
				[
					'label' => esc_html__( 'Desription', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::TEXTAREA,
					'default' => esc_html__( 'John Doe Creates Beautiful websites', WP_MF_CORE_SLUG ),
					'placeholder' => esc_html__( 'Description', WP_MF_CORE_SLUG ),
				]
			);
			$this->add_control(
				'show_social_icons',
				[
					'label' => esc_html__( 'Show Socials', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'Show', WP_MF_CORE_SLUG ),
					'label_off' => __( 'Hide', WP_MF_CORE_SLUG ),
					'return_value' => 'yes',
					'default' => 'yes',
				]
			);
			$this->add_control(
				'th_team_carousel_select_columns',
				[
					'label' => esc_html__( 'Select Columns', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => '4',
					'options' => [
						'1' 	=> esc_html__( '1 Column', WP_MF_CORE_SLUG ),
						'2' 	=> esc_html__( '2 Columns', WP_MF_CORE_SLUG ),
						'3'  	=> esc_html__( '3 Columns', WP_MF_CORE_SLUG ),
						'4' 	=> esc_html__( '4 Columns', WP_MF_CORE_SLUG ),
						'5' 	=> esc_html__( '5 Columns', WP_MF_CORE_SLUG ),
						'6' 	=> esc_html__( '6 Columns', WP_MF_CORE_SLUG ),
						'7' 	=> esc_html__( '7 Columns', WP_MF_CORE_SLUG ),
						'8' 	=> esc_html__( '8 Columns', WP_MF_CORE_SLUG ),
					],
				]
			);
			// Social Profiles


			// $repeater->add_control(
			// 'th_team_carousel_social_icon',
			// 	[
			// 		'label' => __( 'Social Icons', WP_MF_CORE_SLUG ),
			// 		'type' => \Elementor\Controls_Manager::ICON,
			// 		'include' => [
			// 			'fa fa-facebook',
			// 			'fa fa-flickr',
			// 			'fa fa-google-plus',
			// 			'fa fa-instagram',
			// 			'fa fa-linkedin',
			// 			'fa fa-pinterest',
			// 			'fa fa-reddit',
			// 			'fa fa-twitch',
			// 			'fa fa-twitter',
			// 			'fa fa-vimeo',
			// 			'fa fa-youtube',
			// 		],
			// 		'default' => 'fa fa-facebook',
			// 	]
			// );
			$repeater->add_control(
				'th_team_carousel_social_link_facebook', [
						'label' => esc_html__( 'Facebook Link', WP_MF_CORE_SLUG ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'label_block' => true,
					]
				);
			$repeater->add_control(
				'th_team_carousel_social_link_twitter', [
						'label' => esc_html__( 'Twitter Link', WP_MF_CORE_SLUG ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'label_block' => true,
					]
				);
			$repeater->add_control(
				'th_team_carousel_social_link_instagram', [
						'label' => esc_html__( 'Instagram Link', WP_MF_CORE_SLUG ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'label_block' => true,
					]
				);
			$repeater->add_control(
				'th_team_carousel_social_link_linkedin', [
						'label' => esc_html__( 'LinkedIn Link', WP_MF_CORE_SLUG ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'label_block' => true,
					]
				);
			$repeater->add_control(
				'th_team_carousel_social_link_youtube', [
						'label' => esc_html__( 'Youtube Link', WP_MF_CORE_SLUG ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'label_block' => true,
					]
				);
			$repeater->add_control(
				'th_team_carousel_social_link_gplus', [
						'label' => esc_html__( 'Google Plus Link', WP_MF_CORE_SLUG ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'label_block' => true,
					]
				);
			$repeater->add_control(
				'th_team_carousel_social_link_vimeo', [
						'label' => esc_html__( 'Vimeo Link', WP_MF_CORE_SLUG ),
						'type' => \Elementor\Controls_Manager::TEXT,
						'label_block' => true,
					]
				);
			
			$this->add_control(
				'th_team_carousel_items',
				[
					'label' => esc_html__( 'Add Social Profiles', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $repeater->get_controls(),
					'default' => [
						[
							'th_team_carousel_social_icon' => 'fa fa-facebook',
							'th_team_carousel_social_link_facebook' => '#',
							'th_team_carousel_social_link_twitter' => '#',
						]
					],
				]
			);

			

			$this->add_control(
				'th_team_carousel_text_align',
				[
					'label' => esc_html__( 'Alignment', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::CHOOSE,
					'options' => [
						'th-text-left' => [
							'title' => esc_html__( 'Left', WP_MF_CORE_SLUG ),
							'icon' => 'fa fa-align-left',
						],
						'th-text-center' => [
							'title' => esc_html__( 'Center', WP_MF_CORE_SLUG ),
							'icon' => 'fa fa-align-center',
						],
						'th-text-right' => [
							'title' => esc_html__( 'Right', WP_MF_CORE_SLUG ),
							'icon' => 'fa fa-align-right',
						],
					],
					'default' => 'th-text-center',
					'toggle' => true,
				]
			);
		$this->end_controls_section();
		$this->start_controls_section(
			'th_team_carousel_carousel_options',
				[
					'label' => esc_html__( 'Carousel Options', WP_MF_CORE_SLUG ),
					'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
				]
			);
			$this->add_control(
				'th_team_carousel_autoplay',
				[
					'label' => esc_html__( 'Autoplay', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'Yes', WP_MF_CORE_SLUG ),
					'label_off' => __( 'No', WP_MF_CORE_SLUG ),
					'return_value' => 'yes',
					'default' => 'yes',
				]
			);
			$this->add_control(
				'th_team_carousel_autoplay_delay',
				[
					'label' => esc_html__( 'Autoplay Delay', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 1000,
					'options' => [
						300  => esc_html__( '300ms', WP_MF_CORE_SLUG),
						400  => esc_html__( '400ms', WP_MF_CORE_SLUG),
						500  => esc_html__( '500ms', WP_MF_CORE_SLUG),
						600  => esc_html__( '600ms', WP_MF_CORE_SLUG),
						700  => esc_html__( '700ms', WP_MF_CORE_SLUG),
						800  => esc_html__( '800ms', WP_MF_CORE_SLUG),
						900  => esc_html__( '900ms', WP_MF_CORE_SLUG),
						1000  => esc_html__( '1s', WP_MF_CORE_SLUG),
						1500  => esc_html__( '1.5s', WP_MF_CORE_SLUG),
						2000  => esc_html__( '2s', WP_MF_CORE_SLUG),
						2500  => esc_html__( '2.5s', WP_MF_CORE_SLUG),
						3000  => esc_html__( '3s', WP_MF_CORE_SLUG),
					],
					'condition' => ['th_team_carousel_autoplay' => 'yes']
				]
			);
			$this->add_control(
				'th_team_carousel_play_speed',
				[
					'label' => esc_html__( 'Play Speed', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 500,
					'options' => [
						100  => esc_html__( '100ms', WP_MF_CORE_SLUG),
						200  => esc_html__( '200ms', WP_MF_CORE_SLUG),
						300  => esc_html__( '300ms', WP_MF_CORE_SLUG),
						400  => esc_html__( '400ms', WP_MF_CORE_SLUG),
						500  => esc_html__( '500ms', WP_MF_CORE_SLUG),
						600  => esc_html__( '600ms', WP_MF_CORE_SLUG),
						700  => esc_html__( '700ms', WP_MF_CORE_SLUG),
						800  => esc_html__( '800ms', WP_MF_CORE_SLUG),
						900  => esc_html__( '900ms', WP_MF_CORE_SLUG),
						1000  => esc_html__( '1s', WP_MF_CORE_SLUG),
						1500  => esc_html__( '1.5s', WP_MF_CORE_SLUG),
						2000  => esc_html__( '2s', WP_MF_CORE_SLUG),
						2500  => esc_html__( '2.5s', WP_MF_CORE_SLUG),
						3000  => esc_html__( '3s', WP_MF_CORE_SLUG),
					],
				]
			);
			$this->add_control(
				'th_team_carousel_per_slide',
				[
					'label' => esc_html__( 'Course Per Slide', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 2,
					'options' => [
						1  => esc_html__( '1', WP_MF_CORE_SLUG),
						2  => esc_html__( '2', WP_MF_CORE_SLUG),
						3  => esc_html__( '3', WP_MF_CORE_SLUG),
						4  => esc_html__( '4', WP_MF_CORE_SLUG),
						5  => esc_html__( '5', WP_MF_CORE_SLUG),
						6  => esc_html__( '6', WP_MF_CORE_SLUG),
						7  => esc_html__( '7', WP_MF_CORE_SLUG),
						8  => esc_html__( '8', WP_MF_CORE_SLUG),
						9  => esc_html__( '9', WP_MF_CORE_SLUG),
						10  => esc_html__( '10', WP_MF_CORE_SLUG),
					],
				]
			);
		$this->end_controls_section();

		/*
  		** Carousel Navigation section
		*/
	    $this->start_controls_section(
            'th_team_carousel_carousel_navigation',
	            [
	                'label' => esc_html__( 'Navigation', WP_MF_CORE_SLUG ),
	                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
	            ]
	        );
    		$this->add_control(
				'th_team_carousel_carousel_arrow_section',
				[
					'label' => esc_html__( 'Arrow', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::HEADING,
				]
			);
		    $this->add_control(
		    	'th_team_carousel_hide_arrow',
		    	[
		    		'label' 		=> esc_html__( 'Hide Arrow', WP_MF_CORE_SLUG ),
		    		'type' 			=> \Elementor\Controls_Manager::SWITCHER,
		    		'label_on' 		=> __( 'Show', WP_MF_CORE_SLUG ),
		    		'label_off' 	=> __( 'Hide', WP_MF_CORE_SLUG ),
		    		'return_value' 	=> 'yes',
		    		'default' 		=> 'no',
		    	]
		    );
    		
			$this->add_control(
				'th_team_carousel_arrow_position',
				[
					'label' => esc_html__( 'Select arrow position', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 4,
					'options' => [
						'ec-text-center'  => esc_html__( 'Center', WP_MF_CORE_SLUG ),
						'ec-text-right'  => esc_html__( 'Top Right', WP_MF_CORE_SLUG ),
						'ec-text-left'  => esc_html__( 'Top Left', WP_MF_CORE_SLUG ),
					],
					'default' => 'ec-text-center'
				]
			);
			$this->start_controls_tabs(
				'th_team_carousel_arrow_style_tabs'
			);

			$this->start_controls_tab(
				'th_team_carousel_arrow_normal_tab',
				[
					'label' => esc_html__( 'Normal', WP_MF_CORE_SLUG ),

				]
			);
			$this->add_control(
				'th_team_carousel_more_btn_background_color',
				[
					'label' => esc_html__( 'Background Color', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::COLOR,
					
					'selectors' => [
						'{{WRAPPER}} .ec-swiper-button-prev, {{WRAPPER}} .ec-swiper-button-next' => 'background-color: {{VALUE}}',
					],
					'default' => '#ff5166'
				]
			);
			$this->add_control(
				'th_team_carousel_more_btn_color',
				[
					'label' => esc_html__( 'Color', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::COLOR,
					
					'selectors' => [
						'{{WRAPPER}} .ec-swiper-button-prev, {{WRAPPER}} .ec-swiper-button-next' => 'color: {{VALUE}}',
					],
					'default' => '#fff'
				]
			);
			$this->add_control(
				'th_team_carousel_arrow_width',
				[
					'label' => esc_html__( 'Width', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 50,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ec-swiper-arrow > span' => 'width: {{SIZE}}{{UNIT}};',
					],
				]
			);
			$this->add_control(
				'th_team_carousel_arrow_height',
				[
					'label' => esc_html__( 'Height', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 50,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ec-swiper-arrow > span' => 'height: {{SIZE}}{{UNIT}};',
					],
				]
			);
			$this->add_control(
				'th_team_carousel_arrow_size',
				[
					'label' => esc_html__( 'Arrow Size', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 50,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ec-swiper-arrow > span > svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					],
				]
			);
			$this->add_control(
				'th_team_carousel_more_btn_margin',
				[
					'label' => esc_html__( 'Margin', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .ec-swiper-button-prev, {{WRAPPER}} .ec-swiper-button-next' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);
			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'th_team_carousel_more_btn_box_shadow',
					'label' => esc_html__( 'Box Shadow', WP_MF_CORE_SLUG ),
					'selector' => '{{WRAPPER}} .ec-swiper-button-prev, {{WRAPPER}} .ec-swiper-button-next',
				]
			);
			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'th_team_carousel_more_btn_border',
					'label' => __( 'Border', WP_MF_CORE_SLUG ),
					'selector' => '{{WRAPPER}} .ec-swiper-button-prev, {{WRAPPER}} .ec-swiper-button-next',
				]
			);
			
			$this->add_control(
				'th_team_carousel_more_btn_border_rad',
				[
					'label' => esc_html__( 'Border Radius', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 50,
							'step' => 1,
						]
					],
					'selectors' => [
						'{{WRAPPER}} .ec-swiper-button-prev, {{WRAPPER}} .ec-swiper-button-next' => 'border-radius: {{SIZE}}{{UNIT}};',
					]
				]
			);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'arrow_hover_tab',
				[
					'label' => esc_html__( 'Hover', WP_MF_CORE_SLUG ),
				]
			);

			$this->add_control(
				'th_team_carousel_more_btn_background_color_hover',
				[
					'label' => esc_html__( 'Background Color', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::COLOR,
					
					'selectors' => [
						'{{WRAPPER}} .ec-swiper-button-prev:hover, {{WRAPPER}} .ec-swiper-button-next:hover' => 'background-color: {{VALUE}}',
					],
					'default' => '#00214D'
				]
			);
			$this->add_control(
				'th_team_carousel_more_btn_color_hover',
				[
					'label' => esc_html__( 'Color', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::COLOR,
					
					'selectors' => [
						'{{WRAPPER}} .ec-swiper-button-prev:hover, {{WRAPPER}} .ec-swiper-button-next:hover' => 'color: {{VALUE}}',
					],
					'default' => '#fff'
				]
			);
			
			$this->add_control(
				'th_team_carousel_more_btn_margin_hover',
				[
					'label' => esc_html__( 'Margin', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .ec-swiper-button-prev:hover, {{WRAPPER}} .ec-swiper-button-next:hover' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);
			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'th_team_carousel_more_btn_box_shadow_hover',
					'label' => esc_html__( 'Box Shadow', WP_MF_CORE_SLUG ),
					'selector' => '{{WRAPPER}} .ec-swiper-button-prev:hover, {{WRAPPER}} .ec-swiper-button-next:hover',
				]
			);
			$this->add_control(
				'th_team_carousel_more_btn_border_rad_hover',
				[
					'label' => esc_html__( 'Border Radius', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px'],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 50,
							'step' => 1,
						]
					],
					'selectors' => [
						'{{WRAPPER}} .ec-swiper-button-prev:hover, {{WRAPPER}} .ec-swiper-button-next:hover' => 'border-radius: {{SIZE}}{{UNIT}};',
					]
				]
			);
			$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_control(
				'th_team_carousel_carousel_dot_nav_section',
				[
					'label' => esc_html__( 'Navigation', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::HEADING,
				]
			);
			$this->add_control(
		    	'th_team_carousel_hide_navigation',
		    	[
		    		'label' 		=> esc_html__( 'Hide Dot Navigation', WP_MF_CORE_SLUG ),
		    		'type' 			=> \Elementor\Controls_Manager::SWITCHER,
		    		'label_on' 		=> __( 'Show', WP_MF_CORE_SLUG ),
		    		'label_off' 	=> __( 'Hide', WP_MF_CORE_SLUG ),
		    		'return_value' 	=> 'yes',
		    		'default' 		=> 'no',
		    	]
		    );
		    $this->start_controls_tabs(
				'th_team_carousel_dot_nav_style_tabs'
			);

				$this->start_controls_tab(
					'th_team_carousel_dot_nav_normal_tab',
					[
						'label' => esc_html__( 'Normal', WP_MF_CORE_SLUG ),

					]
				);
				$this->add_control(
					'th_team_carousel_dot_nav_background_color',
					[
						'label' => esc_html__( 'Color', WP_MF_CORE_SLUG ),
						'type' => \Elementor\Controls_Manager::COLOR,
						
						'selectors' => [
							'{{WRAPPER}} .ec-swiper-pagination .swiper-pagination-bullet' => 'background-color: {{VALUE}}',
						],
						'default' => '#ff5166'
					]
				);
				$this->add_control(
					'width_team_carousel_dot_nav_background_opacity',
					[
						'label' => esc_html__( 'Opacity', WP_MF_CORE_SLUG ),
						'type' => \Elementor\Controls_Manager::SLIDER,
						'size_units' => [ '%' ],
						'range' => [
							'px' => [
								'min' => 0,
								'max' => 100,
								'step' => 1,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ec-swiper-pagination .swiper-pagination-bullet' => 'opacity: {{SIZE}}%;',
						],
					]
				);
				$this->add_control(
					'th_team_carousel_dot_nav_width',
					[
						'label' => esc_html__( 'Width', WP_MF_CORE_SLUG ),
						'type' => \Elementor\Controls_Manager::SLIDER,
						'size_units' => [ 'px', '%' ],
						'range' => [
							'px' => [
								'min' => 0,
								'max' => 50,
								'step' => 1,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ec-swiper-pagination .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}};',
						],
					]
				);
				$this->add_control(
					'th_team_carousel_dot_nav_height',
					[
						'label' => esc_html__( 'Height', WP_MF_CORE_SLUG ),
						'type' => \Elementor\Controls_Manager::SLIDER,
						'size_units' => [ 'px', '%' ],
						'range' => [
							'%' => [
								'min' => 0,
								'max' => 50,
								'step' => 1,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ec-swiper-pagination .swiper-pagination-bullet' => 'height: {{SIZE}}{{UNIT}};',
						],
					]
				);
				$this->add_control(
					'th_team_carousel_dot_nav_margin',
					[
						'label' => esc_html__( 'Margin', WP_MF_CORE_SLUG ),
						'type' => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em' ],
						'selectors' => [
							'{{WRAPPER}} .ec-swiper-pagination .swiper-pagination-bullet' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);
				
				$this->add_control(
					'th_team_carousel_dot_nav_border_rad',
					[
						'label' => esc_html__( 'Border Radius', WP_MF_CORE_SLUG ),
						'type' => \Elementor\Controls_Manager::SLIDER,
						'size_units' => [ 'px'],
						'range' => [
							'px' => [
								'min' => 0,
								'max' => 50,
								'step' => 1,
							]
						],
						'selectors' => [
							'{{WRAPPER}} .ec-swiper-pagination .swiper-pagination-bullet' => 'border-radius: {{SIZE}}{{UNIT}};',
						]
					]
				);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'th_team_carousel_dot_nav_hover_tab',
					[
						'label' => esc_html__( 'Active', WP_MF_CORE_SLUG ),
					]
				);

				$this->add_control(
					'width_team_carousel_dot_nav_background_opacity_active',
					[
						'label' => esc_html__( 'Opacity', WP_MF_CORE_SLUG ),
						'type' => \Elementor\Controls_Manager::SLIDER,
						'size_units' => [ '%' ],
						'range' => [
							'px' => [
								'min' => 0,
								'max' => 100,
								'step' => 1,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ec-swiper-pagination .swiper-pagination-bullet-active' => 'opacity: {{SIZE}}%;',
						],
					]
				);
				$this->add_control(
					'th_team_carousel_dot_nav_background_color_hover',
					[
						'label' => esc_html__( 'Color', WP_MF_CORE_SLUG ),
						'type' => \Elementor\Controls_Manager::COLOR,
						
						'selectors' => [
							'{{WRAPPER}} .ec-swiper-pagination .swiper-pagination-bullet-active' => 'background-color: {{VALUE}}',
						],
						'default' => '#00214D'
					]
				);
				$this->add_control(
					'th_team_carousel_dot_nav_width_hover',
					[
						'label' => esc_html__( 'Width', WP_MF_CORE_SLUG ),
						'type' => \Elementor\Controls_Manager::SLIDER,
						'size_units' => [ 'px', '%' ],
						'range' => [
							'px' => [
								'min' => 0,
								'max' => 50,
								'step' => 1,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ec-swiper-pagination .swiper-pagination-bullet-active' => 'width: {{SIZE}}{{UNIT}};',
						],
					]
				);
				$this->add_control(
					'th_team_carousel_dot_nav_height_hover',
					[
						'label' => esc_html__( 'Height', WP_MF_CORE_SLUG ),
						'type' => \Elementor\Controls_Manager::SLIDER,
						'size_units' => [ 'px', '%' ],
						'range' => [
							'px' => [
								'min' => 0,
								'max' => 50,
								'step' => 1,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ec-swiper-pagination .swiper-pagination-bullet-active' => 'height: {{SIZE}}{{UNIT}};',
						],
					]
				);
				
				$this->add_control(
					'th_team_carousel_dot_nav_rad_hover',
					[
						'label' => esc_html__( 'Border Radius', WP_MF_CORE_SLUG ),
						'type' => \Elementor\Controls_Manager::SLIDER,
						'size_units' => [ 'px'],
						'range' => [
							'px' => [
								'min' => 0,
								'max' => 50,
								'step' => 1,
							]
						],
						'selectors' => [
							'{{WRAPPER}} .swiper-pagination-bullet-active' => 'border-radius: {{SIZE}}{{UNIT}};',
						]
					]
				);
				$this->end_controls_tab();
			
			$this->end_controls_tabs();

		$this->end_controls_section();
			$this->start_controls_section(
				'th_team_carousel_section_style',
				[
					'label' => esc_html__( 'Box Styles', WP_MF_CORE_SLUG ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);
			$this->add_control(
				'th_team_carousel_box',
				[
					'label' => esc_html__( 'Border Radius', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 50,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .th-team-block' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
					'default' => [
						'unit' => 'px',
						'size' => 3
					]
				]
			);
			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'th_team_carousel_box_shadow',
					'label' => esc_html__( 'Box Shadow', WP_MF_CORE_SLUG ),
					'selector' => '{{WRAPPER}} .th-team-block',
				]
			);
			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'th_team_carousel_border',
					'label' => esc_html__( 'Border', WP_MF_CORE_SLUG ),
					'selector' => '{{WRAPPER}} .th-team-block',
				]
			);
			$this->add_control(
				'th_team_carousel_color_bg',
				[
					'label' => esc_html__( 'Background', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::COLOR,
					
					'selectors' => [
						'{{WRAPPER}} .th-team-block' => 'background-color: {{VALUE}}',
					],
					'default' => '#ffffff'
				]
			);
			$this->add_control(
				'th_team_carousel_padding',
				[
					'label' => esc_html__( 'Padding', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .th-team-block' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'default' => [
						'top' => 30,
						'right' => 30,
						'bottom' => 30,
						'left' => 30,
						'unit' => 'px',
						'isLinked' => true,
					]
				]
			);
			$this->end_controls_section();

			$this->start_controls_section(
				'th_team_carousel_image_style',
				[
					'label' => esc_html__( 'Image', WP_MF_CORE_SLUG ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);
			$this->add_control(
				'hide_image_overflow',
				[
					'label' => esc_html__( 'Hide Image Overflow', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => __( 'Show', WP_MF_CORE_SLUG ),
					'label_off' => __( 'Hide', WP_MF_CORE_SLUG ),
					'return_value' => 'yes',
					'default' => 'yes',
				]
			);
			$this->add_responsive_control(
				'th_team_carousel_image_height',
				[
					'label' => esc_html__( 'Image Height', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 250,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .th-team-thumbnail, {{WRAPPER}} .th-team-thumbnail img' => 'height: {{SIZE}}{{UNIT}};',
					],
					'default' => [
						'unit' => 'px',
						'size' => 200
					]
				]
			);
			$this->add_responsive_control(
				'th_team_carousel_image_widht',
				[
					'label' => esc_html__( 'Image Width', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 250,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .th-team-thumbnail, {{WRAPPER}} .th-team-thumbnail img' => 'width: {{SIZE}}{{UNIT}};',
					],
					'default' => [
						'unit' => 'px',
						'size' => 200
					]
				]
			);
			$this->add_control(
				'th_team_carousel_image_margin',
				[
					'label' => esc_html__( 'Margin', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .th-team-thumbnail img' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);
			$this->add_control(
				'th_team_carousel_bor_rad',
				[
					'label' => esc_html__( 'Border Radius', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .th-team-thumbnail img, {{WRAPPER}} .th-team-thumbnail' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'default' => [
						'top' => 30,
						'right' => 30,
						'bottom' => 30,
						'left' => 30,
						'unit' => 'px',
						'isLinked' => true,
					]
				]
			);
			$this->end_controls_section();


			// Content Styles Section
			$this->start_controls_section(
				'th_team_carousel_content_style',
				[
					'label' => esc_html__( 'Content', WP_MF_CORE_SLUG ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);
			$this->add_control(
				'th_team_carousel_title_head',
				[
					'label' => esc_html__( 'Title', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);
			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'th_team_carousel_title_typo',
					'label' => esc_html__( 'Typography', WP_MF_CORE_SLUG ),
					
					'selector' => '{{WRAPPER}} .th-team-name',
				]
			);
			$this->add_control(
				'th_team_carousel_title_color',
				[
					'label' => esc_html__( 'Title Color', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::COLOR,
					
					'selectors' => [
						'{{WRAPPER}} .th-team-name' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'th_team_carousel_title_margin',
				[
					'label' => esc_html__( 'Spacing', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .th-team-name' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'th_team_carousel_designation_head',
				[
					'label' => esc_html__( 'Designation', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);
			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'th_team_carousel_designation_typo',
					'label' => esc_html__( 'Typography', WP_MF_CORE_SLUG ),
					
					'selector' => '{{WRAPPER}} .th-team-designation',
				]
			);
			$this->add_control(
				'th_team_carousel_designation_color',
				[
					'label' => esc_html__( 'Color', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::COLOR,
					
					'selectors' => [
						'{{WRAPPER}} .th-team-designation' => 'color: {{VALUE}}',
					],
				]
			);
			$this->add_control(
				'th_team_carousel_designation_margin',
				[
					'label' => esc_html__( 'Spacing', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .th-team-designation' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'th_team_carousel_desc_head',
				[
					'label' => esc_html__( 'Description', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);
			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' => 'th_team_carousel_desc_typo',
					'label' => esc_html__( 'Typography', WP_MF_CORE_SLUG ),
					
					'selector' => '{{WRAPPER}} .th-team-description',
				]
			);
			$this->add_control(
				'th_team_carousel_desc_color',
				[
					'label' => esc_html__( 'Color', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::COLOR,
					
					'selectors' => [
						'{{WRAPPER}} .th-team-description' => 'color: {{VALUE}}',
					],
				]
			);
			$this->add_control(
				'th_team_carousel_desc_margin',
				[
					'label' => esc_html__( 'Spacing', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .th-team-description' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);
			$this->end_controls_section();

			$this->start_controls_section(
				'th_team_carousel_social_style',
				[
					'label' => esc_html__( 'Social Profiles', WP_MF_CORE_SLUG ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_control(
				'th_team_carousel_social_bg',
				[
					'label' => esc_html__( 'Container Background', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::COLOR,
					
					'selectors' => [
						'{{WRAPPER}} ul.th-team-socials' => 'background-color: {{VALUE}}',
					],
					'default' => '#f1f1f1',
				]
			);
			$this->add_control(
				'th_team_carousel_social_link_color',
				[
					'label' => esc_html__( 'Color', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::COLOR,
					
					'selectors' => [
						'{{WRAPPER}} ul.th-team-socials li a i' => 'color: {{VALUE}}',
					],
				]
			);
			$this->add_control(
				'th_team_carousel_social_link_hover_color',
				[
					'label' => esc_html__( 'Hover Color', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::COLOR,
					
					'selectors' => [
						'{{WRAPPER}} ul.th-team-socials li a:hover i' => 'color: {{VALUE}}',
					],
				]
			);
			$this->add_control(
				'th_team_carousel_social_bg_color',
				[
					'label' => esc_html__( 'background', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::COLOR,
					
					'selectors' => [
						'{{WRAPPER}} ul.th-team-socials li a i' => 'background-color: {{VALUE}}',
					],
					'default' => '#f1f1f1',
				]
			);
			$this->add_control(
				'th_team_carousel_social_font_size',
				[
					'label' => esc_html__( 'Font Size', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => 8,
							'max' => 50,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} ul.th-team-socials li a i' => 'font-size: {{SIZE}}{{UNIT}};',
					],
					'default' => [
						'size' => 16,
						'unit' => 'px'
					],
				]
			);
			$this->add_control(
				'th_team_carousel_social_br',
				[
					'label' => esc_html__( 'Border Radius', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 50,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} ul.th-team-socials li a i' => 'border-radius: {{SIZE}}{{UNIT}};',
					],
				]
			);
			$this->add_control(
				'th_team_carousel_social_items_padding',
				[
					'label' => esc_html__( 'Padding', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 50,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} ul.th-team-socials li a i' => 'padding: {{SIZE}}{{UNIT}};',
					],
				]
			);
			$this->add_control(
				'th_team_carousel_social_icons_spacing',
				[
					'label' => esc_html__( 'Spacing', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 50,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} ul.th-team-socials li' => 'margin-right: {{SIZE}}{{UNIT}};margin-left: {{SIZE}}{{UNIT}};',
					],
					'default' => [
						'size' => 7,
						'unit' => 'px'
					],
				]
			);
			$this->add_control(
				'th_team_carousel_social_items_bg',
				[
					'label' => esc_html__( 'Container Background', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::COLOR,
					
					'selectors' => [
						'{{WRAPPER}} ul.th-team-socials' => 'background-color: {{VALUE}}',
					],
				]
			);
			$this->add_control(
				'th_team_carousel_social_items_con_padding',
				[
					'label' => esc_html__( 'Container padding', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'selectors' => [
						'{{WRAPPER}} ul.th-team-socials' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);
			$this->add_responsive_control(
				'th_team_carousel_social_border_rad',
				[
					'label' => esc_html__( 'Border Radius', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'selectors' => [
						'{{WRAPPER}} ul.th-team-socials' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);
			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'th_team_carousel_socails_box_shadow',
					'label' => esc_html__( 'Box Shadow', WP_MF_CORE_SLUG ),
					'selector' => '{{WRAPPER}} ul.th-team-socials',
				]
			);
			$this->add_responsive_control(
				'th_team_carousel_social_container_vertical_position',
				[
					'label' => esc_html__( 'Vertical Position', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => -200,
							'max' => 300,
							'step' => 1,
						],
						'%' => [
							'min' => -100,
							'max' => 100,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} ul.th-team-socials' => 'top: {{SIZE}}{{UNIT}};',
					],
				]
			);
			$this->add_responsive_control(
				'th_team_carousel_social_items_horizontal_position',
				[
					'label' => esc_html__( 'Horizontal Position', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => -200,
							'max' => 200,
							'step' => 1,
						],
						'%' => [
							'min' => -100,
							'max' => 100,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} ul.th-team-socials' => 'right: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->end_controls_section();

		}
		protected function tophiveTeamRepeaterAdditionalFields( $r ){
			$r->add_control(
				'th_team_carousel_additional_fields',
				[
					'label' => esc_html__( 'Additional text', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => esc_html__( 'Default title', WP_MF_CORE_SLUG ),
				]
			);
		}
		protected function render(){
			$team = $this->get_settings_for_display();
			$slider_sl = rand();
			$overflow = 'yes' == $team['hide_image_overflow'] ? ' o-hidden' : '';

			?>
			<div class="ec-swiper-fixed-width">
				<div class="ec-swiper-container ec-swiper-container-<?php echo $slider_sl . ' ' . $team['th_team_carousel_posts_layout']; ?>" id="ec-swiper-container-<?php echo $slider_sl ?>">
				    <!-- Additional required wrapper -->
				    <div class="swiper-wrapper ec-dynamic-slides">
				        <!-- Slides -->
				        <?php  
				        	$members = $team['th_team_carousel_items'];
				        	if( !empty($members) ){
				        		foreach ($members as $member) {
				        			$html = '';
										$html .= '<div class="swiper-slide th-team-block '. $team['th_team_carousel_text_align'] .'">';
											$html .= '<div class="th-team-thumbnail'. $overflow .'">';
												$html .= '<img src="' . $member['th_team_carousel_image']['url'] . '" alt="team-member" />';
											$html .= '</div>';
											$html .= '<div class="th-team-name">';
												$html .= $member['th_team_carousel_title'];
											$html .= '</div>';
											$html .= '<div class="th-team-designation">';
												$html .= $member['th_team_carousel_designation'];
											$html .= '</div>';
											$html .= '<div class="th-team-description">';
												$html .= $member['th_team_carousel_description'];
											$html .= '</div>';
											if( 'yes' == $team['show_social_icons'] ){
												$html .= '<ul class="th-team-socials">';
													if ( !empty($member['th_team_carousel_social_link_facebook']) ) {
														$html .= '<li><a href="'. $member['th_team_carousel_social_link_facebook'] .'"><i class="fa fa-facebook"></i></a></li>';
													}
													if ( !empty($member['th_team_carousel_social_link_twitter']) ) {
														$html .= '<li><a href="'. $member['th_team_carousel_social_link_twitter'] .'"><i class="fa fa-twitter"></i></a></li>';
													}
													if ( !empty($member['th_team_carousel_social_link_linkedin']) ) {
														$html .= '<li><a href="'. $member['th_team_carousel_social_link_linkedin'] .'"><i class="fa fa-linkedin"></i></a></li>';
													}
													if ( !empty($member['th_team_carousel_social_link_instagram']) ) {
														$html .= '<li><a href="'. $member['th_team_carousel_social_link_instagram'] .'"><i class="fa fa-instagram"></i></a></li>';
													}
													if ( !empty($member['th_team_carousel_social_link_youtube']) ) {
														$html .= '<li><a href="'. $member['th_team_carousel_social_link_youtube'] .'"><i class="fa fa-youtube"></i></a></li>';
													}
													if ( !empty($member['th_team_carousel_social_link_gplus']) ) {
														$html .= '<li><a href="'. $member['th_team_carousel_social_link_gplus'] .'"><i class="fa fa-google-plus"></i></a></li>';
													}
													if ( !empty($member['th_team_carousel_social_link_vimeo']) ) {
														$html .= '<li><a href="'. $member['th_team_carousel_social_link_vimeo'] .'"><i class="fa fa-vimeo"></i></a></li>';
													}
												$html .= '</ul>';
											}

										echo $html .= '</div>';
				        		}
				        	}
				        ?>
				    </div>

				    

				</div>	
				<?php if( $team['th_team_carousel_hide_arrow'] !== 'yes' ){ ?>
					<div class="ec-swiper-arrow">
						<span class="ec-swiper-button-prev ec-swiper-button-prev-<?php echo $slider_sl?>">
							<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-chevron-left" fill="currentColor" preserveAspectRatio="xMidYMin" xmlns="http://www.w3.org/2000/svg">
							  <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
							</svg>
						</span>
						<span class="ec-swiper-button-next ec-swiper-button-next-<?php echo $slider_sl?>">
							<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-chevron-right" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
							  <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
							</svg>
						</span>
					</div>
				<?php } ?>
				<?php if( $team['th_team_carousel_hide_navigation'] !== 'yes' ){ ?>
					<div class="ec-swiper-pagination ec-swiper-pagination-<?php echo $slider_sl?>"></div>
				<?php } ?>
			</div>
			<script>
				jQuery(document).ready(function(){
					var THslider = new Swiper ('#ec-swiper-container-<?php echo $slider_sl; ?>', {
				    // Optional parameters
					    slidesPerView: <?php echo $team['th_team_carousel_select_columns'] ?>,
					    spaceBetween: 20,
					    slidesPerGroup: <?php echo $team['th_team_carousel_per_slide'] ?>,
					    loop: false,
					    autoplay : <?php echo $team['th_team_carousel_autoplay'] == 'yes' ? var_export(true) : var_export(false) ?>,
					    speed: <?php echo $team['th_team_carousel_play_speed'] ?>,
					    loopFillGroupWithBlank: true,
					    breakpoints: {
						    300: {
						      	slidesPerView: 1,
						      	spaceBetween: 10
						    },
						    700: {
						      	slidesPerView: 2,
						      	spaceBetween: 10
						    },
						    1000: {
						      	slidesPerView: <?php echo $team['th_team_carousel_select_columns'] ?>,
						      	spaceBetween: 20
						    }
						},
					    // If we need pagination
					    pagination: {
					      el: '.ec-swiper-pagination-<?php echo $slider_sl?>',
					    },

					    // Navigation arrows
					    navigation: {
					      nextEl: '.ec-swiper-button-next-<?php echo $slider_sl?>',
					      prevEl: '.ec-swiper-button-prev-<?php echo $slider_sl?>',
					    },

					    // And if we need scrollbar
					    scrollbar: {
					      el: '.ec-swiper-scrollbar',
					    },
				  	});
				})
			</script>
			<?php
		}
		
	}

?>