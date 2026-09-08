<?php 

namespace METAFANSCORE\widgets\elementor;

class MetafansElementorBase
{
	private static $instance = null;

	
	public static function getInstance(){
		if(empty(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}
	public static function randColors(){
		$colors = array(
			array(
				'rgba(90, 90, 204, 0.2)',
				'rgba(90, 90, 204, 1)'
			), 
			array(
				'rgba(140, 103, 198, 0.2)',
				'rgba(140, 103, 198, 1)',
			), 
			array(
				'rgba(216, 99, 216, 0.2)',
				'rgba(216, 99, 216, 1)',
			), 
			array(
				'rgba(108, 40, 161, 0.2)',
				'rgba(108, 40, 161, 1)',
			), 
			array(
				'rgba(248, 129, 45, 0.2)',
				'rgba(248, 129, 45, 1)',
			), 
			array(
				'rgba(150, 229, 16, 0.2)',
				'rgba(150, 229, 16, 1)',
			), 
			array(
				'rgba(36, 85, 206, 0.2)',
				'rgba(36, 85, 206, 1)',
			), 
			array(
				'rgba(248, 51, 78, 0.2)',
				'rgba(248, 51, 78, 1)',
			), 
			array(
				'rgba(244, 180, 93, 0.2)',
				'rgba(244, 180, 93, 1)',
			), 
			array(
				'rgba(242, 176, 164, 0.2)',
				'rgba(242, 176, 164, 1)',
			),
		);
		return $colors[array_rand($colors)];
	}
	public static function getCourseLayout(){}
	public static function getCourseThumb( $thumb, &$id = '' ){
		return 'yes' === $thumb ? '<div class="th-course-thumb ec-mb-4 ec-mb-md-0">'. get_the_post_thumbnail($id , 'large' ) .'</div>' : '';
	}
	
	public static function getCourseTitle(){
		return '<a href="'. get_the_permalink() .'"><h5 class="course-block-title">' . get_the_title() . '</h5></a>';
	}
	public static function getCourseDetails( $s, $count ){
		return $s ? '<div class="th-description">' . wp_trim_words( get_the_content(), $count ) . '</div>' : '';
	}
	public static function getCourseCategory( $show, $showbg, $id ){
		$color = self::randColors();
		$bg = $showbg ? $color[0] : 'transparent';
		$textcolor = $color[1];

    	$terms = get_the_terms( $id , 'course_category' );
		if ( $show && ! empty( $terms ) && is_array( $terms ) ) {
			return '<span class="th-course-category"><p class="course-category ec-d-inline-block" style="background:'. $bg .';color:'. $textcolor .'">'.$terms[0]->name.'</p></span>';
		}
		return '';
	}
	public static function getCoursePrice( $showPrice, $price ){
        return $showPrice ? '<div class="price-section"><p class="th-sale-price">' . $price . '</p></div>' : '';        
        // Original price
        // if('yes' === $settings['show_pricing_prev']){
        // 	if ( $course->has_sale_price() ) {
        //         $html .= '<span class="th-original-price"><del>'. $course->get_origin_price_html() .'</del></span>';
        //     }
        // }
	}
	public static function getWishListStatus(){
		global $current_user;
		return get_user_meta($current_user->ID, '_lpr_wish_list', true);
	}
	public static function getHoverInfo( $settings ){

		$added_to_wishlist = self::getWishListStatus();
		$html = '';

		if($settings['show_hover_info']){
			$html .= '<div class="hover-section ec-d-none ec-d-md-block">';
		    	$course = learn_press_get_course( get_the_ID() );
		    	$course_meta = apply_filters( 'tophive/learnpress/course-meta', get_the_ID() );
	    		$learning_points = get_post_meta( get_the_ID(), 'customdata_group', true );
	    		$learning_points = array_splice($learning_points, 0, 3);
		    	$desc_excerpt = isset($settings['hi_desc_excerpt']);

		    	if( 'yes' === $settings['show_hi_title'] ){
	    			$html .= '<div class="hover-info-title mb-3"><a href="'. get_the_permalink() .'">' . get_the_title() . '</a></div>';
		    	}
    			$html .= '<p class="hover-info-date ec-text-success ec-mb-1">' . $course_meta['updated']['html'] . '</p>';
		    	$html .= '<div class="hover-info-lessons-duration">';
		    		$html .= 'yes' == $settings['show_hi_level'] ? '<span class="hover-info-level">'. $course_meta['level']['html_icon'] .'</span>' : '';
		    		$html .= 'yes' == $settings['show_hi_lessons'] ? '<span class="hover-info-lessons">'. $course_meta['lessons']['html'] .'</span>' : '';
	    			$html .= 'yes' == $settings['show_hi_course_duration'] ? '<span class="hover-info-duration">'. $course_meta['duration']['html'] .'</span>' : '';
		    	$html .= '</div>';

	    		$html .= $settings['show_hi_course_details'] ? '<div class="hover-info-desc">' . wp_trim_words( get_the_excerpt(), $settings['hi_desc_excerpt'] ) . '</div>' : '';
	    		if( $settings['show_learning_points'] == 'yes' ){
		    		foreach ($learning_points as $point) {
		    			$html .= '<p class="learning-points"><svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-check2" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
							  <path fill-rule="evenodd" d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
							</svg> '. $point['TitleItem'] .'</p>';
		    		}
	    		}
		    	$html .= '<div class="th-display-flex">';
		    		if( $settings['show_hi_add_to_cart'] == 'yes' ){	
				    	$html .= '<form method="post" enctype="multipart/form-data">
							' . do_action( 'learn-press/before-purchase-button' ) . '
					        <input type="hidden" name="purchase-course" value="'. esc_attr( $course->get_id() ) .'"/>
					        <input type="hidden" name="purchase-course-nonce"
					               value="'. esc_attr( \LP_Nonce_Helper::create_course( 'purchase' ) ) .'"/>
					        <input type="submit" class="hover-info-add-cart" value="'. esc_html( apply_filters( 'learn-press/purchase-course-button-text', esc_html__( 'Purchase', 'learnpress' ) ) ) .'"
					        />
							'.do_action( 'learn-press/after-purchase-button' ).'
					    </form>';
		    		}

			    	if ( $settings['show_hi_wishlist'] ) {
			    		if(!empty($added_to_wishlist))
			    			{
			    				$wishlist = in_array(get_the_ID(), $added_to_wishlist) ? 'on ' : '';
								$wishlist_text = in_array(get_the_ID(), $added_to_wishlist) ? esc_html__('Remove From  wishlist', WP_MF_CORE_SLUG) : esc_html__('Add to wishlist', WP_MF_CORE_SLUG);
							}else{
								$wishlist = '';
								$wishlist_text = esc_html__('Add to wishlist', WP_MF_CORE_SLUG);
							}
			    		$html .= '<div class="hover-info-wishlist"><a class="'. $wishlist .'hover-wishlist-'. get_the_ID() .' th-tooltip" data-id="'. get_the_ID() .'" data-nonce="'. wp_create_nonce( 'course-toggle-wishlist' ) .'" href="#"><i class="'. $settings['wishlist_icon'] .'"></i><span class="th-tooltip-text">'. $wishlist_text .'</span></a></div>';
			    	}
				$html .= '</div>';
			$html .= '</div>';
		}
		return $html;
	}
	
	public static function getCourseFooter( $author = '', $rating = '', $lessons = array( 'show' => '', 'align' => 'ec-text-left' ), $level = '', $enrolled = '' ){
		$levels = apply_filters( 'tophive/learnpress/course-meta/level', get_the_ID() );
		$html = '<div class="th-course-footer ec-align-items-center ec-row ec-no-gutters ec-px-2 ec-py-3">';
    		// course author
			$html .= $author ? '<div class="ec-col"><p class="ec-mb-0 course-author">' . self::getCourseAuthor() . '</p></div>' : '';
			$html .= 'yes' == $level['show'] ? '<div class="ec-col"><p class="ec-mb-0 course-level">' . $levels['html_icon'] . '</p></div>' : '';
			$html .= 'yes' == $lessons['show'] ? '<div class="ec-col"><p class="ec-mb-0 course-lessons '. $lessons['align'] .'">' . self::getCourseLessons() . '</p></div>' : '';

			if($rating && function_exists('learn_press_get_course_rate')){
	        	$html .= '<div class="ec-col">';
	                // Course Ratings
                    $course_rate = learn_press_get_course_rate(get_the_ID(), false );
                    $rated = isset( $course_rate['rated'] ) ? $course_rate['rated'] : 0;
                    $item = isset( $course_rate['total'] ) ? $course_rate['total'] : 0;    
                    
                    
                    if ($item > 999 && $item <= 999999) {
					    $stds = number_format((float)($item / 1000), 1, '.', '') . 'K';
					} elseif ($item > 999999) {
					    $stds = number_format((float)($item / 1000000), 1, '.', '') . 'M';
					} else {
					    $stds = $item;
					}

                    $percent = ( ! $rated ) ? 0 : min( 100, ( round( $rated * 2 ) / 2 ) * 20 );
                    $point = ( ! $rated ) ? 0 : min( 100, ( round( $rated * 2 ) / 2 ) );
                    $point = number_format((float)$point, 1, '.', '');
                    $html .= '<div class="review-stars-rated ec-float-right">
                    	<div class="review-stars empty"></div>
                    	<div class="review-stars filled" style="width:' . $percent . '%;">
                    	</div>
                    	<div class="review-count">' . ' ' . $point .' ('. $stds .')</div>
                    </div>';
	        	$html .= '</div>';
        	}
        return $html .= '</div>';
	}
	
	public static function getCourseAuthor(){
		$icons_url = esc_url(get_template_directory_uri()) . '/assets/images/svg-icons'; 
		$instructor_id = get_post_field( 'post_author', get_the_ID() );
		$instructor_slug = get_the_author_meta( 'user_nicename', $instructor_id );
		$instructor_img = !get_avatar( $instructor_id, 2) ? '<img class="default-avatar" src="'. $icons_url . '/user-alt.svg' .'" alt="'. $instructor_slug .'"/>' : get_avatar( $instructor_id, 32, $default = '', $instructor_slug, $args = array( 'class' => 'rounded-circle' ) ); 
		$pages = get_pages(array(
		    'meta_key' => '_wp_page_template',
		    'meta_value' => 'page-instructor.php'
		));
		if ( ! empty( $pages ) ) {
			return '<a href="'. esc_url( trailingslashit(site_url()) ) . get_post($pages[0]->ID)->post_name . '/' . $instructor_slug .'">' . $instructor_img . get_the_author_meta( 'display_name', $instructor_id ) .'</a>';
		}
		return '';
	}
	public static function getCourseLessons(){
		if(function_exists('learn_press_get_course')){		
			$course = learn_press_get_course( get_the_ID() );
	    	$lessons = $course->count_items( LP_LESSON_CPT ) . esc_html__(' lessons', WP_MF_CORE_SLUG);
		}
		return $lessons;
	}
	public static function deliverCoursesAjaxRequest(){
		$settings = isset($_REQUEST['settings']) ? $_REQUEST['settings'] : array();
		$offset = isset($_REQUEST['offset']) ? absint($_REQUEST['offset']) : 0;
		$category = isset($_REQUEST['category']) ? sanitize_text_field($_REQUEST['category']) : 'all';
		$sort = isset($_REQUEST['sort']) ? sanitize_text_field($_REQUEST['sort']) : '';
		$style = isset($_REQUEST['style']) ? sanitize_text_field($_REQUEST['style']) : '';
		$type = isset($_REQUEST['type']) ? sanitize_text_field($_REQUEST['type']) : '';
		$courses_count = isset($settings['courses_count']) ? absint($settings['courses_count']) : 10;


			$args = array(
				'post_type' => 'lp_course',
				'post_status' => 'publish',
				'posts_per_page' => $courses_count,
				'offset' => $offset
			);

			if($category !== 'all'){
				$args['tax_query'] = array(
					array(
						'taxonomy' => 'course_category',
						'field'    => 'slug',
			            'terms'    => $category,
					)
				);
			}
			if( $sort == 'popular' ){
				$args['meta_key'] = 'count_enrolled_users';
				$args['orderby'] = 'meta_value_num';
			}
			if( $sort == 'latest' ){
				$args['orderby'] = 'date';
				$args['order'] = 'desc';
			}
			if( $sort == 'treanding' ){
				$args['meta_query'] = array(
					'relation' => 'OR',
					array(
						'key' => '_lp_count_course_view',
					)
				);
				$args['orderby'] = 'meta_value_num';
			}
			if( $type == 'slider' ){
				$response = self::prepareCourseSlider( $args, $settings, $offset, $style );
			}else{
				$response = self::prepareCourses($args, $settings, $offset, $category);
			}
		echo $response;
		die();
	}
	public static function prepareCourseSlider( $args, $settings, $offset = '', $style = '' ){
		$html = '';
		$posts = new \WP_Query($args);
			if($posts->have_posts()){
				$i = 1;
				while ($posts->have_posts()) {
					if( $i == 1 ){
						$classes[] = 'swiper-slide-active';
					}
					if( $i == 2 ){
						$classes[] = 'swiper-slide-next';
					}
					$settings['style'] = $style;
					$settings['classes'] = $classes;
					$settings['use_slider'] = true;
					$posts->the_post();
					$course = \LP_Global::course();	
					$html .= apply_filters( 'tophive/learnpress/default/single/course/grid-two', $course, $settings );
					$i++;
				}
			}
		return $html;
	}
	public static function deliverPostsAjaxRequest(){
		$settings = isset($_REQUEST['settings']) ? $_REQUEST['settings'] : array();
		$offset = isset($_REQUEST['offset']) ? absint($_REQUEST['offset']) : 0;
		$category = isset($_REQUEST['category']) ? sanitize_text_field($_REQUEST['category']) : 'all';
		$blog_post_count = isset($settings['blog_post_count']) ? absint($settings['blog_post_count']) : 10;

		$args = array(
			'posts_per_page' => $blog_post_count,
			'offset' => $offset
		);
		if($category !== 'all'){
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'category',
					'field'    => 'term_id',
					'terms'    => $category,
				)
			);
		}
		$response = self::prepareBlogs($args, $settings, $offset);
		wp_send_json( $response );
	}
	public static function prepareCourses( $args, $settings, $offset = 0, $category = '' ){
		$courses = new \WP_Query($args);
		$displayflex = $settings['select_layout'] === 'thumb-left' || $settings['select_layout'] === 'thumb-right' ? 'ec-d-md-flex' : ''; 
		if($courses->have_posts()){
			$html = '';
			$k = 1;
			while ($courses->have_posts()){
				$courses->the_post();
				$course = \LP_Global::course();

				$ihover_position = self::hover_info_position($settings['select_columns'], $k);

					$settings['hover_info_position'] = $ihover_position;
					// course column
					$html .= apply_filters( 'tophive/learnpress/default/single/course/grid-two', $course, $settings );
					// end courses
				$k++;
			}


			$prev_offset = !empty($offset) ? ($offset - $settings['courses_count']) : -1;
			$next_offset = !empty($offset) ? ( $offset + $settings['courses_count']) < $courses->found_posts ? ($settings['courses_count'] + $offset ) : $offset : $settings['courses_count'];
			
			$prev_button_class = $prev_offset < 0 ? 'disabled ' : '';
			$next_button_class = ($offset + $settings['courses_count']) >= $courses->found_posts ? 'ec-d-none ' : '';
			if( $settings['nav_type'] == 'load_more' ){
				if( $courses->found_posts > $settings['courses_count'] ){
					$html .= '<div class="th-course-pagination-arrow ec-col-12 '. $settings['Load_more_btn_position'] .'">';
					    $html .= '<button data-type="load_more" data-offset="'. $next_offset .'" data-settings="'. htmlspecialchars(json_encode($settings)) .'" class="'. $next_button_class .'ec-switch-button tophive-infinity-button ec-switch-button-next button">'. $settings['load_more_text'] .'</button>';
				    $html .= '</div>';
				}
			}
			if( $settings['nav_type'] == 'arrow' ){
				$html .= '<div class="th-course-pagination-arrow th-course-pagination-next-prev '. $settings['arrow_position'] .'">';
					if( $courses->found_posts > $settings['courses_count'] ){
					    $html .= '<button data-type="arrow" data-category="' . $category . '" data-offset="'. $prev_offset .'" data-settings="'. htmlspecialchars(json_encode($settings)) .'" class="'. $prev_button_class .'ec-switch-button ec-switch-button-prev center-svg button ec-mr-2">
					    	<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-chevron-left" fill="currentColor" preserveAspectRatio="xMidYMin" xmlns="http://www.w3.org/2000/svg">
							  <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
							</svg>
					    </button>';
					    $html .= '<button data-type="arrow" data-category="'. $category .'" data-offset="'. $next_offset .'" data-settings="'. htmlspecialchars(json_encode($settings)) .'" class="'. $next_button_class .'ec-switch-button ec-switch-button-next center-svg button">
					    	<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-chevron-right" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
							  <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
							</svg>
					    </button>';
					}
			    $html .= '</div>';
			}
		}else{
			$html = '<div class="ec-text-center">' . esc_html__('No Courses have been Found', WP_MF_CORE_SLUG) . '</div>';
		}
		return $html;
	}
	
