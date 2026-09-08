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
class MetafansElementorForumTabs extends \Elementor\Widget_base
{
	public function get_title(){
		return esc_html__( 'BBPress Forum', WP_MF_CORE_SLUG );
	}
	public function get_name(){
		return 'th-forum-tabs';
	}
	public function get_icon(){
		return 'eicon-tabs';
	}
	public function get_categories(){
        return [ WP_MF_CORE_SLUG ];
    }
	public function get_keywords() {
		return [ 'tabs', 'forum' ];
	}
	protected function register_controls(){
		$this->start_controls_section(
			'th_adv_tabs_section',
			[
				'label' => esc_html__( 'Activity Tab', WP_MF_CORE_SLUG ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);			
		$this->add_control(
			'tab_show_activities',
			[
				'label' => esc_html__( 'Show Latest Activities', WP_MF_CORE_SLUG ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', WP_MF_CORE_SLUG ),
				'label_off' => __( 'Hide', WP_MF_CORE_SLUG ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);
		$this->add_control(
			'tab_show_forums',
			[
				'label' => esc_html__( 'Show Forums', WP_MF_CORE_SLUG ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Show', WP_MF_CORE_SLUG ),
				'label_off' => __( 'Hide', WP_MF_CORE_SLUG ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);
		$this->end_controls_section();

	}
	protected function render(){
		$settings = $this->get_settings_for_display();

		$topics_args = array(
			'post_type' => 'topic',
			'post_status' => 'publish',
			'posts_per_page' => 20,
			'no_found_rows'   => true,
		);

		$topics = new \WP_Query($topics_args);

		$forum_args = array(
			'post_type' => 'forum',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'no_found_rows'   => true,
			'post_parent' => 0
		);
		$forum = new \WP_Query($forum_args);
		?>
			<div class="tophive-advanced-forum-tab-container">
				<div class="tophive-forum-tabs">
					<?php if( 'yes' == $settings['tab_show_activities'] && 'yes' == $settings['tab_show_forums'] ){ ?>
						<ul>
							<li class="active"><a href="#th_topics"><?php echo esc_html__( 'Recent Activity', WP_MF_CORE_SLUG ); ?></a></li>
							<li><a href="#th_forums"><?php echo esc_html__( 'Forums', WP_MF_CORE_SLUG ); ?></a></li>
						</ul>
					<?php } ?>
				</div>
				<div class="tophive-forum-tabs-container">
					<?php if( 'yes' == $settings['tab_show_activities'] ): ?>
						<div class="tophive-forum-recent-topics-tab-container" id="th_topics">
							<?php
							if( $topics->have_posts() ){
									while( $topics->have_posts() ){
										$topics->the_post();

										if( function_exists('bbp_get_topic_forum_id') && function_exists('bbp_get_forum_title') ){
											$forum_id = bbp_get_topic_forum_id( get_the_ID() );
											$forum_title = ! empty( $forum_id ) ? bbp_get_forum_title( $forum_id ) : '';
											$forum_link = ! empty( $forum_id ) ? bbp_get_forum_permalink( $forum_id ) : '';
										}else{
											$forum_title = '';
										}

										$last_updated_by = '';
										$last_active = get_post_meta( get_the_ID(), '_bbp_last_active_time', true );
										if ( empty( $last_active ) ) {
											$reply_id = bbp_get_topic_last_reply_id( get_the_ID() );
											if ( ! empty( $reply_id ) ) {
												$last_active = get_post_field( 'post_date', $reply_id );
											} else {
												$last_active = get_post_field( 'post_date', get_the_ID() );
											}
										}
										$last_updated_by = bbp_get_author_link( array( 'post_id' => bbp_get_topic_last_reply_id( get_the_ID() ), 'size' => 20 ) );
										
										$last_active = ! empty( $last_updated_by ) ? bbp_get_time_since( bbp_convert_date( $last_active ) ) : '';
										?>
											<div class="tophive-forum-topic-loop-single recent-topics">
												<div class="tophive-forum-topic-loop-single-avatar">
													<?php echo get_avatar( get_the_author_meta( 'ID' ), 40 ); ?>
												</div>
												<div class="tophive-forum-topic-loop-single-details">
													<div class="tophive-forum-topic-loop-single-meta">
														<span><a href=""><?php echo get_the_author_meta('display_name'); ?></a></span>
														 • <span>Posted in : <a href="<?php echo $forum_link; ?>"><?php echo $forum_title ?></a></span>
													</div>
													<h6><a href="<?php echo bbp_get_topic_permalink(get_the_ID()); ?>"><?php the_title() ?></a></h6>
													<p><?php echo get_the_excerpt() ?></p>

													<div class="tophive-forum-topic-loop-single-footer-meta">
														<span class="replies"><svg width="0.9em" height="0.9em" viewBox="0 0 16 16" class="bi bi-chat-right-dots" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
														  <path fill-rule="evenodd" d="M2 1h12a1 1 0 0 1 1 1v11.586l-2-2A2 2 0 0 0 11.586 11H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zm12-1a2 2 0 0 1 2 2v12.793a.5.5 0 0 1-.854.353l-2.853-2.853a1 1 0 0 0-.707-.293H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h12z"/>
														  <path d="M5 6a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
														</svg> <?php echo bbp_topic_reply_count(get_the_ID()) . esc_html__( ' Replies', WP_MF_CORE_SLUG ); ?></span>
														<span class="last-active-time">
															<?php echo $last_updated_by . $last_active; ?>
														</span>
													</div>
												</div>
											</div>
										<?php
									}
									wp_reset_postdata();
								}
							?>
						</div>
					<?php endif; ?>
					<?php if( 'yes' == $settings['tab_show_forums'] ): ?>
						<div class="tophive-forum-recent-topics-tab-container" id="th_forums">
							<?php
							if( $forum->have_posts() ){
									while( $forum->have_posts() ){
										$forum->the_post();

										$last_active_id = bbp_get_forum_last_active_id(get_the_ID());
										$main_title = get_the_title(); 
										$main_desc = get_the_content(); 
										?>

												<?php
													$forum_childs_args = array(
														'post_type' 	 => 'forum',
														'post_status' 	 => 'publish',
														'post_parent'    => get_the_ID(),
														'posts_per_page' => -1,
														'no_found_rows'   => true,
													);
													$forum_childs = new \WP_Query( $forum_childs_args );

													if( $forum_childs->have_posts() ){
														echo '<h6 class="th-forum-main-heading">' . $main_title . '</h6>';
														echo '<p>' . $main_desc . '</p>';
														while ( $forum_childs->have_posts() ) {
														    $forum_childs->the_post();
														    ?>
															<div class="tophive-forum-topic-loop-single">
														    	<div class="tophive-forum-topic-loop-single-details tophive-forums">
																	<h4>
																		<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="single-icon" viewBox="0 0 16 16">
																		  <path fill-rule="evenodd" d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4.414a1 1 0 0 0-.707.293L.854 15.146A.5.5 0 0 1 0 14.793V2zm3.5 1a.5.5 0 0 0 0 1h9a.5.5 0 0 0 0-1h-9zm0 2.5a.5.5 0 0 0 0 1h9a.5.5 0 0 0 0-1h-9zm0 2.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5z"/>
																		</svg>
																		<a class="theme-secondary-color" href="<?php echo bbp_get_forum_permalink(get_the_ID()); ?>">
																				<?php 
																					the_title();
																					echo '<span>' . get_the_content() . '</span>';
																				?>
																		</a>

																	</h4>
																</div>

																<div class="tophive-forum-topic-loop-single-footer-meta">
																	<div class="meta-item">
																		<?php 
																			echo '<span>';
																			echo function_exists( 'bbp_get_forum_topic_count' ) ? absint( bbp_get_forum_topic_count( get_the_ID() ) ) : 0;
																			echo '</span>';
																			esc_html_e( ' Topics', WP_MF_CORE_SLUG ); ?>
																	</div>
																	<div class="meta-item last-active-time">
																		<?php 
																			echo '<span>';
																			echo bbp_forum_reply_count(get_the_ID());
																			echo '</span>';
																			esc_html_e( ' Replies', WP_MF_CORE_SLUG ); ?>
																	</div>
																</div>
																<div class="tophive-forum-last-topic">
																	<?php if( bbp_get_forum_topic_count(get_the_ID()) > 0 ) : ?>
																		<span class="bbp-topic-freshness-author">
																			<?php 
																				bbp_author_link( array( 'post_id' => bbp_get_forum_last_active_id( get_the_ID() ), 'size' => 45 ) );
																			?>		
																		</span>
																		<span class="bbp-topic-freshness-details">
																			<span class="bbp-last-topic-title">
																				<?php echo '<a href="'. bbp_get_forum_last_topic_permalink( get_the_ID() ) .'">'. bbp_get_forum_last_topic_title( get_the_ID() ) .'</a>' ?>
																			</span>
																			<span class="last-active-time">
																				<?php echo bbp_get_forum_last_active_time( get_the_ID(), false ); ?> •
																				<?php
																					echo bbp_author_link( array( 'post_id' => bbp_get_forum_last_active_id( get_the_ID() ), 'type' => 'name' ) );
																				?>
																			</span>
																		</span>
																	<?php endif; ?>
																</div>
															</div>
														    <?php
														}
													}else{
														?>
															<div class="tophive-forum-topic-loop-single">
																<div class="tophive-forum-topic-loop-single-details tophive-forums">
																	<h4>
																		<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="single-icon" viewBox="0 0 16 16">
																		  <path fill-rule="evenodd" d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4.414a1 1 0 0 0-.707.293L.854 15.146A.5.5 0 0 1 0 14.793V2zm3.5 1a.5.5 0 0 0 0 1h9a.5.5 0 0 0 0-1h-9zm0 2.5a.5.5 0 0 0 0 1h9a.5.5 0 0 0 0-1h-9zm0 2.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5z"/>
																		</svg>
																		<a href="<?php echo bbp_get_forum_permalink(get_the_ID()); ?>">
																				<?php 
																					the_title();
																					the_content();
																				?>
																		</a>

																	</h4>
																</div>

																<div class="tophive-forum-topic-loop-single-footer-meta">
																	<div class="meta-item">
																		<?php 
																			echo '<span>';
																			echo function_exists( 'bbp_get_forum_topic_count' ) ? absint( bbp_get_forum_topic_count( get_the_ID() ) ) : 0;
																			echo '</span>';
																			esc_html_e( ' Topics', WP_MF_CORE_SLUG ); ?>
																	</div>
																	<div class="meta-item last-active-time">
																		<?php 
																			echo '<span>';
																			echo bbp_forum_reply_count(get_the_ID());
																			echo '</span>';
																			esc_html_e( ' Replies', WP_MF_CORE_SLUG ); ?>
																	</div>
																</div>
																<div class="tophive-forum-last-topic">
																	<span class="bbp-topic-freshness-author">
																		<?php 
																			bbp_author_link( array( 'post_id' => bbp_get_forum_last_active_id( get_the_ID() ), 'size' => 45 ) );
																		?>		
																	</span>
																	<span class="bbp-topic-freshness-details">
																		<span class="bbp-last-topic-title">
																			<?php echo bbp_get_forum_last_topic_title( get_the_ID() ); ?>
																		</span>
																		<span class="last-active-time">
																			<?php echo bbp_get_forum_last_active_time( get_the_ID(), false ); ?> • 
																			<?php
																				echo bbp_author_link( array( 'post_id' => bbp_get_forum_last_active_id( get_the_ID() ), 'type' => 'name' ) );
																			?>
																		</span>
																	</span>
																</div>
															</div>
														<?php
													}
												?>
										<?php
									}
									wp_reset_postdata();
								}
							?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php
	}
		
	}

?>