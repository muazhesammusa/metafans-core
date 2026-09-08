<?php

add_action( 'add_meta_boxes', 'category_meta_box' );
add_action( 'add_meta_boxes', 'th_main_add_meta_box' );
/**
 * Adds the meta box container.
 *
 * @param string $post_type Post Type.
 */
function th_main_add_meta_box( $post_type ) {
    $tophive_metabox = new Metafans_MetaBox();
    // Limit meta box to certain post types.
    $post_types = $tophive_metabox->get_support_post_types();
    if ( in_array( $post_type, $post_types ) ) {
        add_meta_box(
            'tophive_page_settings',
            __( 'Metafans Settings', 'tophive' ),
            array( Metafans_MetaBox::get_instance(), 'render_meta_box_content' ),
            $post_type,
            'normal',
            'high'
        );
    }
}
function category_meta_box( $post_type ) {
    // Limit meta box to certain post types.
    $post_types = array( 'lp_course' );

    if ( in_array( $post_type, $post_types ) ) {
        add_meta_box(
            'th_lp_course_level',
            esc_html__( 'Level and tags', 'tophive' ),
            'tophive_learnpress_metabox_sidebar_levels',
            $post_type,
            'side',
            'high'
        );
        add_meta_box(
            'th_lp_course_featured_video',
            esc_html__( 'Featured Video', 'tophive' ),
            'tophive_learnpress_metabox_sidebar_featured_video',
            $post_type,
            'side'
        );
        add_meta_box(
            'th_lp_course_learning_points',
            esc_html__( 'What Students Will Learn?', 'tophive' ),
            'render_meta_box_learning_points',
            $post_type,
            'normal',
            'high'
        );
    }
}
function render_meta_box_learning_points( $post ){
    $th_lp_course_learning_points_group = get_post_meta($post->ID, 'customdata_group', true);
    wp_nonce_field( 'tophive_learnpress_objective_nonce', 'tophive_learnpress_objective_nonce' );
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function( $ ){
        $( '#add-row' ).on('click', function() {
            var row = $( '.empty-row.screen-reader-text' ).clone(true);
            row.removeClass( 'empty-row screen-reader-text' );
            row.insertBefore( '#repeatable-fieldset-one tbody>tr:last' );
            return false;
        });

        $( '.remove-row' ).on('click', function() {
            $(this).parents('tr').remove();
            return false;
        });
    });
  </script>
  <table id="repeatable-fieldset-one" width="100%">
  <tbody>
    <?php
     if ( $th_lp_course_learning_points_group ) :
      foreach ( $th_lp_course_learning_points_group as $field ) {
    ?>
    <tr>
      <td>
        <input type="text"  placeholder="Input learning points" name="TitleItem[]" value="<?php if($field['TitleItem'] != '') echo esc_attr( $field['TitleItem'] ); ?>" /></td>
      <td><a class="button remove-row" href="#1">Remove</a></td>
    </tr>
    <?php
    }
    else :
    // show a blank one
    ?>
    <tr>
      <td> 
        <input type="text" placeholder="Input learning points" title="Title" name="TitleItem[]" /></td>
      <td><a class="button  cmb-remove-row-button button-disabled" href="#">Remove</a></td>
    </tr>
    <?php endif; ?>

    <!-- empty hidden one for jQuery -->
    <tr class="empty-row screen-reader-text">
      <td>
        <input type="text" placeholder="Input learning points" name="TitleItem[]"/></td>
      <td><a class="button remove-row" href="#">Remove</a></td>
    </tr>
  </tbody>
