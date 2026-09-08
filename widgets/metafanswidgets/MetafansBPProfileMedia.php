<?php

class MetafansBPProfileMedia extends WP_Widget
{

  public function __construct()
  {
    $widget_options = array(
      'classname' => 'tophive-mf-profile-media-widget',
      'description' => esc_html__('BuddyPress - Profile Media', 'WP_MF_CORE_SLUG')
    );
    parent::__construct('buddypress_profile_media', 'Buddypress profile media [metafans]', $widget_options);
  }
  public function widget($args, $instance)
  {
    // if ( bp_is_profile_component() ) {
    $html = $args['before_widget'];
    $html .= '<div>';
    $html .= '<h2 class="widget-title">' . $instance['title'] . '</h2>';

    $user_id = absint( bp_displayed_user_id() );
    $all_images = [];
    $media_html = '';
    $activities = function_exists( 'bp_activity_get' )
      ? bp_activity_get( array( 'user_id' => $user_id, 'action' => 'activity_update', 'per_page' => 50, 'show_hidden' => false ) )
      : array();
    $activities = isset( $activities['activities'] ) && is_array( $activities['activities'] ) ? $activities['activities'] : array();

    if (!empty($activities)) {
      foreach ($activities as $activity) {
        $activity_id = isset( $activity->id ) ? absint( $activity->id ) : 0;
        $can_view = $activity_id && class_exists( 'MetaFans_Security' )
          ? MetaFans_Security::can_view_activity( $activity_id )
          : ( $activity_id && ( ! function_exists( 'bp_activity_user_can_read' ) || bp_activity_user_can_read( $activity, get_current_user_id() ) ) );
        if ( ! $can_view ) {
          continue;
        }
        $images = bp_activity_get_meta($activity_id, 'activity_media', false);
        if (!empty($images[0])) {
          $newImages = $images[0];
          foreach ($newImages as &$image) {
            $image['activity_id'] = $activity_id;
          }
          array_push($all_images, ...$newImages);
        }
      }
      array_filter($all_images);
      $media_html .= '<div class="ec-row bp-image-previewer mesonry bp-member-photo-widget">';
      $i = 1;
      $upper_bound = 9;

      foreach ($all_images as $url) {
        if ($i > $upper_bound) {
          break;
        }

        if (!empty($url['thumb']) && isset($url['url']) && isset($url['id']) && isset($url['activity_id'])) {
          $media_html .= "<img class='media-popup-thumbnail bp-image-single' src='{$url['url']} ' alt='gm' href={$url['url']} data-id={$url['id']} data-activity={$url['activity_id']}>";
          $i++;
        }
      }
      if (empty($all_images)) {
        $media_html .= '<span class="no-photos">' . esc_html__('No photos', 'WP_MF_CORE_SLUG') . '</span>';
      }
      $media_html .= '</div>';

      if ($i > $upper_bound) {
        $template_file = 'page-images.php';

        $page = get_pages(['meta_key' => '_wp_page_template', 'meta_value' => $template_file, 'number' => 1,]);

        $permalink = "#";
        if (! empty($page)) {
          $permalink = get_permalink($page[0]->ID);
        }

        $media_html .= "<a href='{$permalink}' class='view-more-media'>" . esc_html__("View All", "metafans")  . "</a>";
      }
    } else {
      $media_html .= '<span class="no-photos">' . esc_html__('No photos', 'WP_MF_CORE_SLUG') . '</span>';
    }
    $html .= $media_html;
    $html .= '</div>';
    $html .= $args['after_widget'];
    // }
    echo $html;
  }

  public function form($instance)
  {
    $title = ! empty($instance['title']) ? $instance['title'] : '';
?>
    <div class="mchimp-subs_form">
      <p>
        <label
          for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
        <input
          id="<?php echo $this->get_field_id('title'); ?>"
          type="text"
          name="<?php echo $this->get_field_name('title'); ?>"
          value="<?php echo esc_attr($title); ?>" />
      </p>
    </div>
<?php
  }

  public function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    return $instance;
  }
}
