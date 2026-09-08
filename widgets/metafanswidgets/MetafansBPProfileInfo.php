<?php
/**
 * BuddyPress Profile Info Widget
 *
 * @package     Tophive
 * @subpackage  Widgets
 * @author      Tophive Themes
 */
class MetafansBPProfileInfo extends WP_Widget {
 
    public function __construct()
  {
    $widget_options = array(
      'classname' => 'tophive-profile-info-widget',
      'description' => esc_html__('BuddyPress Profile', 'WP_MF_CORE_SLUG')
    );
    parent::__construct('buddypress_profile', 'Buddypress Profile', $widget_options);
  }

  public function widget($args, $instance)
  {
    $profile_id = get_current_user_id();
    if (!$profile_id) return;
    $html  = '';
    $html .= $args['before_widget'];
    $html .= '<div class="widget-profile-container">';

    // ❯❯❯❯ Profile Photo
    // Get the displayed user's cover/banner
    $cover = bp_attachments_get_attachment('url', [
      'object_dir' => 'members',
      'item_id'    => $profile_id,
    ]);

    if (! $cover) {
      $cover = get_template_directory_uri() . "/assets/images/placeholder.png";
    }

    $html .= "<div class='widget-profile-banner'>";
    $html .= "<figure><img src='{$cover}' alt='Banner' /></figure>";

    // Get user avatar HTML
    $avatar_html = bp_core_fetch_avatar([
      'item_id' => $profile_id,
      'type'    => 'full',
      'html'    => true
    ]);

    // Fallback if user has no avatar
    if (empty($avatar_html)) {
      $pla_url = get_template_directory_uri() . '/assets/images/people.webp';
      $avatar_html = "<img src='{$pla_url}' alt='User Avatar' />";
    }

    $html .= $avatar_html;
    $html .= "</div>"; // banner end ═══════

    //name
    $name = get_the_author_meta('display_name', $profile_id);
    $domain = bp_core_get_user_domain($profile_id);
    $html .= "<div class='widget-profile-name'>";
    $html .= "<a href='{$domain}'>{$name}</a>"; //profile link
    $html .= "</div>";

    //Location field in BuddyPress xProfile

    $html .= "<div class='widget-profile-location'>";
    $location = bp_get_profile_field_data([
      'field'   => 'Location',
      'user_id' => $profile_id,
    ]);
    if ($location) {
      $html .= '<p>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-geo-alt" viewBox="0 0 16 16">
                      <path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A31.493 31.493 0 0 1 8 14.58a31.481 31.481 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94zM8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10z"/>
                      <path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                    </svg> ' . $location . ' 
                </p>';
    }
    $html .= "•";
    //designation
    $designation = get_user_meta($profile_id, 'designation', true);
    $html .= "<div class='widget-profile-designation'>";
    $html .= "<p> {$designation} </p>";
    $html .= "</div>";
    $html .= "</div>"; // END location ═══════

    // ❯❯❯❯ Profile Tab

    // Get member nav items
    $base_link  = bp_core_get_user_domain($profile_id);

    // Define tabs with slug with SVG
    $tabs = [
      'Profile'  => [
        'slug' => bp_get_profile_slug() . '/',
        'svg'  => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M15.25 6C15.25 7.79493 13.7949 9.25 12 9.25V10.75C14.6234 10.75 16.75 8.62335 16.75 6H15.25ZM12 9.25C10.2051 9.25 8.75 7.79493 8.75 6H7.25C7.25 8.62335 9.37665 10.75 12 10.75V9.25ZM8.75 6C8.75 4.20507 10.2051 2.75 12 2.75V1.25C9.37665 1.25 7.25 3.37665 7.25 6H8.75ZM12 2.75C13.7949 2.75 15.25 4.20507 15.25 6H16.75C16.75 3.37665 14.6234 1.25 12 1.25V2.75ZM9 13.75H15V12.25H9V13.75ZM15 20.25H9V21.75H15V20.25ZM9 20.25C7.20507 20.25 5.75 18.7949 5.75 17H4.25C4.25 19.6234 6.37665 21.75 9 21.75V20.25ZM18.25 17C18.25 18.7949 16.7949 20.25 15 20.25V21.75C17.6234 21.75 19.75 19.6234 19.75 17H18.25ZM15 13.75C16.7949 13.75 18.25 15.2051 18.25 17H19.75C19.75 14.3766 17.6234 12.25 15 12.25V13.75ZM9 12.25C6.37665 12.25 4.25 14.3766 4.25 17H5.75C5.75 15.2051 7.20507 13.75 9 13.75V12.25Z" fill="currentColor"/>
        </svg>'
      ],
      'Messages' => [
        'slug' => 'messages/',
        'svg'  => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M11.6692 18.9506L12.1051 19.5609L11.6692 18.9506ZM11.7222 18.9128L11.2902 18.2997L11.7222 18.9128ZM14.5703 18.0001L14.5751 18.7501L14.5703 18.0001ZM7.3959 19.5103L8.09226 19.7888L7.3959 19.5103ZM9.05848 20.8154L8.62255 20.2051H8.62255L9.05848 20.8154ZM21.798 14.4069L22.5177 14.6179L21.798 14.4069ZM18.4069 17.798L18.6179 18.5177L18.4069 17.798ZM4.73005 2.54497L5.07054 3.21322L4.73005 2.54497ZM2.54497 4.73005L3.21322 5.07054L2.54497 4.73005ZM19.27 2.54497L18.9295 3.21322L19.27 2.54497ZM21.455 4.73005L20.7868 5.07054L21.455 4.73005ZM8 11.75C7.58579 11.75 7.25 12.0858 7.25 12.5C7.25 12.9142 7.58579 13.25 8 13.25V11.75ZM16 13.25C16.4142 13.25 16.75 12.9142 16.75 12.5C16.75 12.0858 16.4142 11.75 16 11.75V13.25ZM8 7.25C7.58579 7.25 7.25 7.58579 7.25 8C7.25 8.41421 7.58579 8.75 8 8.75V7.25ZM16 8.75C16.4142 8.75 16.75 8.41421 16.75 8C16.75 7.58579 16.4142 7.25 16 7.25V8.75ZM14 1.25H10V2.75H14V1.25ZM1.25 10V14.2283H2.75V10H1.25ZM22.75 11.1842V10H21.25V11.1842H22.75ZM5.77166 18.75H6.37341V17.25H5.77166V18.75ZM9.49441 21.4257L12.1051 19.5609L11.2333 18.3403L8.62255 20.2051L9.49441 21.4257ZM14.6354 18.75H15.1842V17.25H14.6354V18.75ZM12.1051 19.5609C12.1325 19.5413 12.1436 19.5334 12.1543 19.5259L11.2902 18.2997C11.2747 18.3107 11.2592 18.3218 11.2333 18.3403L12.1051 19.5609ZM14.6354 17.25C14.6036 17.25 14.5845 17.25 14.5655 17.2501L14.5751 18.7501C14.5882 18.75 14.6018 18.75 14.6354 18.75V17.25ZM12.1543 19.5259C12.8632 19.0264 13.7079 18.7556 14.5751 18.7501L14.5655 17.2501C13.3922 17.2576 12.2493 17.6239 11.2902 18.2997L12.1543 19.5259ZM6.37341 18.75C6.62191 18.75 6.79183 19.001 6.69954 19.2317L8.09226 19.7888C8.57867 18.5728 7.68311 17.25 6.37341 17.25V18.75ZM6.69954 19.2317C6.01288 20.9484 7.9899 22.5003 9.49441 21.4257L8.62255 20.2051C8.33709 20.409 7.96197 20.1145 8.09226 19.7888L6.69954 19.2317ZM21.25 11.1842C21.25 12.9261 21.2424 13.6363 21.0783 14.1958L22.5177 14.6179C22.7576 13.7996 22.75 12.8206 22.75 11.1842H21.25ZM15.1842 18.75C16.8206 18.75 17.7996 18.7576 18.6179 18.5177L18.1958 17.0783C17.6363 17.2424 16.9261 17.25 15.1842 17.25V18.75ZM21.0783 14.1958C20.671 15.5848 19.5848 16.671 18.1958 17.0783L18.6179 18.5177C20.4971 17.9667 21.9667 16.4971 22.5177 14.6179L21.0783 14.1958ZM1.25 14.2283C1.25 16.7256 3.27441 18.75 5.77166 18.75V17.25C4.10284 17.25 2.75 15.8972 2.75 14.2283H1.25ZM10 1.25C8.61224 1.25 7.52632 1.24942 6.65494 1.32061C5.77479 1.39252 5.04768 1.54138 4.38955 1.87671L5.07054 3.21322C5.48197 3.00359 5.9897 2.87996 6.77708 2.81563C7.57322 2.75058 8.58749 2.75 10 2.75V1.25ZM2.75 10C2.75 8.58749 2.75058 7.57322 2.81563 6.77708C2.87996 5.9897 3.00359 5.48197 3.21322 5.07054L1.87671 4.38955C1.54138 5.04768 1.39252 5.77479 1.32061 6.65494C1.24942 7.52632 1.25 8.61224 1.25 10H2.75ZM4.38955 1.87671C3.30762 2.42798 2.42798 3.30762 1.87671 4.38955L3.21322 5.07054C3.62068 4.27085 4.27085 3.62068 5.07054 3.21322L4.38955 1.87671ZM14 2.75C15.4125 2.75 16.4268 2.75058 17.2229 2.81563C18.0103 2.87996 18.518 3.00359 18.9295 3.21322L19.6104 1.87671C18.9523 1.54138 18.2252 1.39252 17.3451 1.32061C16.4737 1.24942 15.3878 1.25 14 1.25V2.75ZM22.75 10C22.75 8.61224 22.7506 7.52632 22.6794 6.65494C22.6075 5.77479 22.4586 5.04768 22.1233 4.38955L20.7868 5.07054C20.9964 5.48197 21.12 5.9897 21.1844 6.77708C21.2494 7.57322 21.25 8.58749 21.25 10H22.75ZM18.9295 3.21322C19.7291 3.62068 20.3793 4.27085 20.7868 5.07054L22.1233 4.38955C21.572 3.30762 20.6924 2.42798 19.6104 1.87671L18.9295 3.21322ZM8 13.25H16V11.75H8V13.25ZM8 8.75H16V7.25H8V8.75Z" fill="currentColor"/>
        </svg>'
      ],
      'Settings' => [
        'slug' => 'settings/',
        'svg'  => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M5.93188 5.38642L5.74952 6.11391L5.93188 5.38642ZM9.30644 3.43812L8.58523 3.2323L9.30644 3.43812ZM3.23837 10.0517L3.77722 9.53005L3.23837 10.0517ZM3.23837 13.9483L2.69952 13.4266H2.69952L3.23837 13.9483ZM5.93188 18.6136L6.11423 19.3411L5.93188 18.6136ZM9.30644 20.5619L8.58523 20.7677L9.30644 20.5619ZM14.6935 20.5619L15.4147 20.7677L14.6935 20.5619ZM18.068 18.6136L18.2504 17.8861L18.068 18.6136ZM20.7615 13.9483L20.2227 14.47V14.47L20.7615 13.9483ZM20.7615 10.0517L20.2227 9.53005V9.53005L20.7615 10.0517ZM18.068 5.38642L17.8857 4.65893L18.068 5.38642ZM14.6935 3.43812L15.4147 3.2323L14.6935 3.43812ZM5.74952 6.11391C7.60843 6.57987 9.50172 5.48678 10.0276 3.64394L8.58523 3.2323C8.28146 4.2967 7.18792 4.92806 6.11423 4.65893L5.74952 6.11391ZM3.77722 9.53005C2.34195 8.04752 3.74798 5.6122 5.74952 6.11391L6.11423 4.65893C2.6489 3.7903 0.214599 8.00664 2.69952 10.5734L3.77722 9.53005ZM3.77722 14.47C5.1102 13.0931 5.1102 10.9069 3.77722 9.53005L2.69952 10.5734C3.46944 11.3687 3.46944 12.6314 2.69952 13.4266L3.77722 14.47ZM5.74952 17.8861C3.74798 18.3878 2.34195 15.9525 3.77722 14.47L2.69952 13.4266C0.214598 15.9934 2.6489 20.2097 6.11423 19.3411L5.74952 17.8861ZM10.0276 20.3561C9.50172 18.5133 7.60843 17.4202 5.74952 17.8861L6.11423 19.3411C7.18792 19.072 8.28146 19.7033 8.58523 20.7677L10.0276 20.3561ZM13.9722 20.3561C13.406 22.3403 10.5939 22.3403 10.0276 20.3561L8.58523 20.7677C9.56564 24.2031 14.4342 24.2031 15.4147 20.7677L13.9722 20.3561ZM18.2504 17.8861C16.3915 17.4202 14.4982 18.5133 13.9722 20.3561L15.4147 20.7677C15.7184 19.7033 16.812 19.072 17.8857 19.3411L18.2504 17.8861ZM20.2227 14.47C21.6579 15.9525 20.2519 18.3878 18.2504 17.8861L17.8857 19.3411C21.351 20.2097 23.7853 15.9934 21.3004 13.4266L20.2227 14.47ZM20.2227 9.53005C18.8897 10.9069 18.8897 13.0931 20.2227 14.47L21.3004 13.4266C20.5305 12.6314 20.5305 11.3687 21.3004 10.5734L20.2227 9.53005ZM18.2504 6.11391C20.2519 5.6122 21.6579 8.04752 20.2227 9.53005L21.3004 10.5734C23.7853 8.00664 21.351 3.7903 17.8857 4.65893L18.2504 6.11391ZM13.9722 3.64394C14.4982 5.48678 16.3915 6.57987 18.2504 6.11391L17.8857 4.65893C16.812 4.92806 15.7184 4.2967 15.4147 3.2323L13.9722 3.64394ZM15.4147 3.2323C14.4342 -0.203084 9.56564 -0.203083 8.58523 3.2323L10.0276 3.64394C10.5939 1.6597 13.406 1.6597 13.9722 3.64394L15.4147 3.2323ZM8.24994 12C8.24994 14.0711 9.92888 15.75 11.9999 15.75V14.25C10.7573 14.25 9.74994 13.2427 9.74994 12H8.24994ZM11.9999 15.75C14.071 15.75 15.7499 14.0711 15.7499 12H14.2499C14.2499 13.2427 13.2426 14.25 11.9999 14.25V15.75ZM15.7499 12C15.7499 9.92895 14.071 8.25001 11.9999 8.25001V9.75002C13.2426 9.75002 14.2499 10.7574 14.2499 12H15.7499ZM11.9999 8.25001C9.92888 8.25001 8.24994 9.92895 8.24994 12H9.74994C9.74994 10.7574 10.7573 9.75002 11.9999 9.75002V8.25001Z" fill="currentColor"/>
        </svg>'
      ]
    ];
    
    $html .= "<div class='widget-profile-action-btns'>";
    foreach ($tabs as $label => $data) {
      $tab_link = $base_link . $data['slug'];
      $html .= "<a href='{$tab_link}' class='bp-profile-link'>{$data['svg']} {$label}</a>";
    }
    $html .= "</div>"; // END Profile Tab ═══════

    // Get user description
    $desc = get_the_author_meta('description', $profile_id, false);

    if (! empty($desc)) {  // If the description is empty, nothing is rendered, keeping your profile card clean.
      $html .= "<div class='widget-profile-about'>";
      $html .= "<p>{$desc}</p>";
      $html .= "</div>";
    }

    // ❯❯❯❯ FOLLOW/ING INFO
    $html .= '<div class="widget-profile-meta-info member_meta_info_con">';
    // total number of posts
    if (function_exists('bp_activity_get')) {
      $activities = bp_activity_get(['filter' => ['user_id' => $profile_id], 'count_total' => true, 'per_page' => 1, 'show_hidden' => false]);
      $activity_count = isset($activities['total']) ? intval($activities['total']) : 0;
      $html .= "<p> <span> {$activity_count} </span> <span> " . esc_html__('Posts', 'WP_MF_CORE_SLUG') . " </span> </p>";
    }
    //total follower
    $_follow_count = Tophive_BP_Members::get_instance()->get_following_count($profile_id);
    $html .= "<p> <span> {$_follow_count} </span> <span> " . esc_html__('Following', 'WP_MF_CORE_SLUG') . " </span> </p>";
    //total friend
    if (function_exists('friends_get_total_friend_count')) {
      $friend_count = friends_get_total_friend_count($profile_id);
      $html .= "<p> <span> {$friend_count} </span> <span> " . esc_html__('Friends', 'WP_MF_CORE_SLUG') . " </span> </p>";
    }
    $html .= '</div>'; // END FOLLOW/ING INFO

    /*
    //action btn
    $current_user = get_current_user_id();
    if (Tophive_BP_Members::get_instance()->is_already_following($current_user, $profile_id)) {
      $status = 'following';
      $text   = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16"><path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/></svg>' . esc_html__(' Following', 'WP_MF_CORE_SLUG');
    } else {
      $status = '';
      $text = esc_html__('+ Follow', 'WP_MF_CORE_SLUG');
    }
    $follow_btn = "<a href='' class='bp-th-follow-button' data-follower-id='{$profile_id}' data-following='{$status}' data-following-id='{$current_user}'> {$text} </a>";

    $html .= "<div class='widget-profile-action-btn'>";
    $html .= $follow_btn;
    $html .= '<a href="#" class="private-msg" data-recipients-id="<?php echo absint( $user_id ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chat-dots" viewBox="0 0 16 16">
                  <path d="M5 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"></path>
                  <path d="m2.165 15.803.02-.004c1.83-.363 2.948-.842 3.468-1.105A9.06 9.06 0 0 0 8 15c4.418 0 8-3.134 8-7s-3.582-7-8-7-8 3.134-8 7c0 1.76.743 3.37 1.97 4.6a10.437 10.437 0 0 1-.524 2.318l-.003.011a10.722 10.722 0 0 1-.244.637c-.079.186.074.394.273.362a21.673 21.673 0 0 0 .693-.125zm.8-3.108a1 1 0 0 0-.287-.801C1.618 10.83 1 9.468 1 8c0-3.192 3.004-6 7-6s7 2.808 7 6c0 3.193-3.004 6-7 6a8.06 8.06 0 0 1-2.088-.272 1 1 0 0 0-.711.074c-.387.196-1.24.57-2.634.893a10.97 10.97 0 0 0 .398-2z"></path>
                </svg>
                Message
						  </a>';
    $html .= "</div>";
    */

    // ❯❯❯❯ Forum Stats Info (If bbPress is active)
    $html .= '<div class="ec-mt-sm-3 forum_meta_info_con">';

    // total forum topics
    if (function_exists('bbp_get_user_topic_count_raw')) {
      $topic_count = bbp_get_user_topic_count_raw($profile_id);
      $html .= "<p> <span> {$topic_count} </span> <span> " . esc_html__('Topics Created', 'WP_MF_CORE_SLUG') . " </span> </p>";
    }

    // total forum replies
    if (function_exists('bbp_get_user_reply_count_raw')) {
      $reply_count = bbp_get_user_reply_count_raw($profile_id);
      $html .= "<p> <span> {$reply_count} </span> <span> " . esc_html__('Replies', 'WP_MF_CORE_SLUG') . " </span> </p>";
    }

    $html .= '</div>'; // END Forum Stats Info

    //end main container div
    $html .= '</div>';
    $html .= $args['after_widget'];
    echo $html;
  }

  public function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['profile_id'] = !empty($new_instance['profile_id']) ? strip_tags($new_instance['profile_id']) : '';
    return $instance;
  }
 
}