</table>
<p><a id="add-row" class="button" href="#">Add new</a></p>
 <?php
}
function tophive_learnpress_metabox_sidebar_levels( $post ) {
 
    wp_nonce_field( 'tophive_learnpress_save_levels_tags_nonce', 'tophive_learnpress_save_levels_tags_nonce' );

    $value = get_post_meta( $post->ID, 'th_wp_student_level_meta_key', true );
    $tag = get_post_meta( $post->ID, 'th_wp_course_tag_meta_key', true );

    ?>
    <div class="th-sidebar-meta-block">
        <label for="th-student-level">
            <?php esc_html_e( 'Select a level for this course', 'tophive' ); ?>
        </label>
        <select id="th-student-level" class="th-select" name="th-student-level">
            <option value="0" <?php echo esc_attr($value) == 0 ? 'selected' : '' ?>><?php esc_html_e( 'All Level', 'tophive' ); ?></option>
            <option value="1" <?php echo esc_attr($value) == 1 ? 'selected' : '' ?>><?php esc_html_e( 'Begineer', 'tophive' ); ?></option>
            <option value="2" <?php echo esc_attr($value) == 2 ? 'selected' : '' ?>><?php esc_html_e( 'Intermediate', 'tophive' ); ?></option>
            <option value="3" <?php echo esc_attr($value) == 3 ? 'selected' : '' ?>><?php esc_html_e( 'Advanced', 'tophive' ); ?></option>
        </select>
    </div>
    <hr />
    <div class="th-sidebar-meta-block">
        <label for="th-student-level">
            <?php esc_html_e( 'Select a level tag', 'tophive' ); ?>
        </label>
        <select id="th-course-level" class="th-select" name="th-course-level">
            <option value=""><?php esc_html_e( 'Default', 'tophive' ); ?></option>
            <option value="new" <?php echo esc_attr($tag) == 'new' ? 'selected' : '' ?>><?php esc_html_e( 'New', 'tophive' ); ?></option>
            <option value="bestseller" <?php echo esc_attr($tag) == 'bestseller' ? 'selected' : '' ?>><?php esc_html_e( 'Best Seller', 'tophive' ); ?></option>
            <option value="popular" <?php echo esc_attr($tag) == 'popular' ? 'selected' : '' ?>><?php esc_html_e( 'Popular', 'tophive' ); ?></option>
            <option value="trending" <?php echo esc_attr($tag) == 'trending' ? 'selected' : '' ?>><?php esc_html_e( 'Treanding', 'tophive' ); ?></option>
        </select>
    </div>
    <?php
}
function tophive_learnpress_metabox_sidebar_featured_video( $post ){
    ?>
    <div class="th-sidebar-meta-block">
        <?php
            $url = get_post_meta( $post->ID, 'tophive_lp_featured_video', true );
            wp_nonce_field( 'tophive_learnpress_save_featured_video_nonce', 'tophive_learnpress_save_featured_video_nonce' )
        ?>
        <label class="th-label" for="upload_video_url"><?php esc_html_e( 'Embed Url', 'tophive' ); ?></label>
        <span class="th-description"><?php esc_html_e( 'You can embed your youtube/vimeo url here.Or you can also upload video', 'tophive' ); ?></span>
        <input class="th-input" id="upload_video_url" type="text" size="28" name="upload_video_url" placeholder="<?php esc_html_e( 'Paste embed url here', 'tophive' ); ?>" value="<?php echo esc_url( $url ); ?>" />
        <span class="th-seperator"><?php echo esc_attr( 'or' ); ?></span>
        <button class="button th-button th-btn-block button-primary custom-plugin-media-button" data-custom-plugin-media-uploader-target="#upload_video_url"><?php esc_html_e( 'Upload Video', 'tophive' ); ?></button>
    </div>
    <script type="text/javascript">
        var CustomPluginMediaUploader = {
            construct:function(){
                    // Run initButton when the media button is clicked.
                jQuery( '.custom-plugin-media-button' ).each(function( index ) {
                          CustomPluginMediaUploader.initButton(jQuery(this));
                });
            },
            initButton:function(_that){
                _that.click(function(e){
                var metaImageFrame;
                    var btn = e.target;
                    if ( !btn || !jQuery( btn ).attr( 'data-custom-plugin-media-uploader-target' ) ) return;
                    var field = jQuery( btn ).data( 'custom-plugin-media-uploader-target' );
                    e.preventDefault();
                    metaImageFrame = wp.media.frames.metaImageFrame = wp.media({
                        title: 'Upload Video',
                        button: { text:  'Use this file' },
                    });
                    metaImageFrame.on('select', function() {
                        var media_attachment = metaImageFrame.state().get('selection').first().toJSON();
                        jQuery( field ).val(media_attachment.url);

                    });
                    metaImageFrame.open();
                });
            }
        };
        CustomPluginMediaUploader.construct();
    </script>
        
    <?php
}
/**
 * Register advanced metabox fields
 */