	public static function getCourseBody( $settings, $course, $id){
		$html = '<div class="th-course-details">';
			// return sale price
			$price = explode('.', $course->get_price_html())[0];

			// Pricing
			$html .= self::getCoursePrice($settings['show_pricing'], $price);

        	$html .= self::getCourseCategory( $settings['cat_display'], $settings['show_cat_bg'], $id );

            // $html .= apply_filters( 'tophive/elementor/learnpress/course-block/before-title', get_the_ID() );
            $html .= self::getCourseTitle();
            $html .= apply_filters( 'tophive/elementor/learnpress/course-block/after-title', get_the_ID(), $settings );

            $html .= self::getCourseDetails($settings['show_desc'], $settings['word_count']);
            if( 'yes' == $settings['show_hover_info'] ){
				$html .= self::getHoverInfo($settings);
            }

		return $html .= '</div>';
	}
	
	
	
	public static function hover_info_position( $cols, $k ){
    	$var = '';
    	switch ($cols) {
    		case 'ec-col-md-12':
    			$var = ($k%1 == 0) ? 'right ' : '';
    			break;
    		
    		case 'ec-col-md-6':
    			$var = ($k%2 == 0) ? 'right ' : '';
    			break;

    		case 'ec-col-lg-4 ec-col-md-6 ec-col-12':
    			$var = ($k%3 == 0) ? 'right ' : '';
    			break;

    		case 'ec-col-lg-3 ec-col-md-6 ec-col-12':
    			$var = ($k%4 == 0) ? 'right ' : '';
    			break;

    		case 'ec-col-lg-2 ec-col-md-6 ec-col-12':
    			$var = ($k%6 == 0) ? 'right ' : '';
    			break;
    		
    		default:
    			$var = '';
    			break;
    	}
    	return $var;
    }
	

