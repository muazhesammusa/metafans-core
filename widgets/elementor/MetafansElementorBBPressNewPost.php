<?php 
namespace METAFANSCORE\widgets\elementor;

if ( ! class_exists( 'bbPress' ) ) {
	return;
}
/**
 **
 * Metafans elementor Forum tabs
 * Since Version 1.0.0
 * @package wordpress
 * @subpackage MasterClass
 *
 *
 */
class MetafansElementorBBPressNewPost extends \Elementor\Widget_base
{
	public function get_title(){
		return esc_html__( 'BBPress Post Topic', WP_MF_CORE_SLUG );
	}
	public function get_name(){
		return 'th-bbpress-topic-post';
	}
	public function get_icon(){
		return 'eicon-time-line';
	}
	public function get_categories(){
        return [ WP_MF_CORE_SLUG ];
    }
	public function get_keywords() {
		return [ 'topic' ];
	}
	public function get_script_depends() {
		return [ 'rich-text-quill' ];
	}
	public function get_style_depends() {
		return [ 'rich-text-quill-css' ];
	}
	protected function register_controls(){
		$this->start_controls_section(
			'th_new_post_section',
			[
				'label' => esc_html__( 'New Post Form', WP_MF_CORE_SLUG ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);			
		$this->add_control(
			'bbp_new_post_title',
			[
				'label' => esc_html__( 'Title', WP_MF_CORE_SLUG ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Write New Post', WP_MF_CORE_SLUG ),
				'placeholder' => esc_html__( 'Type your title here', WP_MF_CORE_SLUG ),
			]
		);
		$this->add_control(
			'bbp_new_post_text',
			[
				'label' => __( 'Description', WP_MF_CORE_SLUG ),
				'type' => \Elementor\Controls_Manager::WYSIWYG,
				'rows' => 10,
				'placeholder' => __( 'Type your description here', WP_MF_CORE_SLUG ),
			]
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'th_new_post_section_style',
			[
				'label' => esc_html__( 'Styles', WP_MF_CORE_SLUG ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);			
		$this->add_control(
			'bbp_new_post_title_font_size',
			[
				'label' => esc_html__( 'Font Size', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 50,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .form-title' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);			
		$this->add_control(
			'bbp_new_post_desc_font_size',
			[
				'label' => esc_html__( 'Description Font Size', 'plugin-domain' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 50,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .form-description' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->end_controls_section();

	}
	protected function render(){
		$settings = $this->get_settings_for_display();

		$forum_args = array(
			'post_type' => 'forum',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'no_found_rows'   => true,
		);
		$forum = new \WP_Query($forum_args);
		?>
			<div class="tophive-bbpress-new-post-form">
				<h2 class="form-title"><?php echo $settings['bbp_new_post_title']; ?></h2>
				<div class="form-description"><?php echo $settings['bbp_new_post_text']; ?></div>
				<form>
					<div class="form-group">
						<label for="thbbpressposttitle"><?php esc_html_e( 'Title', WP_MF_CORE_SLUG ); ?></label>
						<input type="text" id="thbbpressposttitle" name="thbbpressposttitle" placeholder="<?php esc_html_e( 'Provide a short and descriptive title', WP_MF_CORE_SLUG ); ?>">
					</div>
					<div class="form-group">
						<label for="thbbpresspostdesc"><?php esc_html_e( 'Description', WP_MF_CORE_SLUG ); ?></label>
						<div id="thbbpresspostdesc"></div>
					</div>
					<div class="form-group">
						<label for="thbbpressposttopics"><?php esc_html_e( 'Forum (Important)', WP_MF_CORE_SLUG ); ?></label>
						<select id="thbbpressposttopics" name="thbbpressposttopics" required>
							<?php
								if($forum->have_posts()) {
									while ($forum->have_posts()) {
										$forum->the_post();
										echo '<option value="'. get_the_ID() .'">'. get_the_title() .'</option>';
									}	    
								}	
							?>
						</select>
						<?php wp_reset_postdata(); ?>
					</div>
					<div class="form-group">
						<label for="thbbpressposttags"><?php esc_html_e( 'Tags (optional)', WP_MF_CORE_SLUG ); ?></label>
						<input type="text" id="thbbpressposttags" name="thbbpressposttags" placeholder="<?php esc_html_e( 'Input tags seperated by commas', WP_MF_CORE_SLUG ); ?>">
					</div>
					<div class="form-group">
						<?php if( is_user_logged_in() ){ ?>
							<input type="submit" class="thbbpresspostsubmit" name="thbbpresspostsubmit" value="<?php echo esc_html_e( 'Submit', WP_MF_CORE_SLUG ); ?>">
						<?php }else{ ?>
							<input type="button" class="show-signin-form-modal" value="<?php echo esc_html_e( 'Submit', WP_MF_CORE_SLUG ); ?>">
						<?php } ?>
					</div>

				</form>
				<div class="response-text ec-mt-3 strong"></div>
			</div>
		<?php

	}
}
?>