add_action( 'tophive/metabox/init', 'tophive_pro_metabox_fields' );
/**
 * Add More Metabox Field Settings
 * @since 0.0.3
 *
 * @param $metabox
 */
function tophive_pro_metabox_fields( $metabox ){
	$metabox->field_builder->add_field( array(
		'title' => __( 'Custom Title', 'tophive-pro' ),
		'type'  => 'text',
		'tab'   => 'page_header',
		'name'  => 'page_header_title'
	) );

	$metabox->field_builder->add_field( array(
		'title' => __( 'Custom Tagline', 'tophive-pro' ),
		'type'  => 'text',
		'tab'   => 'page_header',
		'name'  => 'page_header_tagline'
	) );

	$metabox->field_builder->add_field( array(
		'title' => __( 'Custom Image', 'tophive-pro' ),
		'type'  => 'image',
		'tab'   => 'page_header',
		'name'  => 'page_header_image'
	) );

	$metabox->field_builder->add_field( array(
		'title'       => __( 'Custom Shortcode', 'tophive-pro' ),
		'type'        => 'textarea',
		'tab'         => 'page_header',
		'name'        => 'page_header_shortcode',
		'description' => __( 'If this field is set the page header cover will replace by this content. Arbitrary HTML code or Shortcode.', 'tophive-pro' )
	) );

	if ( MetafansCoreCustomizer()->is_enabled_module( 'MetafansCoreCustomizer_Module_Header_Transparent' ) ) {
		$metabox->field_builder->add_tab( 'header_transparent', array(
			'title' => __( 'Header Transparent', 'tophive-pro' ),
			'icon' => 'dashicons dashicons-admin-page'
		) );
		$metabox->field_builder->add_field( array(
			'title'       => __( 'Header Transparent', 'tophive-pro' ),
			'type'        => 'select',
			'tab'         => 'header_transparent',
			'name'        => 'header_transparent_display',
			'choices'     => array(
				'default' => __( 'Default', 'tophive-pro' ),
				'hide'    => __( 'Disable', 'tophive-pro' ),
				'show'    => __( 'Enable', 'tophive-pro' ),
			),
			'description' => ''
		) );
	}
}

/**
 * Calls the class on the post edit screen.
 * @deprecated 0.0.3 @todo Add a notice in a future version.
 */
function tophive_pro_metabox_init() {
    if ( is_admin() ) {
        $metabox = new MetafansCoreCustomizer_MetaBox();
        add_filter( 'tophive/metabox/fields', array( $metabox, 'get_fields' ) );
        add_action( 'tophive/metabox/settings', array( $metabox, 'render_meta_box_content' ) );
    }
}
tophive_pro_metabox_init();



/**
 * The Class Metabox.
 *
 * @deprecated 0.0.3 @todo Add a notice in a future version.
 */
class MetafansCoreCustomizer_MetaBox {

    public $fields = array(
        'page_header_title' => '',
        'page_header_tagline' => '',
        'header_transparent_display' => '',
        'page_header_image' => '',
        'page_header_shortcode' => '',
    ) ;

