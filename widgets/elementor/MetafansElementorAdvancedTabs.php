<?php 
	

namespace METAFANSCORE\widgets\elementor;
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 **
 * Metafans elementor Advanced tabs
 * Since Version 1.0.0
 * @package wordpress
 * @subpackage MasterClass
 *
 *
 */
class MetafansElementorAdvancedTabs extends \Elementor\Widget_base
{
	public function get_title(){
		return esc_html__( 'Advanced Tabs', WP_MF_CORE_SLUG );
	}
	public function get_name(){
		return 'th-team-advanced-tabs';
	}
	public function get_icon(){
		return 'eicon-tabs';
	}
	public function get_categories(){
        return [ WP_MF_CORE_SLUG ];
    }
	public function get_keywords() {
		return [ 'tabs' ];
	}
	protected function register_controls(){

		$this->start_controls_section(
				'th_adv_tabs_section',
				[
					'label' => esc_html__( 'Advanced tabs', WP_MF_CORE_SLUG ),
					'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
				]
			);

			$repeater = new \Elementor\Repeater();
			$this->add_control(
				'th_adv_tabs_layout',
				[
					'label' => esc_html__( 'Layout', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'options' => [
						'nav-top' 	=> esc_html__( 'Navigation Top', WP_MF_CORE_SLUG ),
						'nav-left' 	=> esc_html__( 'Navigation Left', WP_MF_CORE_SLUG ),
						'nav-right' 	=> esc_html__( 'Navigation Right', WP_MF_CORE_SLUG ),
					],
					'default' => 'nav-top',
				]
			);
			$repeater->add_control(
				'th_adv_tabs_icon',
				[
					'label' => esc_html__( 'Choose Icon', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::ICONS
				]
			);
			$repeater->add_control(
				'th_adv_tabs_title',
				[
					'label' => esc_html__( 'Tab Title', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => esc_html__( 'This is title', WP_MF_CORE_SLUG ),
					'placeholder' => esc_html__( 'Type your title here', WP_MF_CORE_SLUG ),
				]
			);
			$repeater->add_control(
				'th_adv_tabs_desc',
				[
					'label' => esc_html__( 'Tab title description', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::TEXT,
					'default' => esc_html__( 'Description', WP_MF_CORE_SLUG ),
					'default' => esc_html__( 'Description text goes here', WP_MF_CORE_SLUG ),
				]
			);
			$repeater->add_control(
				'th_adv_tabs_content_image',
				[
					'label' => esc_html__( 'Tab Content Image', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::MEDIA,
					'default' => [
						'url' => \Elementor\Utils::get_placeholder_image_src(),
					],
				]
			);
			$repeater->add_control(
				'th_adv_tabs_content_text',
				[
					'label' => esc_html__( 'Tab Content Text', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::TEXTAREA
				]
			);
			
			$this->add_control(
				'th_adv_tabs_items',
				[
					'label' => esc_html__( 'Add New Tab', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::REPEATER,
					'fields' => $repeater->get_controls(),
					'default' => [
						[
							'th_adv_tabs_title' => esc_html__( 'New Tab Title', WP_MF_CORE_SLUG )
						]
					],
				]
			);

		$this->end_controls_section();
		$this->start_controls_section(
			'th_adv_tabs_carousel_options',
				[
					'label' => esc_html__( 'Tab Options', WP_MF_CORE_SLUG ),
					'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
				]
			);
			$this->add_control(
				'th_adv_tabs_autoplay',
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
				'th_adv_tabs_autoplay_delay',
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
					'condition' => ['th_adv_tabs_autoplay' => 'yes']
				]
			);
		$this->end_controls_section();

			$this->start_controls_section(
				'th_adv_tabs_main_style',
				[
					'label' => esc_html__( 'Tabs Style', WP_MF_CORE_SLUG ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);
			$this->add_control(
				'th_adv_tabs_main_margin',
				[
					'label' => esc_html__( 'Margin', 'plugin-domain' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .tophive-advanced-tab-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);
			$this->add_control(
				'th_adv_tabs_main_padding',
				[
					'label' => esc_html__( 'Padding', 'plugin-domain' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .tophive-advanced-tab-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);
			$this->add_control(
				'th_adv_tabs_main_content_width',
				[
					'label' => esc_html__( 'Width', 'plugin-domain' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ '%' ],
					'range' => [
						'%' => [
							'min' => 0,
							'max' => 100,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .tophive-advanced-tab-content' => 'flex: 1 0 {{SIZE}}%;',
					],
				]
			);
			$this->add_control(
				'th_adv_tabs_item_animator_bg',
				[
					'label' => esc_html__( 'Animator Background Color', 'plugin-domain' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					
					'selectors' => [
						'{{WRAPPER}} .tophive-advanced-tab-container ul.tophive-advanced-tab-nav li a .tab-animator' => 'background-color: {{VALUE}}',
					],
				]
			);
		$this->end_controls_section();
		$this->start_controls_section(
				'th_adv_tabs_nav_style',
				[
					'label' => esc_html__( 'Nav Tab Style', WP_MF_CORE_SLUG ),
					'tab' => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);
			$this->add_control(
				'th_adv_tabs_nav_margin',
				[
					'label' => esc_html__( 'Margin', 'plugin-domain' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .tophive-advanced-tab-nav' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);
			$this->add_control(
				'th_adv_tabs_nav_bg_color',
				[
					'label' => esc_html__( 'Backgound Color', 'plugin-domain' ),
					'type' => \Elementor\Controls_Manager::COLOR,
					
					'selectors' => [
						'{{WRAPPER}} .tophive-advanced-tab-nav' => 'background-color: {{VALUE}}',
					],
					'default' => 'transparent'
				]
			);
			$this->add_group_control(
				\Elementor\Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'th_adv_tabs_nav_bg_box_shadow',
					'label' => esc_html__( 'Box Shadow', 'plugin-domain' ),
					'selector' => '{{WRAPPER}} .tophive-advanced-tab-nav',
				]
			);
			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'th_adv_tabs_nav_bg_border',
					'label' => esc_html__( 'Border', 'plugin-domain' ),
					'selector' => '{{WRAPPER}} .tophive-advanced-tab-nav',
				]
			);
			$this->add_control(
				'th_adv_tabs_nav_items_head',
				[
					'label' => esc_html__( 'Nav Items', 'plugin-name' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);
			$this->add_control(
				'th_adv_tabs_nav_items_margin',
				[
					'label' => esc_html__( 'Item Margin', 'plugin-domain' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .tophive-advanced-tab-container ul.tophive-advanced-tab-nav li a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);
			$this->add_control(
				'th_adv_tabs_nav_icon_position',
				[
					'label' => esc_html__( 'Icon Position', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::CHOOSE,
					'options' => [
						'icon-left' => [
							'title' => esc_html__( 'Left', WP_MF_CORE_SLUG ),
							'icon' => 'eicon-h-align-left',
						],
						'icon-right' => [
							'title' => esc_html__( 'Right', WP_MF_CORE_SLUG ),
							'icon' => 'eicon-h-align-right',
						],
					],
					'default' => 'icon-left',
					'toggle' => true,
				]
			);
			
			$this->add_control(
				'th_adv_tabs_nav_icon_width',
				[
					'label' => esc_html__( 'Width', 'plugin-domain' ),
					'type' => \Elementor\Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 100,
							'step' => 1,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .tophive-advanced-tab-container ul.tophive-advanced-tab-nav li a img' => 'width: {{SIZE}}{{UNIT}};',
					],
				]
			);
			$this->add_control(
				'th_adv_tabs_nav_icon_margin',
				[
					'label' => esc_html__( 'Icon Spacing', 'plugin-domain' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'selectors' => [
						'{{WRAPPER}} .tophive-advanced-tab-container ul.tophive-advanced-tab-nav li a img' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);
			$this->add_control(
				'th_adv_tabs_nav_text_align',
				[
					'label' => esc_html__( 'Text Alignment', WP_MF_CORE_SLUG ),
					'type' => \Elementor\Controls_Manager::CHOOSE,
					'options' => [
						'ec-text-left' => [
							'title' => esc_html__( 'Left', WP_MF_CORE_SLUG ),
							'icon' => 'fa fa-align-left',
						],
						'ec-text-center' => [
							'title' => esc_html__( 'Center', WP_MF_CORE_SLUG ),
							'icon' => 'fa fa-align-center',
						],
						'ec-text-right' => [
							'title' => esc_html__( 'Right', WP_MF_CORE_SLUG ),
							'icon' => 'fa fa-align-right',
						],
					],
					'default' => 'ec-text-center',
					'toggle' => true,
				]
			);
			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
					[
						'name' => 'th_adv_tabs_nav_item_tpyo',
						'label' => esc_html__( 'Title Typography', 'plugin-domain' ),
						
						'selector' => '{{WRAPPER}} .text',
					]
				);
				$this->start_controls_tabs(
					'th_adv_tabs_nav_items_hover_normal_tabs'
				);

					$this->start_controls_tab(
							'th_adv_tabs_nav_items_normal_tab',
							[
								'label' => esc_html__( 'Normal', WP_MF_CORE_SLUG ),
							]
						);
						$this->add_group_control(
							\Elementor\Group_Control_Typography::get_type(),
							[
								'name' => 'th_adv_tabs_nav_item_title_typo',
								'label' => esc_html__( 'Title Typography', 'plugin-domain' ),
								
								'selector' => '{{WRAPPER}} .tophive-advanced-tab-container ul.tophive-advanced-tab-nav li a .nav-tab-desc-content .nav-tab-title',
							]
						);
						$this->add_control(
							'th_adv_tabs_nav_item_title_color',
							[
								'label' => esc_html__( 'Title Color', 'plugin-domain' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								
								'selectors' => [
									'{{WRAPPER}} .tophive-advanced-tab-container ul.tophive-advanced-tab-nav li a .nav-tab-desc-content .nav-tab-title' => 'color: {{VALUE}}',
								],
							]
						);
						$this->add_group_control(
							\Elementor\Group_Control_Typography::get_type(),
							[
								'name' => 'th_adv_tabs_nav_item_desc_typo',
								'label' => esc_html__( 'Description Typography', 'plugin-domain' ),
								
								'selector' => '{{WRAPPER}}.tophive-advanced-tab-container ul.tophive-advanced-tab-nav li a .nav-tab-desc-content .nav-tab-desc',
							]
						);
						$this->add_control(
							'th_adv_tabs_nav_item_desc_color',
							[
								'label' => esc_html__( 'Description Color', 'plugin-domain' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								
								'selectors' => [
									'{{WRAPPER}} .tophive-advanced-tab-container ul.tophive-advanced-tab-nav li a .nav-tab-desc-content .nav-tab-desc' => 'color: {{VALUE}}',
								],
							]
						);
						$this->add_control(
							'th_adv_tabs_nav_item_bg',
							[
								'label' => esc_html__( 'Background Color', 'plugin-domain' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								
								'selectors' => [
									'{{WRAPPER}} .tophive-advanced-tab-container ul.tophive-advanced-tab-nav li a' => 'color: {{VALUE}}',
								],
							]
						);
						$this->add_group_control(
							\Elementor\Group_Control_Border::get_type(),
							[
								'name' => 'th_adv_tabs_nav_item_border',
								'label' => __( 'Border', 'plugin-domain' ),
								'selector' => '{{WRAPPER}} .tophive-advanced-tab-container ul.tophive-advanced-tab-nav li a',
							]
						);
						$this->add_group_control(
							\Elementor\Group_Control_Box_Shadow::get_type(),
							[
								'name' => 'th_adv_tabs_nav_item_box_shadow',
								'label' => esc_html__( 'Box Shadow', 'plugin-domain' ),
								'selector' => '{{WRAPPER}} .tophive-advanced-tab-container ul.tophive-advanced-tab-nav li a',
							]
						);
					$this->end_controls_tab();
					$this->start_controls_tab(
							'th_adv_tabs_nav_items_active_tab',
							[
								'label' => esc_html__( 'Active', WP_MF_CORE_SLUG ),
							]
						);

						$this->add_control(
							'th_adv_tabs_nav_item_title_color_active',
							[
								'label' => esc_html__( 'Title Color', 'plugin-domain' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								
								'selectors' => [
									'{{WRAPPER}} .tophive-advanced-tab-container ul.tophive-advanced-tab-nav li a.active .nav-tab-desc-content .nav-tab-title' => 'color: {{VALUE}}',
								],
							]
						);
						$this->add_control(
							'th_adv_tabs_nav_item_desc_color_active',
							[
								'label' => esc_html__( 'Description Color', 'plugin-domain' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								
								'selectors' => [
									'{{WRAPPER}} .tophive-advanced-tab-container ul.tophive-advanced-tab-nav li a.active .nav-tab-desc-content .nav-tab-desc' => 'color: {{VALUE}}',
								],
							]
						);
						$this->add_control(
							'th_adv_tabs_nav_item_bg_active',
							[
								'label' => esc_html__( 'Background Color', 'plugin-domain' ),
								'type' => \Elementor\Controls_Manager::COLOR,
								
								'selectors' => [
									'{{WRAPPER}} .tophive-advanced-tab-container ul.tophive-advanced-tab-nav li a.active' => 'color: {{VALUE}}',
								],
							]
						);
						$this->add_group_control(
							\Elementor\Group_Control_Border::get_type(),
							[
								'name' => 'th_adv_tabs_nav_item_border_active',
								'label' => __( 'Border', 'plugin-domain' ),
								'selector' => '{{WRAPPER}} .tophive-advanced-tab-container ul.tophive-advanced-tab-nav li a.active',
							]
						);
						$this->add_group_control(
							\Elementor\Group_Control_Box_Shadow::get_type(),
							[
								'name' => 'th_adv_tabs_nav_item_box_shadow_active',
								'label' => esc_html__( 'Box Shadow', 'plugin-domain' ),
								'selector' => '{{WRAPPER}} .tophive-advanced-tab-container ul.tophive-advanced-tab-nav li a.active',
							]
						);
					$this->end_controls_tab();
				$this->end_controls_tabs();

			$this->end_controls_section();
			$this->start_controls_section(
				'th_adv_tabs_content_style',
					[
						'label' => esc_html__( 'Tab Content', WP_MF_CORE_SLUG ),
						'tab' => \Elementor\Controls_Manager::TAB_STYLE,
					]
				);
				$this->add_control(
					'th_adv_tabs_content_margin',
					[
						'label' => esc_html__( 'COntent Margin', 'plugin-domain' ),
						'type' => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em' ],
						'selectors' => [
							'{{WRAPPER}} .tophive-advanced-tab-content-panel' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);
				$this->add_control(
					'th_adv_tabs_content_img_br',
					[
						'label' => esc_html__( 'Image Border Radius', 'plugin-domain' ),
						'type' => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em' ],
						'selectors' => [
							'{{WRAPPER}} .tophive-advanced-tab-container .tophive-advanced-tab-content .tophive-advanced-tab-content-panel img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);
			$this->end_controls_section();

		}
		protected function render(){
			$tabs = $this->get_settings_for_display();

			$tabs_items = $tabs['th_adv_tabs_items'];

			$layout = $tabs['th_adv_tabs_layout'];

			?>
			<div class="tophive-advanced-tab-container <?php echo $layout; ?>">
				<ul class="tophive-advanced-tab-nav">
					<?php 
						foreach ($tabs_items as $tab_item) {
							echo '<li><a href="#panel'. $tab_item['_id'] .'" class="'. $tabs['th_adv_tabs_nav_icon_position'] .'">
								<span class="tab-animator"></span>
								<span class="nav-tab-icon">';
								\Elementor\Icons_Manager::render_icon( $settings['th_adv_tabs_icon'], [ 'aria-hidden' => 'true' ] );
							echo '</span>
								<span class="nav-tab-desc-content '. $tabs['th_adv_tabs_nav_text_align'] .'">
									<span class="nav-tab-title">'. $tab_item['th_adv_tabs_title'] .'</span>
									<span class="nav-tab-desc">'. $tab_item['th_adv_tabs_desc'] .'</span>
								</span>
							</a></li>';
						}
					?>
				</ul>
				<div class="tophive-advanced-tab-content">
					<?php
						foreach ($tabs_items as $tab_item){
							?>
								<div id="panel<?php echo $tab_item['_id'] ?>" class="tophive-advanced-tab-content-panel">
									<?php
										echo '<img src="' . $tab_item['th_adv_tabs_content_image']['url'] . '">';
								   	?>
								</div>
							<?php
						}
					?>
				</div>
			</div>
			<?php
		}
		
	}

?>