	/********************************************
	************* Starting Of Blog **************
	*********************************************/

	public static function prepareBlogs( $args, $settings, $offset = 0 ){
		$posts = new \WP_Query($args);

		// echo '<pre>';
		// print_r($posts);
		// echo '</pre>';
		$displayflex = 'thumb-left' == $settings['select_blog_layout'] || 'thumb-right' == $settings['select_blog_layout'] ? 'ec-d-md-flex' : ''; 
		if($posts->have_posts()){
			$html = '';
			$k = 1;
			$d = 100;

			while ($posts->have_posts()){
				$posts->the_post();

					$html .= '<div class="'. $settings['select_blog_columns'] .'">';

						/* ---------- Single blog ---------------*/
						$html .= '<div class="th-blog-block ec-justify-content-between ' . $displayflex .'">';
							if( $settings['select_blog_layout'] === 'thumb-left' ){
								/*------- Blog Thumbnail -------*/
								$html .= '<div class="ec-d-flex ec-align-items-end ec-flex-column">';
                            		$html .= self::renderBlogThumb($settings['show_blog_thumb']);
								$html .= '</div>';

								/*--------- Blog Content ---------*/
								$html .= '<div>';
									/*--------- Blog Single body  ---------*/
									$html .= self::getBlogBody( $settings, get_the_ID() );
									/* ---------Footer -----------*/
									$html .= self::getBlogFooter($settings['display_footer_author'], $settings['blog_read_more_text'], $settings['display_footer_view']);
								$html .= '</div>';
							}elseif( $settings['select_blog_layout'] === 'thumb-right' ){
								/*--------- Blog Content ---------*/
								$html .= '<div class="ec-d-flex ec-align-items-end ec-flex-column">';
									/*--------- Blog Single body  ---------*/
									$html .= self::getBlogBody( $settings, get_the_ID() );
									/* ---------Footer -----------*/
									$html .= self::getBlogFooter($settings['display_footer_author'], $settings['blog_read_more_text'], $settings['display_footer_view']);
								$html .= '</div>';
								/*------- Blog Thumbnail -------*/
								$html .= '<div>';
                            		$html .= self::renderBlogThumb($settings['show_blog_thumb']);
								$html .= '</div>';
							}else{
								/*------- Blog Thumbnail -------*/
                        		$html .= self::renderBlogThumb($settings['show_blog_thumb']);
								/*--------- Blog Content ---------*/
								/*--------- Blog Single body  ---------*/
								$html .= self::getBlogBody( $settings, get_the_ID() );
								/* ---------Footer -----------*/
								$html .= self::getBlogFooter($settings['display_footer_author'], $settings['blog_read_more_text'], $settings['display_footer_view']);
							}
						$html .= '</div>';
					$html .= '</div>';
					/*--------- End Blog Grid Single ----------*/
				$k++;
				$d = $d + 50;
			}
			$prev_offset = !empty($offset) ? ($offset - $settings['blog_post_count']) : -1;
			$next_offset = !empty($offset) ? ($offset + $settings['blog_post_count']) < $posts->found_posts ? ($settings['blog_post_count'] + $offset) : $offset : $settings['blog_post_count'];
			
			$prev_button_class = $prev_offset < 0 ? 'disabled ' : '';
			$next_button_class = ($offset + $settings['blog_post_count']) > $posts->found_posts ? 'disabled ' : '';
			if( 'yes' == $settings['blog_carousel_arrow'] ){
				if( $posts->found_posts > $settings['blog_post_count'] ){
					$html .= '<div class="th-blog-pagination-arrow">';
					    $html .= '<div data-offset="'. $prev_offset .'" data-settings="'. htmlspecialchars(json_encode($settings)) .'" class="'. $prev_button_class .'ec-switch-button ec-switch-button-prev '. $settings['blog_arrow_position'] .'"><i class="eicon-angle-left"></i></div>';
					    $html .= '<div data-offset="'. $next_offset .'" data-settings="'. htmlspecialchars(json_encode($settings)) .'" class="'. $next_button_class .'ec-switch-button ec-switch-button-next '. $settings['blog_arrow_position'] .'"><i class="eicon-angle-right"></i></div>';
				    $html .= '</div>';
				}
			}
		}else{
			$html = '<div class="ec-text-center">' . esc_html__('No Posts have been Found', WP_MF_CORE_SLUG) . '</div>';
		}
		return $html;
	}
	/*--------------------- BLOG THUMBNAIL ----------------------------*/
	public static function renderBlogThumb( $thumb, &$id = '' ){
		return 'yes' === $thumb ? '<div class="th-blog-thumb">'. get_the_post_thumbnail( $id, 'post-thumbnail' ) .'</div>' : '';
	}
	/*------------------- BLOG GRID CONTENT MAIN ----------------------------*/
	public static function getBlogBody( $settings, $id){
		$html = '<div class="th-blog-details">';

			if( $settings['meta_position'] == 'after-title' ){
            	$html .= self::getBlogTitle();

	        	$html .= self::getBlogMeta( 
	        		$settings['display_category'], 
	        		$settings['display_comments'],  
	        		$settings['display_author'],  
	        		$settings['display_views'],  
	        		$settings['display_date_meta'], 
	        		$settings['blog_date_format'], 
	        		$id
	        	);
			}else{
	        	$html .= self::getBlogMeta( 
	        		$settings['display_category'], 
	        		$settings['display_comments'],  
	        		$settings['display_author'],  
	        		$settings['display_views'],  
	        		$settings['display_date_meta'], 
	        		$settings['blog_date_format'], 
	        		$id
	        	);
				$html .= self::getBlogTitle();
			}
            $html .= self::getBlogDetails($settings['blog_show_desc'], $settings['blog_word_count']);

		return $html .= '</div>';
	}
	/*------------------- BLOG GRID DATE ----------------------------*/
	public static function getBlogMeta( $category, $comment, $author, $views, $show_date, $date_format, $id ){
		$date = '';
		$html = '';
		$categories = get_the_category();
		switch ($date_format) {
			case 'FjY':
				$date = get_the_date( 'F j Y', $id );
				break;
			case 'DMj':
				$date = get_the_date( 'D M j', $id );
				break;
			case 'dSMY':
				$date = get_the_date( 'dS M Y', $id );
				break;
			case 'FY':
				$date = get_the_date( 'F Y', $id );
				break;
			
			default:
				$date = get_the_date( 'F j', $id );
				break;
		}
		$html .= '<div class="th-elem-blog-meta">';

		$html .= 'yes' == $category && !empty($categories) ? 
			'<span class="th-blog-meta-category">
				<svg width="1.1em" height="1.1em" viewBox="0 0 16 16" class="bi bi-folder" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
					<path d="M9.828 4a3 3 0 0 1-2.12-.879l-.83-.828A1 1 0 0 0 6.173 2H2.5a1 1 0 0 0-1 .981L1.546 4h-1L.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3v1z"/>
					<path fill-rule="evenodd" d="M13.81 4H2.19a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4zM2.19 3A2 2 0 0 0 .198 5.181l.637 7A2 2 0 0 0 2.826 14h10.348a2 2 0 0 0 1.991-1.819l.637-7A2 2 0 0 0 13.81 3H2.19z"/>
				</svg>
				<a href="' . esc_url(get_category_link($categories[0]->term_id)) . '">' . esc_html($categories[0]->name) . '</a>
			</span>' 
		: '';


		$html .= 'yes' == $author ? '<span class="th-blog-meta-author">
			<svg width="1.1em" height="1.1em" viewBox="0 0 16 16" class="bi bi-person" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
			  <path fill-rule="evenodd" d="M13 14s1 0 1-1-1-4-6-4-6 3-6 4 1 1 1 1h10zm-9.995-.944v-.002.002zM3.022 13h9.956a.274.274 0 0 0 .014-.002l.008-.002c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664a1.05 1.05 0 0 0 .022.004zm9.974.056v-.002.002zM8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
			</svg>
		'. get_the_author_link() .'</span>' : '';
		$html .= 'yes' == $show_date ? '<span class="th-blog-meta-date">
			<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-calendar-check" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
			  <path fill-rule="evenodd" d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
			  <path fill-rule="evenodd" d="M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1zm1-3a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2H2z"/>
			  <path fill-rule="evenodd" d="M3.5 0a.5.5 0 0 1 .5.5V1a.5.5 0 0 1-1 0V.5a.5.5 0 0 1 .5-.5zm9 0a.5.5 0 0 1 .5.5V1a.5.5 0 0 1-1 0V.5a.5.5 0 0 1 .5-.5z"/>
			</svg>
		'. $date .'</span>' : '';
		$html .= 'yes' == $comment ? '<span class="th-blog-meta-comments">
		<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-chat-left-dots" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
		  <path fill-rule="evenodd" d="M14 1H2a1 1 0 0 0-1 1v11.586l2-2A2 2 0 0 1 4.414 11H14a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 0a2 2 0 0 0-2 2v12.793a.5.5 0 0 0 .854.353l2.853-2.853A1 1 0 0 1 4.414 12H14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
		  <path d="M5 6a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
		</svg>
		 '. get_comments_number( $id ) .'</span>' : '';
		$html .= 'yes' == $views ? '<span class="th-blog-meta-views">
			<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-eye" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
			  <path fill-rule="evenodd" d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.134 13.134 0 0 0 1.66 2.043C4.12 11.332 5.88 12.5 8 12.5c2.12 0 3.879-1.168 5.168-2.457A13.134 13.134 0 0 0 14.828 8a13.133 13.133 0 0 0-1.66-2.043C11.879 4.668 10.119 3.5 8 3.5c-2.12 0-3.879 1.168-5.168 2.457A13.133 13.133 0 0 0 1.172 8z"/>
			  <path fill-rule="evenodd" d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
			</svg>
		'. get_post_meta( $id, 'tophive_get_post_view', true ) .'</span>' : '';
		$html .= '</div>';
		return $html;
	}
	/*------------------- BLOG GRID TITLE ----------------------------*/
	public static function getBlogTitle(){
		return '<a href="'. get_the_permalink() .'"><h5 class="blog-block-title">' . get_the_title() . '</h5></a>';
	}
	/*------------------- BLOG GRID CONTENT ----------------------------*/
	public static function getBlogDetails( $s, $count ){
		return 'yes' == $s ? '<div class="th-description">' . wp_trim_words( get_the_content(), $count ) . '</div>' : '';
	}
	/*------------------- BLOG GRID FOOTER ----------------------------*/
	public static function getBlogFooter( $author = '', $readmore = '', $views = '' ){
		$lessons = '';
		$html = '<div class="th-blog-footer ec-w-100 ec-row ec-mt-auto">';
    		// blog author
			if( is_rtl() ){
        		$html .= '' != $readmore ? '<div class="ec-col ec-col-7 footer-readmore"><p class="ec-mb-0 blog-read-more"><a href="'. get_post_permalink() .'"><i class="ti-arrow-left"></i>'. $readmore .'</a></p></div>' : '';
			}else{

        		$html .= '' != $readmore ? '<div class="ec-col ec-col-7 footer-readmore"><p class="ec-mb-0 blog-read-more"><a href="'. get_post_permalink() .'">'. $readmore .'<i class="ti-arrow-right"></i></a></p></div>' : '';
			}
			$html .= 'yes' == $author ? '<div class="ec-col ec-col-5"><p class="ec-mb-0 blog-author"><small>'. get_avatar( get_the_author_meta( 'ID' ), 20 ) .'</small>' . get_the_author_posts_link() . '</p></div>' : '';
			$html .= 'yes' == $views ? '<div class="ec-col ec-col-5"><p class="ec-mb-0 blog-view"><small><i class="ti-pulse"></i></small>' . get_post_meta( get_the_ID(), 'tophive_get_post_view', true ) . '</p></div>' : '';
        return $html .= '</div>';
	}

