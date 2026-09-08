<?php

class MetafansBPGroupMembers extends WP_Widget {

    public function __construct() {
        $widget_options = array(
            'classname' => 'tophive-mf-groups-members-widget',
            'description' => esc_html__( 'BuddyPress - Metafans Groups members lists', 'WP_MF_CORE_SLUG' )
        );
        parent::__construct('buddypress_groups_members', 'BuddyPress Group Members', $widget_options);
    }

    public function widget( $args, $instance ) {
        $html = '';
        if ( bp_is_groups_component() && bp_is_single_item() ) {
            $html .= $args['before_widget'];
            $html .= '<h2 class="widget-title">' . esc_html( $instance['title'] ?? '' ) . '</h2>';
            $group_id = absint( bp_get_group_id() );
            $limit = max( 1, min( 50, (int) apply_filters( 'metafans_group_members_widget_limit', 12, $group_id ) ) );
            $members = function_exists( 'groups_get_group_members' )
                ? groups_get_group_members(
                    array(
                        'group_id' => $group_id,
                        'per_page' => $limit,
                        'page'     => 1,
                        'exclude_admins_mods' => false,
                    )
                )
                : array();
            $member_rows = isset( $members['members'] ) && is_array( $members['members'] ) ? $members['members'] : array();

            if ( $member_rows ) {
                $html .= '<div class="avatar-block">';
                foreach ( $member_rows as $member ) {
                    $user_id = absint( $member->ID ?? $member->user_id ?? 0 );
                    if ( $user_id < 1 ) {
                        continue;
                    }
                    $html .= '<div class="item-avatar">';
                    $html .= '<a href="' . esc_url( bp_core_get_user_domain( $user_id ) ) . '">';
                    $html .= get_avatar( $user_id, 50 );
                    $html .= '</a>';
                    $html .= '</div>';
                }
                $html .= '</div>';
            }
            $html .= $args['after_widget'];
        }
        echo $html;
    }

    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : '';
        ?>
            <div class="mchimp-subs_form">
                <p>
                    <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Title:</label>
                    <input
                        id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
                        type="text"
                        name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
                        value="<?php echo esc_attr( $title ); ?>"
                    />
                </p>
            </div>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = sanitize_text_field( $new_instance['title'] ?? '' );
        return $instance;
    }
}
