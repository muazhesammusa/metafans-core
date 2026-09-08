<?php 
namespace METAFANSCORE\widgets\elementor;

if ( ! class_exists( 'BuddyPress' ) ) {
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
class MetafansElementorBuddyPressGroups extends \Elementor\Widget_base
{
	public function get_title(){
		return esc_html__( 'BuddyPress Groups', WP_MF_CORE_SLUG );
	}
	public function get_name(){
		return 'th-buddypress-groups';
	}
	public function get_icon(){
		return 'eicon-time-line';
	}
	public function get_categories(){
        return [ WP_MF_CORE_SLUG ];
    }
	public function get_keywords() {
		return [ 'group' ];
	}
	protected function register_controls(){
		$this->start_controls_section(
			'th_adv_tabs_section',
			[
				'label' => esc_html__( 'Groups', WP_MF_CORE_SLUG ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);			

		$this->end_controls_section();

	}
	protected function render(){

		?>
			<div id="buddypress" class="buddypress buddypress-wrap">
		<?php

		if ( bp_has_groups( bp_ajax_querystring( 'groups' ) ) ) : ?>

			<?php bp_nouveau_pagination( 'top' ); ?>

			<ul id="groups-list" class="item-list groups-list bp-list">

			<?php
			while ( bp_groups() ) :
				bp_the_group();
			?>

				<li <?php bp_group_class( array( 'item-entry' ) ); ?> data-bp-item-id="<?php bp_group_id(); ?>" data-bp-item-component="groups">
					<div class="list-wrap">
						<?php
					        $group_cover_image_url = bp_attachments_get_attachment('url', array(
					          'object_dir' => 'groups',
					          'item_id' => bp_get_group_id(),
					          'type'    => 'cover-image',
					        ));
					    ?>
					    <div class="item-media-wrap">
						    <div class="item-cover-img">
							<img src="<?php echo esc_url( $group_cover_image_url ); ?>" alt="bp-gourp-cover">
						    </div>
							<?php if ( ! bp_disable_group_avatar_uploads() ) : ?>
								<div class="item-avatar">
									<a href="<?php bp_group_permalink(); ?>"><?php bp_group_avatar( bp_nouveau_avatar_args() ); ?></a>
								</div>
							<?php endif; ?>
						</div>
						<div class="item">

							<div class="item-block">

								<h2 class="list-title groups-title"><?php bp_group_link(); ?></h2>

								<?php if ( bp_nouveau_group_has_meta() ) : ?>

									<p class="item-meta group-details"><?php bp_nouveau_group_meta(); ?></p>

								<?php endif; ?>
								<div class="group-loops-footer">
									<div class="group-memebers-dp">
										<?php
											$args = array( 
											    'group_id' => bp_get_group_id(),
											    'exclude_admins_mods' => false
											);

											$group_members_result = groups_get_group_members( $args );
											$group_members = array();

											foreach(  $group_members_result['members'] as $member ) {
												$group_members[] = $member->ID;
											}
											$i = 0;
											if( !empty($group_members) ){
												foreach ($group_members as $value) {
													if( $i <= 3 ){
														echo get_avatar( $value, 25 );
													}
													$i++;
												}
												$total_members = count($group_members);
												$remaining = $total_members > 4 ? (int)$total_members - 4 : '';
												echo $total_members > 4 ? '<span class="remaining">+' . $remaining . '</span>' : '';
											}
										?>
									</div>
									<?php bp_nouveau_groups_loop_buttons(); ?>
								</div>

							</div>

							<div class="group-desc"><p><?php bp_nouveau_group_description_excerpt(); ?></p></div>

							<?php bp_nouveau_groups_loop_item(); ?>

						</div>


					</div>
				</li>

			<?php endwhile; ?>

			</ul>

			<?php bp_nouveau_pagination( 'bottom' ); ?>

		<?php else : ?>

			<?php bp_nouveau_user_feedback( 'groups-loop-none' ); ?>

		<?php endif; ?>
		</div>
		<?php

		}
	}
?>