	/*------------------------ ADVANCED AJAX SEARCH ----------------------*/
	public function tophiveAdvancedSearch(){
		$text = isset($_REQUEST['text']) ? sanitize_text_field($_REQUEST['text']) : '';
		$post_type = isset($_REQUEST['post_type']) ? sanitize_text_field($_REQUEST['post_type']) : 'post';

		$args = array(
			'post_status' => 'publish',
			'post_type' => $post_type,
			'posts_per_page' => 4,
			's' => $text,
		);

		$query = new \WP_Query($args);

		$html = '';

		if( $query->have_posts() ){
			$html .= '<ul class="tophive-advanced-search-list">';
			while ($query->have_posts()) {
			    $query->the_post();
			    $html .= '<li>'; 
				    $html .= '<a href="'. get_the_permalink() .'">';
					    $html .= '<div class="ec-d-flex ec-align-items-center">';
						    $html .= '<div class="ec-w-75">';
						    	$html .= '<h6 class="title">'. get_the_title() .'</h6>';	
						    $html .= '</div>';
						    $html .= '<div class="ec-w-25">';
						    	$html .= '<h6 class="price">'. $this->get_price_text(get_the_ID()) .'</h6>';
						    $html .= '</div>';
					    $html .= '</div>';
				    $html .= '</a>';
			    $html .= '</li>'; 
			}
			$html .= '</ul>';
		}else{
			$html .= '<p class="ec-mb-0 ec-p-3">'. esc_html__( 'Nothing found for ' . $text , WP_MF_CORE_SLUG ) .'</p>';
		}
		echo $html;
		die();
	}
	public function get_price_text( $id ){
		$price = intval(get_post_meta( $id, '_lp_price', $single = true ));
    	$sale_price = intval(get_post_meta( $id, '_lp_sale_price', $single = true ));
    	$price_html = '';
    	$symbol = learn_press_get_currency_symbol();

    	if( $sale_price > 0 ){
    		$price_html = '<span class="pr-1"><small><strike>'. $symbol . $price .'</strike></small></span><span> '. $symbol . $sale_price .'</span>';
    	}elseif( ($sale_price == 0 && $price == 0) || ( $sale_price == '' && $price == 0 ) ){
    		$price_html = ' <span>'. esc_html__( 'Free', 'tophive' ) .'</span>';
    	}else{
    		$price_html = ' <span>'. $symbol . $price .'</span>';
    	}
    	return $price_html;
	}
	public function tophivePostTopicSubmit(){
		if ( ! class_exists( 'bbPress' ) || ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Authentication required.', WP_MF_CORE_SLUG ) ), 401 );
		}

		check_ajax_referer( 'metafans_core_topic', 'nonce' );

		if ( ! current_user_can( 'publish_topics' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You are not allowed to create topics.', WP_MF_CORE_SLUG ) ), 403 );
		}

		$post_info = isset( $_POST['data'] ) && is_array( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : array();
		$post_data = array(
			'post_title'     => isset( $post_info[0]['value'] ) ? sanitize_text_field( $post_info[0]['value'] ) : '',
			'post_content'   => isset( $post_info[3]['value'] ) ? wp_kses_post( $post_info[3]['value'] ) : '',
			'post_parent'    => isset( $post_info[1]['value'] ) ? absint( $post_info[1]['value'] ) : 0,
			'post_author'    => get_current_user_id(),
			'comment_status' => 'open',
		);

		if ( '' === $post_data['post_title'] ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Title is empty.', WP_MF_CORE_SLUG ) ), 400 );
		}
		if ( '' === trim( wp_strip_all_tags( $post_data['post_content'] ) ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Topic content is empty.', WP_MF_CORE_SLUG ) ), 400 );
		}
		if ( ! $post_data['post_parent'] || 'forum' !== get_post_type( $post_data['post_parent'] ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You need to select a valid forum.', WP_MF_CORE_SLUG ) ), 400 );
		}
		if ( function_exists( 'bbp_is_forum_closed' ) && bbp_is_forum_closed( $post_data['post_parent'] ) && ! current_user_can( 'moderate' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'This forum is closed.', WP_MF_CORE_SLUG ) ), 403 );
		}
		if ( function_exists( 'bbp_get_forum_visibility' ) ) {
			$visibility = bbp_get_forum_visibility( $post_data['post_parent'] );
			if ( 'private' === $visibility && ! current_user_can( 'read_private_forums' ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'You cannot access this forum.', WP_MF_CORE_SLUG ) ), 403 );
			}
			if ( 'hidden' === $visibility && ! current_user_can( 'read_hidden_forums' ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'You cannot access this forum.', WP_MF_CORE_SLUG ) ), 403 );
			}
		}

		$post_id = function_exists( 'bbp_insert_topic' ) ? bbp_insert_topic( $post_data ) : 0;
		if ( ! $post_id || is_wp_error( $post_id ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Topic creation failed.', WP_MF_CORE_SLUG ) ), 500 );
		}

		if ( function_exists( 'bp_activity_add' ) ) {
			$user_url  = function_exists( 'bp_core_get_user_domain' ) ? bp_core_get_user_domain( get_current_user_id() ) : get_author_posts_url( get_current_user_id() );
			$forum_url = get_permalink( $post_data['post_parent'] );
			$action    = sprintf(
				'%1$s %2$s %3$s',
				'<a href="' . esc_url( $user_url ) . '">' . esc_html( get_the_author_meta( 'display_name', get_current_user_id() ) ) . '</a>',
				esc_html__( 'started a new topic in forum', WP_MF_CORE_SLUG ),
				'<a href="' . esc_url( $forum_url ) . '">' . esc_html( get_the_title( $post_data['post_parent'] ) ) . '</a>'
			);
			bp_activity_add( array(
				'user_id'      => get_current_user_id(),
				'component'    => 'bbpress',
				'type'         => 'activity_update',
				'action'       => wp_kses_post( $action ),
				'content'      => $post_data['post_content'],
				'primary_link' => get_permalink( $post_id ),
			) );
		}

		wp_send_json_success( array(
			'post_id'      => $post_id,
			'message'      => esc_html__( 'Topic created successfully.', WP_MF_CORE_SLUG ),
			'redirect_url' => get_permalink( $post_id ),
		) );
	}
}

?>