    /**
     * Hook into the appropriate actions when the class is constructed.
     */
    public function add_settings() {
        add_action( 'tophive/metabox/settings', array( $this, 'render_meta_box_content' ) );
    }

    function get_fields( $fields ){
        return array_merge( $fields, $this->fields );
    }


    /**
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_meta_box_content( $post ) {

        // Add an nonce field so we can check for it later.
        $values = $this->fields;
        foreach( $values as $key => $value ) {
            $values[ $key ] = get_post_meta( $post->ID, '_tophive_'.$key, true );
        }

        $image =  $values['page_header_image'];
        $image = wp_parse_args( $image, array(
            'url' => '',
            'id' => '',
        ) );

        $img = tophive_metafans()->get_media( $image );

        ?>
        <div class="tophive_metabox_section">
            <label for="tophive_page_header_title"><strong><?php _e( 'Custom Title', 'tophive-pro' ); ?></strong></label>
            <input type="text"  name="tophive_page_settings[page_header_title]" value="<?php echo esc_attr( $values['page_header_title'] ); ?>" id="tophive_page_header_title" class="widefat">
        </div>
        <div class="tophive_metabox_section">
            <label for="tophive_page_header_tagline"><strong><?php _e( 'Custom Tagline', 'tophive-pro' ); ?></strong></label>
            <textarea name="tophive_page_settings[page_header_tagline]" id="tophive_page_header_title" class="widefat" ><?php echo esc_textarea( $values['page_header_tagline'] ); ?></textarea>
        </div>
        <div class="tophive_metabox_section">
            <label for="tophive_page_header_tagline"><strong><?php _e( 'Custom Image', 'tophive-pro' ); ?></strong></label>
            <span class="tophive-mt-media <?php echo ( $img ) ? 'attachment-added': 'no-attachment'; ?>">
                <span class="tophive-image-preview">
                    <?php if ( $img ) {
                        echo '<img src="'.esc_url( $img ).'" alt=""/>';
                    } ?>
                </span>
                <a class="tophive--add" href="#"><?php _e( 'Set page header image', 'tophive-pro' ); ?></a>
                <a class="tophive--remove" href="#"><?php _e( 'Remove page header image', 'tophive-pro' ); ?></a>
                <input type="hidden"  name="tophive_page_settings[page_header_image][id]" value="<?php echo esc_attr( $image['id'] ); ?>" id="page_header_image_id" class="widefat attachment-id">
                <input type="hidden"  name="tophive_page_settings[page_header_image][url]" value="<?php echo esc_attr( $image['url'] ); ?>" id="page_header_image_url" class="widefat attachment-url">
            </span>
        </div>
        <div class="tophive_metabox_section">
            <label for="tophive_page_header_title"><strong><?php _e( 'Custom Page Header Shortcode', 'tophive-pro' ); ?></strong></label>
            <input type="text"  name="tophive_page_settings[page_header_shortcode]" value="<?php echo esc_attr( $values['page_header_shortcode'] ); ?>" id="tophive_page_header_shortcode" class="widefat">
            <br/>
            <span class="description"><?php _e( 'If this field is set the page header cover will replace by this content. Arbitrary HTML code or Shortcode.', 'tophive-pro' ); ?></span>
        </div>

        <div class="tophive_metabox_section">
            <label for="tophive_header_transparent_display"><strong><?php _e( 'Header Transparent Display', 'tophive-pro' ); ?></strong></label>
            <select id="tophive_header_transparent_display" name="tophive_page_settings[header_transparent_display]">
                <?php foreach( array(
                                   'default' => __( 'Default', 'tophive-pro' ),
                                   'hide' => __( 'Disable', 'tophive-pro' ),
                                   'show' => __( 'Enable', 'tophive-pro' ),
                               ) as $k => $label ) { ?>
                    <option <?php selected( $values['header_transparent_display'],  $k ); ?> value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></option>
                <?php } ?>
            </select>
        </div>

        <?php
    }
}


