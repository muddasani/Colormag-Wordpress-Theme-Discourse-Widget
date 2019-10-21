<?php
/*
Plugin Name: Colormag WordPress Theme Discourse Widget
Plugin URI: https://studmed.dk
Description: This plugin adds a discourse widget to be used with the free colormag theme by ThemeGrill.
Version: 1.0 beta 02
Author: Frederik Liljefred
Author URI: https://studmed.dk
License: GPL2
*/

// The widget class Style 1
class Colormag_Discourse_Widget_Style_1 extends WP_Widget {

	// Main constructor
	public function __construct() {
		parent::__construct(
			'colormag_discourse_widget',
			__( 'Colormag Discourse Widget Style 1', 'colormag-discourse-widget' ),
			array(
				'classname'                   => 'widget_featured_posts widget_featured_meta',  //we need to set the class to the same name as the original colormag theme class
				'customize_selective_refresh' => true,
			)
		);
	}

	// The widget form (for the backend )
	public function form( $instance ) {

		// Set widget defaults
		$defaults = array(
			'title'    => '',
			'textarea' => '',
			'select'   => '',
			'number'   => '5',		//Set the default number of posts == 5
			'type'   => 'latest',  	//set default type to "latest"
		);
		
		// Greb the settings in the wp admin site; wp-admin/admin.php?page=colormag_discourse_widget_admin
		$discourse_url = get_option('discourse_url');
		
		// Get the list of categories from my forum
		$DiscourseCategoryUrl = $discourse_url . '/'. 'categories.json';  //alert at the moment it is very sensitive for the right path - eg https://studmed.dk work but not https://wwww.studmed.dk
		$response = wp_remote_get($DiscourseCategoryUrl);
		
		$body = wp_remote_retrieve_body( $response ) ;
		$categoryjsondata = json_decode($body);	
		
		// Parse current settings with defaults
		extract( wp_parse_args( ( array ) $instance, $defaults ) ); ?>
		
		<?php // Widget Image ?>
		<p>
			<?php _e( 'Layout will be as below:', 'colormag-discourse-widget' ) ?></p>
			<div style="text-align: center;"><img src="<?php echo plugin_dir_url( __FILE__ ) . '/images/style-1.jpg' ?>"></div>
		<p>		

		<?php // Widget Title ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title', 'colormag-discourse-widget' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<?php // Description Field ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'textarea' ) ); ?>"><?php _e( 'Description:', 'colormag-discourse-widget' ); ?></label>
			<textarea class="widefat"  rows="5" cols="20" id="<?php echo esc_attr( $this->get_field_id( 'textarea' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'textarea' ) ); ?>"><?php echo wp_kses_post( $textarea ); ?></textarea>
		</p>
		
		<?php // Number of post ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to display:', 'colormag-discourse-widget' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" />
		</p>

		<?php // Type of posts "latest" vs "category" ?>
		<p>
			<input type="radio" <?php checked( $type, 'latest' ) ?> id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" value="latest" /><?php _e( 'Show latest posts', 'colormag-discourse-widget' ); ?>
			<br />
			<input type="radio" <?php checked( $type, 'category' ) ?> id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" value="category" /><?php _e( 'Show posts from a category', 'colormag-discourse-widget' ); ?>
			<br />
		</p>
		
		<?php // Choose category from your forum list ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'select' ); ?>"><?php _e( 'Select Forum Category', 'colormag-discourse-widget' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'select' ); ?>" id="<?php echo $this->get_field_id( 'select' ); ?>" class="widefat">
			<?php
			foreach( $categoryjsondata->category_list->categories as $one ) {
				echo '<option value="' . esc_attr( $one->id ) . '" id="' . esc_attr( $one->id ) . '" '. selected( $select, $one->id, false ) . '>'. $one->name . '</option>';
			}			
			?>
			</select>
		</p>

	<?php }

	// Update widget settings
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']    = isset( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['textarea'] = isset( $new_instance['textarea'] ) ? wp_kses_post( $new_instance['textarea'] ) : '';
		$instance['select']   = isset( $new_instance['select'] ) ? wp_strip_all_tags( $new_instance['select'] ) : '';
		$instance['number']   = absint( $new_instance['number'] );
		$instance['type']     = $new_instance['type'];		//latest vs category
		return $instance;
	}

	// Display the widget
	public function widget( $args, $instance ) {
		extract( $instance );
		extract( $args );

		// Check the widget options
		$title    = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
		$textarea = isset( $instance['textarea'] ) ?$instance['textarea'] : '';
		$select   = isset( $instance['select'] ) ? $instance['select'] : '';
		$number   = empty( $instance['number'] ) ? 4 : $instance['number'];
		$type     = isset( $instance['type'] ) ? $instance['type'] : 'latest';		//latest vs category
		
		// Greb the settings in the wp admin site; wp-admin/admin.php?page=colormag_discourse_widget_admin
		$discourse_url = get_option('discourse_url'); //The discourse forum url
		$random_image = get_option('random_image'); //Do we want to use a random image or not "1" when it is checked
		$grayscale_random_image = get_option('grayscale_random_image'); //If selected then random images gets a grayscale effect
		$blur_random_image = get_option('blur_random_image'); //If selected then random images gets a blur effect
		
		// WordPress core before_widget hook (always include )
		echo $before_widget;
		
		?>
		<?php
						
			//First we need to get the forum list to get the title colors to match the colors set for the category in the forum
			$DiscourseCategoryUrl = $discourse_url . '/'. 'categories.json';  //alert at the moment it is very sensitive for the right path - eg https://studmed.dk work but not https://wwww.studmed.dk			
			$response = wp_remote_get($DiscourseCategoryUrl);		
			$body = wp_remote_retrieve_body( $response );
			$categoryjsondata = json_decode($body);
			
			//We need to trim the date from 2019-09-08T09:04:24.552Z ---> 13. august 2019
			$date_format = ! empty( $this->options['custom-datetime-format'] ) ? $this->options['custom-datetime-format'] : 'd. F Y'; 
			
			foreach( $categoryjsondata->category_list->categories as $one ) {
				if(  $one->id == $select) {
					$categorycolor = $one->color;
				}
			}
			if ( $type != 'latest' ) {  	// for categories get the color from the forum categories colortag
				$border_color = 'style="border-bottom-color:#'. $categorycolor . ';"';
				$title_color  = 'style="background-color:#'. $categorycolor . ';"';
			} else {	//for latest 
				$border_color = '';
				$title_color  = '';
			}
			if ( ! empty( $title ) ) { 	//let us get the widget title from the widget and color it based on the forum category settings
				echo '<h3 class="widget-title" ' . $border_color . '><span ' . $title_color . '>' . esc_html( $title ) . ' </span></h3>';
			}
			if ( ! empty( $textarea ) ) {	//let us get the widget description from the widget if there is any
				?> <p> <?php echo esc_textarea( $textarea ); ?> </p> <?php 
			} ?>
			
<?php		
			// Get data from from the forum either latest.json or from the category specific json
			if ( $type != 'latest' ) {  	// for categories get the data from the forum categories like: https://studmed.dk/c/studmed-debat.json
				$discourse_url_new = $discourse_url . '/c/'. $select . '.json';  //alert at the moment it is very sensitive for the right path - eg https://studmed.dk work but not https://wwww.studmed.dk
			} else {	//for latest 
				$discourse_url_new = $discourse_url . '/'. 'latest.json';  //alert at the moment it is very sensitive for the right path - eg https://studmed.dk work but not https://wwww.studmed.dk
			}
			
			$response = wp_remote_get($discourse_url_new);
			
			$body = wp_remote_retrieve_body( $response ) ;
			$data = json_decode($body);

			foreach( $data->topic_list->topics as $one ) {
				if ($i<$number) {
					if( $one->pinned === false ) {
						$created_at_formatted = mysql2date( $date_format, $one->created_at ); //we have to trim the date format
						if (strlen($one->title)>50) { // we need to trim the lenght of the title if it is to long
							$title_formatted = substr($one->title, 0, 50);  //limit the title lenght to 50 chars
							$title_formatted = $title_formatted . '..'; // we need to visualize that the lengt have been shooten
							} else {
							$title_formatted = $one->title;  //limit the title lenght to 55 chars
						}
						
						//Our random image path
						$picsum = 'https://picsum.photos';
						
						//Let us set the after effect of the images - either blur, grayscale, or none effect
						if ( empty($blur_random_image) ) {
							$blur = "";
						} else {
							$blur = "&blur=2"; // 2 indicates how much blur effect 1-10
						}
						if ( empty($grayscale_random_image) ) {
							$grayscale = "";
						} else {
							$grayscale = "&grayscale";
						}
						$image_effect = $grayscale.$blur;
						
						
						//Advance usage of picsum https://picsum.photos/390/205/?random=6786&grayscale&blur=2
						$random_image_scr_small = $picsum.'/130/90/?random='.$one->id.''.$image_effect;
						$random_image_scr_big = $picsum.'/390/205/?random='.$one->id.''.$image_effect;

								  //We now wants to get the content from the topic as well. This is solved by the following-post
								  $discourseTopicUrl = $discourse_url.'/t/'.$one->slug.'/'.$one->id.'.json';
								  $response = wp_remote_get($discourseTopicUrl);								  
								  $body2 = wp_remote_retrieve_body( $response );
								  $data2 = json_decode($body2);		  
								  
								  foreach( $data2->post_stream->posts as $one2 ) {
										 if ($one2->post_number === 1) { //It will show the first post
											$content_formatted = htmlspecialchars(strip_tags(substr($one2->cooked, 0, 255)));
											$content_formatted = $content_formatted . '...';
										}
								  };							
								  												
					# code...
					if ( $i == 0 ) {
						echo '<div class="first-post">';
					} elseif ( $i == 1 ) {
						echo '<div class="following-post">';
					}
?>					<div class="single-article clearfix"><?php
					if ( $i == 0 ) {
						if (isset($one->image_url)) {
							// The post got an image
							echo '
								<figure>
									<a href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$one->title.'">
										<img width="390" height="205" src="'.$one->image_url.'" class="attachment-colormag-featured-post-medium size-colormag-featured-post-medium wp-post-image" alt="'.$one->title.'" title="'.$one->title.'" />
									</a>
								</figure>';
						} elseif (!empty($random_image) && empty($one->image_url)) {	
							// Random Image is activated and the post doesnt have an image 
							echo '
								<figure>
									<a href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$one->title.'">
										<img width="390" height="205" src="'.$random_image_scr_big.'" class="attachment-colormag-featured-post-medium size-colormag-featured-post-medium wp-post-image" alt="'.$one->title.'" title="'.$one->title.'" />
									</a>
								</figure>'; 
						} else {
							// Random Image is deactivated and the post doesnt have an image 
							echo '';
						};
						echo	'<div class="article-content">
									<div class="above-entry-meta">
										';
												$j=0;
												foreach ($one->posters as $poster) {
													foreach ($data->users as $user) {
														if ($poster->user_id == $user->id) {
															echo '<a href="'.$discourse_url.'/u/'.$user->username.'/summary/"><img alt="'.$user->username.'"  style="border-radius:50%;-moz-border-radius:50%; -webkit-border-radius:50%;-webkit-box-shadow: 0 1px 2px rgba(0,0,0,.1); -moz-box-shadow: 0 1px 2px rgba(0,0,0,.1);box-shadow: 0 1px 2px rgba(0,0,0,.1);" width="40" height="40" src="'.$discourse_url.''.str_replace("{size}", "64", $user->avatar_template).'"/></a>';
														}
													}
												$j++;
												}
											echo '					
									</div>
									<h3 class="entry-title">
										<a href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$one->title.'">'.$title_formatted.'</a>
									</h3>
									<div class="below-entry-meta">
										<span class="posted-on">
											<a href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$created_at_formatted.'" rel="bookmark">
												<i class="fa fa-calendar-o"></i> 
													<time class="entry-date published updated" datetime="'.$one->created_at.'">'.$created_at_formatted.'</time>
											</a>
										</span>
										<span class="byline">
											<span class="author vcard">
												<i class="fa fa-user"></i>
												<a class="url fn n" href="'.$discourse_url.'/u/'.$one->last_poster_username.'/summary/" title="'.$one->last_poster_username.'">'.$one->last_poster_username.'</a>
											</span>
										</span>';
										if ($one->like_count<1) {  //if there is zero likes we use font awesome 4.7
										echo '	<span class="byline">
													<span class="author vcard">
														<i class="fa fa-heart-o"></i>
														<a class="url fn n" href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$one->title.'">'.$one->like_count.'</a>
													</span>
												</span>';
											} else { // if there is more than one like
										echo '	<span class="byline">
													<span class="author vcard">
														<i class="fa fa-heart"></i>
														<a class="url fn n" href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$one->title.'">'.$one->like_count.'</a>
													</span>
												</span>';
										};
										if ($one->posts_count<1) {
										echo '	<span class="comments">
													<i class="fa fa-comment-o"></i>
													<a href="'.$discourse_url.'/t/'.$one->slug.'/'.$one->id.'/'.$one->highest_post_number.'">'.$one->posts_count.'</a>
												</span>';
											} else {
										echo '	<span class="comments">
													<i class="fa fa-comment"></i>
													<a href="'.$discourse_url.'/t/'.$one->slug.'/'.$one->id.'/'.$one->highest_post_number.'">'.$one->posts_count.'</a>
												</span>';
										};
								echo '	<span class="byline">
											<span class="author vcard">
												<i class="fa fa-eye"></i>
												<a class="url fn n" href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$one->title.'">'.$one->views.'</a>
											</span>
										</span>
									</div>
									<div class="entry-content">
										<p>'.$content_formatted.'</p>
									</div>
								</div>
							</div>
						</div>							
						';
						} else {
						if (isset($one->image_url)) {
							// The post got an image
							echo '
								<figure>
									<a href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$one->title.'">
										<img width="130" height="90" src="'.$one->image_url.'" class="attachment-colormag-featured-post-medium size-colormag-featured-post-medium wp-post-image" alt="'.$one->title.'" title="'.$one->title.'" />
									</a>
								</figure>';
						} elseif (!empty($random_image) && empty($one->image_url)) {	
							// Random Image is activated and the post doesnt have an image 
							echo '
								<figure>
									<a href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$one->title.'">
										<img width="130" height="90" src="'.$random_image_scr_small.'" class="attachment-colormag-featured-post-small size-colormag-featured-post-small wp-post-image" alt="'.$one->title.'" title="'.$one->title.'"  />
									</a>
								</figure>'; 
						} else {
							// Random Image is deactivated and the post doesnt have an image 
							echo '';
						};							
						echo '
							<div class="article-content">
								<h3 class="entry-title">
									<a href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$one->title.'">'.$title_formatted.'</a>
								</h3>
								<div class="below-entry-meta">
									<span class="posted-on">
										<a href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$created_at_formatted.'" rel="bookmark">
											<i class="fa fa-calendar-o"></i> 
												<time class="entry-date published updated" datetime="'.$one->created_at.'">'.$created_at_formatted.'</time>
										</a>
									</span>';
										if ($one->posts_count<1) {
										echo '	<span class="comments">
													<i class="fa fa-comment-o"></i>
													<a href="'.$discourse_url.'/t/'.$one->slug.'/'.$one->id.'/'.$one->highest_post_number.'">'.$one->posts_count.'</a>
												</span>';
											} else {
										echo '	<span class="comments">
													<i class="fa fa-comment"></i>
													<a href="'.$discourse_url.'/t/'.$one->slug.'/'.$one->id.'/'.$one->highest_post_number.'">'.$one->posts_count.'</a>
												</span>';
										};
										if ($one->like_count<1) {  //if there is zero likes we use font awesome 4.7
										echo '	<span class="comments">
													<span class="author vcard">
														<i class="fa fa-heart-o"></i>
														<a class="url fn n" href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$one->title.'">'.$one->like_count.'</a>
													</span>
												</span>';
											} else { // if there is more than one like
										echo '	<span class="comments">
													<span class="author vcard">
														<i class="fa fa-heart"></i>
														<a class="url fn n" href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$one->title.'">'.$one->like_count.'</a>
													</span>
												</span>';
										};
						echo '				
								</div>
							</div>
						</div>	
						';
					}
					$i++;
					}
				}
			}
?>
<?php
		// WordPress core after_widget hook (always include )
		echo $after_widget;
	}
}

// The widget class Style 2
class Colormag_Discourse_Widget_Style_2 extends WP_Widget {

	// Main constructor
	public function __construct() {
		parent::__construct(
			'colormag_discourse_widget_style_2',
			__( 'Colormag Discourse Widget Style 2', 'colormag-discourse-widget' ),
			array(
				'classname'                   => 'widget_featured_posts widget_featured_posts_vertical widget_featured_meta',  //we need to set the class to the same name as the original colormag theme class
				'customize_selective_refresh' => true,
			)
		);
	}

	// The widget form (for the backend )
	public function form( $instance ) {

		// Set widget defaults
		$defaults = array(
			'title'    => '',
			'textarea' => '',
			'select'   => '',
			'number'   => '5',		//Set the default number of posts == 5
			'type'   => 'latest',  	//set default type to "latest"
		);
		
		// Greb the settings in the wp admin site; wp-admin/admin.php?page=colormag_discourse_widget_admin
		$discourse_url = get_option('discourse_url');
		
		// Get the list of categories from my forum
		$DiscourseCategoryUrl = $discourse_url . '/'. 'categories.json';  //alert at the moment it is very sensitive for the right path - eg https://studmed.dk work but not https://wwww.studmed.dk
		$response = wp_remote_get($DiscourseCategoryUrl);
		
		$body = wp_remote_retrieve_body( $response ) ;
		$categoryjsondata = json_decode($body);	
		
		// Parse current settings with defaults
		extract( wp_parse_args( ( array ) $instance, $defaults ) ); ?>
		
		<?php // Widget Image ?>
		<p>
			<?php _e( 'Layout will be as below:', 'colormag-discourse-widget' ) ?></p>
			<div style="text-align: center;"><img src="<?php echo plugin_dir_url( __FILE__ ) . '/images/style-2.jpg' ?>"></div>
		<p>		

		<?php // Widget Title ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title', 'colormag-discourse-widget' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<?php // Description Field ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'textarea' ) ); ?>"><?php _e( 'Description:', 'colormag-discourse-widget' ); ?></label>
			<textarea class="widefat"  rows="5" cols="20" id="<?php echo esc_attr( $this->get_field_id( 'textarea' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'textarea' ) ); ?>"><?php echo wp_kses_post( $textarea ); ?></textarea>
		</p>
		
		<?php // Number of post ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to display:', 'colormag-discourse-widget' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" />
		</p>

		<?php // Type of posts "latest" vs "category" ?>
		<p>
			<input type="radio" <?php checked( $type, 'latest' ) ?> id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" value="latest" /><?php _e( 'Show latest posts', 'colormag-discourse-widget' ); ?>
			<br />
			<input type="radio" <?php checked( $type, 'category' ) ?> id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" value="category" /><?php _e( 'Show posts from a category', 'colormag-discourse-widget' ); ?>
			<br />
		</p>
		
		<?php // Choose category from your forum list ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'select' ); ?>"><?php _e( 'Select Forum Category', 'colormag-discourse-widget' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'select' ); ?>" id="<?php echo $this->get_field_id( 'select' ); ?>" class="widefat">
			<?php
			foreach( $categoryjsondata->category_list->categories as $one ) {
				echo '<option value="' . esc_attr( $one->id ) . '" id="' . esc_attr( $one->id ) . '" '. selected( $select, $one->id, false ) . '>'. $one->name . '</option>';
			}			
			?>
			</select>
		</p>

	<?php }

	// Update widget settings
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']    = isset( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['textarea'] = isset( $new_instance['textarea'] ) ? wp_kses_post( $new_instance['textarea'] ) : '';
		$instance['select']   = isset( $new_instance['select'] ) ? wp_strip_all_tags( $new_instance['select'] ) : '';
		$instance['number']   = absint( $new_instance['number'] );
		$instance['type']     = $new_instance['type'];		//latest vs category
		return $instance;
	}

	// Display the widget
	public function widget( $args, $instance ) {
		extract( $instance );
		extract( $args );

		// Check the widget options
		$title    = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
		$textarea = isset( $instance['textarea'] ) ?$instance['textarea'] : '';
		$select   = isset( $instance['select'] ) ? $instance['select'] : '';
		$number   = empty( $instance['number'] ) ? 4 : $instance['number'];
		$type     = isset( $instance['type'] ) ? $instance['type'] : 'latest';		//latest vs category
		
		// Greb the settings in the wp admin site; wp-admin/admin.php?page=colormag_discourse_widget_admin
		$discourse_url = get_option('discourse_url'); //The discourse forum url
		$random_image = get_option('random_image'); //Do we want to use a random image or not "1" when it is checked
		$grayscale_random_image = get_option('grayscale_random_image'); //If selected then random images gets a grayscale effect
		$blur_random_image = get_option('blur_random_image'); //If selected then random images gets a blur effect
		
		// WordPress core before_widget hook (always include )
		echo $before_widget;
		
		?>
		<?php
						
			//First we need to get the forum list to get the title colors to match the colors set for the category in the forum
			$DiscourseCategoryUrl = $discourse_url . '/'. 'categories.json';  //alert at the moment it is very sensitive for the right path - eg https://studmed.dk work but not https://wwww.studmed.dk			
			$response = wp_remote_get($DiscourseCategoryUrl);		
			$body = wp_remote_retrieve_body( $response );
			$categoryjsondata = json_decode($body);
			
			//We need to trim the date from 2019-09-08T09:04:24.552Z ---> 13. august 2019
			$date_format = ! empty( $this->options['custom-datetime-format'] ) ? $this->options['custom-datetime-format'] : 'd. F Y'; 
			
			foreach( $categoryjsondata->category_list->categories as $one ) {
				if(  $one->id == $select) {
					$categorycolor = $one->color;
				}
			}
			if ( $type != 'latest' ) {  	// for categories get the color from the forum categories colortag
				$border_color = 'style="border-bottom-color:#'. $categorycolor . ';"';
				$title_color  = 'style="background-color:#'. $categorycolor . ';"';
			} else {	//for latest 
				$border_color = '';
				$title_color  = '';
			}
			if ( ! empty( $title ) ) { 	//let us get the widget title from the widget and color it based on the forum category settings
				echo '<h3 class="widget-title" ' . $border_color . '><span ' . $title_color . '>' . esc_html( $title ) . ' </span></h3>';
			}
			if ( ! empty( $textarea ) ) {	//let us get the widget description from the widget if there is any
				?> <p> <?php echo esc_textarea( $textarea ); ?> </p> <?php 
			} ?>
			
<?php		
			// Get data from from the forum either latest.json or from the category specific json
			if ( $type != 'latest' ) {  	// for categories get the data from the forum categories like: https://studmed.dk/c/studmed-debat.json
				$discourse_url_new = $discourse_url . '/c/'. $select . '.json';  //alert at the moment it is very sensitive for the right path - eg https://studmed.dk work but not https://wwww.studmed.dk
			} else {	//for latest 
				$discourse_url_new = $discourse_url . '/'. 'latest.json';  //alert at the moment it is very sensitive for the right path - eg https://studmed.dk work but not https://wwww.studmed.dk
			}
			
			$response = wp_remote_get($discourse_url_new);
			
			$body = wp_remote_retrieve_body( $response ) ;
			$data = json_decode($body);

			foreach( $data->topic_list->topics as $one ) {
				if ($i<$number) {
					if( $one->pinned === false ) {
						$created_at_formatted = mysql2date( $date_format, $one->created_at ); //we have to trim the date format
						if (strlen($one->title)>50) { // we need to trim the lenght of the title if it is to long
							$title_formatted = substr($one->title, 0, 50);  //limit the title lenght to 50 chars
							$title_formatted = $title_formatted . '..'; // we need to visualize that the lengt have been shooten
							} else {
							$title_formatted = $one->title;  //limit the title lenght to 55 chars
						}
						
						//Our random image path
						$picsum = 'https://picsum.photos';
						
						//Let us set the after effect of the images - either blur, grayscale, or none effect
						if ( empty($blur_random_image) ) {
							$blur = "";
						} else {
							$blur = "&blur=2"; // 2 indicates how much blur effect 1-10
						}
						if ( empty($grayscale_random_image) ) {
							$grayscale = "";
						} else {
							$grayscale = "&grayscale";
						}
						$image_effect = $grayscale.$blur;
						
						//Advance usage of picsum https://picsum.photos/390/205/?random=6786&grayscale&blur=2
						$random_image_scr_small = $picsum.'/130/90/?random='.$one->id.''.$image_effect;
						$random_image_scr_big = $picsum.'/390/205/?random='.$one->id.''.$image_effect;			

								  //We now wants to get the content from the topic as well. This is solved by the following-post
								  $discourseTopicUrl = $discourse_url.'/t/'.$one->slug.'/'.$one->id.'.json';
								  $response = wp_remote_get($discourseTopicUrl);								  
								  $body2 = wp_remote_retrieve_body( $response );
								  $data2 = json_decode($body2);		  
								  
								  foreach( $data2->post_stream->posts as $one2 ) {
										 if ($one2->post_number === 1) { //It will show the first post
											$content_formatted = htmlspecialchars(strip_tags(substr($one2->cooked, 0, 255)));
											$content_formatted = $content_formatted . '...';
										}
								  };							
								  												
					# code...
					if ( $i == 0 ) {
						echo '<div class="first-post">';
					} elseif ( $i == 1 ) {
						echo '<div class="following-post">';
					}
?>					<div class="single-article clearfix"><?php
					if ( $i == 0 ) {
						if (isset($one->image_url)) {
							// The post got an image
							echo '
								<figure>
									<a href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$one->title.'">
										<img width="390" height="205" src="'.$one->image_url.'" class="attachment-colormag-featured-post-medium size-colormag-featured-post-medium wp-post-image" alt="'.$one->title.'" title="'.$one->title.'" />
									</a>
								</figure>';
						} elseif (!empty($random_image) && empty($one->image_url)) {	
							// Random Image is activated and the post doesnt have an image 
							echo '
								<figure>
									<a href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$one->title.'">
										<img width="390" height="205" src="'.$random_image_scr_big.'" class="attachment-colormag-featured-post-medium size-colormag-featured-post-medium wp-post-image" alt="'.$one->title.'" title="'.$one->title.'" />
									</a>
								</figure>'; 
						} else {
							// Random Image is deactivated and the post doesnt have an image 
							echo '';
						};
						echo	'<div class="article-content">
									<div class="above-entry-meta">
										';
												$j=0;
												foreach ($one->posters as $poster) {
													foreach ($data->users as $user) {
														if ($poster->user_id == $user->id) {
															echo '<a href="'.$discourse_url.'/u/'.$user->username.'/summary/"><img alt="'.$user->username.'"  style="border-radius:50%;-moz-border-radius:50%; -webkit-border-radius:50%;-webkit-box-shadow: 0 1px 2px rgba(0,0,0,.1); -moz-box-shadow: 0 1px 2px rgba(0,0,0,.1);box-shadow: 0 1px 2px rgba(0,0,0,.1);" width="40" height="40" src="'.$discourse_url.''.str_replace("{size}", "64", $user->avatar_template).'"/></a>';
														}
													}
												$j++;
												}
											echo '					
									</div>
									<h3 class="entry-title">
										<a href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$one->title.'">'.$title_formatted.'</a>
									</h3>
									<div class="below-entry-meta">
										<span class="posted-on">
											<a href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$created_at_formatted.'" rel="bookmark">
												<i class="fa fa-calendar-o"></i> 
													<time class="entry-date published updated" datetime="'.$one->created_at.'">'.$created_at_formatted.'</time>
											</a>
										</span>
										<span class="byline">
											<span class="author vcard">
												<i class="fa fa-user"></i>
												<a class="url fn n" href="'.$discourse_url.'/u/'.$one->last_poster_username.'/summary/" title="'.$one->last_poster_username.'">'.$one->last_poster_username.'</a>
											</span>
										</span>';
										if ($one->like_count<1) {  //if there is zero likes we use font awesome 4.7
										echo '	<span class="byline">
													<span class="author vcard">
														<i class="fa fa-heart-o"></i>
														<a class="url fn n" href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$one->title.'">'.$one->like_count.'</a>
													</span>
												</span>';
											} else { // if there is more than one like
										echo '	<span class="byline">
													<span class="author vcard">
														<i class="fa fa-heart"></i>
														<a class="url fn n" href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$one->title.'">'.$one->like_count.'</a>
													</span>
												</span>';
										};
										if ($one->posts_count<1) {
										echo '	<span class="comments">
													<i class="fa fa-comment-o"></i>
													<a href="'.$discourse_url.'/t/'.$one->slug.'/'.$one->id.'/'.$one->highest_post_number.'">'.$one->posts_count.'</a>
												</span>';
											} else {
										echo '	<span class="comments">
													<i class="fa fa-comment"></i>
													<a href="'.$discourse_url.'/t/'.$one->slug.'/'.$one->id.'/'.$one->highest_post_number.'">'.$one->posts_count.'</a>
												</span>';
										};
								echo '	<span class="byline">
											<span class="author vcard">
												<i class="fa fa-eye"></i>
												<a class="url fn n" href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$one->title.'">'.$one->views.'</a>
											</span>
										</span>
									</div>
									<div class="entry-content">
										<p>'.$content_formatted.'</p>
									</div>
								</div>
							</div>
						</div>							
						';
						} else {
						if (isset($one->image_url)) {
							// The post got an image
							echo '
								<figure>
									<a href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$one->title.'">
										<img width="130" height="90" src="'.$one->image_url.'" class="attachment-colormag-featured-post-medium size-colormag-featured-post-medium wp-post-image" alt="'.$one->title.'" title="'.$one->title.'" />
									</a>
								</figure>';
						} elseif (!empty($random_image) && empty($one->image_url)) {	
							// Random Image is activated and the post doesnt have an image 
							echo '
								<figure>
									<a href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$one->title.'">
										<img width="130" height="90" src="'.$random_image_scr_small.'" class="attachment-colormag-featured-post-small size-colormag-featured-post-small wp-post-image" alt="'.$one->title.'" title="'.$one->title.'"  />
									</a>
								</figure>'; 
						} else {
							// Random Image is deactivated and the post doesnt have an image 
							echo '';
						};							
						echo '
							<div class="article-content">
								<h3 class="entry-title">
									<a href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$one->title.'">'.$title_formatted.'</a>
								</h3>
								<div class="below-entry-meta">
									<span class="posted-on">
										<a href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$created_at_formatted.'" rel="bookmark">
											<i class="fa fa-calendar-o"></i> 
												<time class="entry-date published updated" datetime="'.$one->created_at.'">'.$created_at_formatted.'</time>
										</a>
									</span>';
										if ($one->posts_count<1) {
										echo '	<span class="comments">
													<i class="fa fa-comment-o"></i>
													<a href="'.$discourse_url.'/t/'.$one->slug.'/'.$one->id.'/'.$one->highest_post_number.'">'.$one->posts_count.'</a>
												</span>';
											} else {
										echo '	<span class="comments">
													<i class="fa fa-comment"></i>
													<a href="'.$discourse_url.'/t/'.$one->slug.'/'.$one->id.'/'.$one->highest_post_number.'">'.$one->posts_count.'</a>
												</span>';
										};
										if ($one->like_count<1) {  //if there is zero likes we use font awesome 4.7
										echo '	<span class="comments">
													<span class="author vcard">
														<i class="fa fa-heart-o"></i>
														<a class="url fn n" href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$one->title.'">'.$one->like_count.'</a>
													</span>
												</span>';
											} else { // if there is more than one like
										echo '	<span class="comments">
													<span class="author vcard">
														<i class="fa fa-heart"></i>
														<a class="url fn n" href="'.$discourse_url.'/t/'.$one->slug.'" title="'.$one->title.'">'.$one->like_count.'</a>
													</span>
												</span>';
										};
						echo '				
								</div>
							</div>
						</div>						
						';
					}
					$i++;
					}
				}
			}
?>
<?php
		// WordPress core after_widget hook (always include )
		echo $after_widget;
	}
}

// Register the widget
function register_colormag_discourse_widget() {
	register_widget( 'Colormag_Discourse_Widget_Style_1' );
	register_widget( 'Colormag_Discourse_Widget_Style_2' );
}
add_action( 'widgets_init', 'register_colormag_discourse_widget' );

// ADD ADMIN PAGE IN THE SIDE BAR
//To control the siteurl we need a admin setting
function colormag_discourse_widget_admin_menu() {
	// Add a new top-level menu: Runners Log with Submenus
	add_menu_page('Colormag Discourse Widget', 'Colormag Discourse Widget', 'administrator', 'colormag_discourse_widget_admin', 'colormag_discourse_widget_admin', 'dashicons-text');
}
// Hook for adding admin menus
add_action('admin_menu', 'colormag_discourse_widget_admin_menu');

// Admin Options - Start adding the admin menu
function colormag_discourse_widget_admin() {  
	include('colormag-discourse-widget-admin.php');
	
/* STYLE 1 - OUTPUT TEMPLATE WE NEED TO MATCH
<section id="colormag_featured_posts_widget-3" class="widget widget_featured_posts widget_featured_meta clearfix">
	<h3 class="widget-title" style="border-bottom-color:#b17fe2;">
		<span style="background-color:#b17fe2;">Health</span>
	</h3>								
	
	<div class="first-post">			
		<div class="single-article clearfix">
		<figure>
			<a href="https://blog.studmed.dk/2015/03/24/coffee-is-health-food-myth-or-fact/" title="Coffee is health food: Myth or fact?">
				<img width="390" height="205" src="https://blog.studmed.dk/wp-content/uploads/2015/03/coffee-563797_1280-390x205.jpg" class="attachment-colormag-featured-post-medium size-colormag-featured-post-medium wp-post-image" alt="Coffee is health food: Myth or fact?" title="Coffee is health food: Myth or fact?" />
			</a>
		</figure>					
			<div class="article-content">
				<div class="above-entry-meta">
					<span class="cat-links">
						<a href="https://blog.studmed.dk/category/drinks/" style="background:#27f7f7" rel="category tag">Drinks</a>&nbsp;
						<a href="https://blog.studmed.dk/category/food/" style="background:#a38a6d" rel="category tag">Food</a>&nbsp;
						<a href="https://blog.studmed.dk/category/health/" style="background:#b17fe2" rel="category tag">Health</a>&nbsp;
					</span>
				</div>
				<h3 class="entry-title">
					<a href="https://blog.studmed.dk/2015/03/24/coffee-is-health-food-myth-or-fact/" title="Coffee is health food: Myth or fact?">Coffee is health food: Myth or fact?</a>
				</h3>
				<div class="below-entry-meta">
					<span class="posted-on">
						<a href="https://blog.studmed.dk/2015/03/24/coffee-is-health-food-myth-or-fact/" title="10:00" rel="bookmark">
							<i class="fa fa-calendar-o"></i> 
								<time class="entry-date published updated" datetime="2015-03-24T10:00:06+02:00">24. March 2015</time>
						</a>
					</span>
					<span class="byline">
						<span class="author vcard">
							<i class="fa fa-user"></i>
							<a class="url fn n" href="https://blog.studmed.dk/author/frold/" title="frold">frold</a>
						</span>
					</span>
					<span class="comments">
						<i class="fa fa-comment"></i>
						<a href="https://blog.studmed.dk/2015/03/24/coffee-is-health-food-myth-or-fact/#respond">0</a>
					</span>
				</div>
				<div class="entry-content">
					<p>Vivamus vestibulum ut magna vitae facilisis. Maecenas laoreet lobortis tristique. Aenean accumsan malesuada convallis. Suspendisse egestas luctus nisl, sit amet</p>
				</div>
			</div>
		</div>
	</div>
	
	<div class="following-post">
		<div class="single-article clearfix">
			<figure>
				<a href="https://blog.studmed.dk/2015/03/24/mosquito-borne-diseases-has-threaten-world/" title="Mosquito-borne diseases has threaten World">
					<img width="130" height="90" src="https://blog.studmed.dk/wp-content/uploads/2015/03/mosquito-542156_1280-130x90.jpg" class="attachment-colormag-featured-post-small size-colormag-featured-post-small wp-post-image" alt="Mosquito-borne diseases has threaten World" title="Mosquito-borne diseases has threaten World" srcset="https://blog.studmed.dk/wp-content/uploads/2015/03/mosquito-542156_1280-130x90.jpg 130w, https://blog.studmed.dk/wp-content/uploads/2015/03/mosquito-542156_1280-392x272.jpg 392w" sizes="(max-width: 130px) 100vw, 130px" />
				</a>
			</figure>
			<div class="article-content">
				<div class="above-entry-meta">
					<span class="cat-links">
						<a href="https://blog.studmed.dk/category/health/" style="background:#b17fe2" rel="category tag">Health</a>&nbsp;
						<a href="https://blog.studmed.dk/category/news/"  rel="category tag">News</a>&nbsp;
					</span>
				</div>
				<h3 class="entry-title">
					<a href="https://blog.studmed.dk/2015/03/24/mosquito-borne-diseases-has-threaten-world/" title="Mosquito-borne diseases has threaten World">Mosquito-borne diseases has threaten World</a>
				</h3>
				<div class="below-entry-meta">
					<span class="posted-on">
						<a href="https://blog.studmed.dk/2015/03/24/mosquito-borne-diseases-has-threaten-world/" title="9:57" rel="bookmark">
							<i class="fa fa-calendar-o"></i> 
							<time class="entry-date published updated" datetime="2015-03-24T09:57:18+02:00">24. March 2015</time>
						</a>
					</span>
					<span class="byline">
						<span class="author vcard">
							<i class="fa fa-user"></i><a class="url fn n" href="https://blog.studmed.dk/author/frold/" title="frold">frold</a>
						</span>
					</span>
					<span class="comments">
						<i class="fa fa-comment"></i>
							<a href="https://blog.studmed.dk/2015/03/24/mosquito-borne-diseases-has-threaten-world/#respond">0</a>
					</span>
				</div>
			</div>
		</div>	
		<div class="single-article clearfix">
			<figure>
				<a href="https://blog.studmed.dk/2015/03/24/solar-eclipse-eye-health-warning/" title="Solar eclipse: Eye health warning">
					<img width="130" height="90" src="https://blog.studmed.dk/wp-content/uploads/2015/03/solar-eclipse-152834_1280-130x90.png" class="attachment-colormag-featured-post-small size-colormag-featured-post-small wp-post-image" alt="Solar eclipse: Eye health warning" title="Solar eclipse: Eye health warning" srcset="https://blog.studmed.dk/wp-content/uploads/2015/03/solar-eclipse-152834_1280-130x90.png 130w, https://blog.studmed.dk/wp-content/uploads/2015/03/solar-eclipse-152834_1280-392x272.png 392w" sizes="(max-width: 130px) 100vw, 130px" />
				</a>
			</figure>
			<div class="article-content">
				<div class="above-entry-meta">
					<span class="cat-links">
						<a href="https://blog.studmed.dk/category/health/" style="background:#b17fe2" rel="category tag">Health</a>&nbsp;
						<a href="https://blog.studmed.dk/category/news/"  rel="category tag">News</a>&nbsp;
					</span>
				</div>
				<h3 class="entry-title">
					<a href="https://blog.studmed.dk/2015/03/24/solar-eclipse-eye-health-warning/" title="Solar eclipse: Eye health warning">Solar eclipse: Eye health warning</a>
				</h3>
				<div class="below-entry-meta">
					<span class="posted-on">
						<a href="https://blog.studmed.dk/2015/03/24/solar-eclipse-eye-health-warning/" title="9:43" rel="bookmark">
							<i class="fa fa-calendar-o"></i>
							<time class="entry-date published updated" datetime="2015-03-24T09:43:30+02:00">24. March 2015</time>
						</a>
					</span>
					<span class="byline">
						<span class="author vcard">
							<i class="fa fa-user"></i>
								<a class="url fn n" href="https://blog.studmed.dk/author/frold/" title="frold">frold</a>
						</span>
					</span>
					<span class="comments">
						<i class="fa fa-comment"></i>
							<a href="https://blog.studmed.dk/2015/03/24/solar-eclipse-eye-health-warning/#respond">0</a>
					</span>
				</div>
			</div>
		</div>
		<div class="single-article clearfix">
			<figure>
				<a href="https://blog.studmed.dk/2015/03/24/get-more-nutrition-in-every-bite/" title="Get more nutrition in every bite">
					<img width="130" height="90" src="https://blog.studmed.dk/wp-content/uploads/2015/03/yummy-333666_1280-130x90.jpg" class="attachment-colormag-featured-post-small size-colormag-featured-post-small wp-post-image" alt="Get more nutrition in every bite" title="Get more nutrition in every bite" srcset="https://blog.studmed.dk/wp-content/uploads/2015/03/yummy-333666_1280-130x90.jpg 130w, https://blog.studmed.dk/wp-content/uploads/2015/03/yummy-333666_1280-392x272.jpg 392w" sizes="(max-width: 130px) 100vw, 130px" /></a>
			</figure>
			<div class="article-content">
				<div class="above-entry-meta">
					<span class="cat-links">
						<a href="https://blog.studmed.dk/category/food/" style="background:#a38a6d" rel="category tag">Food</a>&nbsp;
						<a href="https://blog.studmed.dk/category/health/" style="background:#b17fe2" rel="category tag">Health</a>&nbsp;
					</span>
				</div>
				<h3 class="entry-title">
					<a href="https://blog.studmed.dk/2015/03/24/get-more-nutrition-in-every-bite/" title="Get more nutrition in every bite">Get more nutrition in every bite</a>
				</h3>
				<div class="below-entry-meta">
					<span class="posted-on"><a href="https://blog.studmed.dk/2015/03/24/get-more-nutrition-in-every-bite/" title="9:39" rel="bookmark">
						<i class="fa fa-calendar-o"></i>
						<time class="entry-date published updated" datetime="2015-03-24T09:39:31+02:00">24. March 2015</time></a>
					</span>
					<span class="byline">
						<span class="author vcard">
							<i class="fa fa-user"></i>
							<a class="url fn n" href="https://blog.studmed.dk/author/frold/" title="frold">frold</a>
						</span>
					</span>
					<span class="comments">
						<i class="fa fa-comment"></i>
						<a href="https://blog.studmed.dk/2015/03/24/get-more-nutrition-in-every-bite/#respond">0</a>
					</span>
				</div>
			</div>
		</div>
		<div class="single-article clearfix">
			<figure>
				<a href="https://blog.studmed.dk/2015/03/24/womens-relay-competition/" title="Women&#8217;s Relay Competition">
					<img width="130" height="90" src="https://blog.studmed.dk/wp-content/uploads/2015/03/relay-race-655353_1280-130x90.jpg" class="attachment-colormag-featured-post-small size-colormag-featured-post-small wp-post-image" alt="Women&#8217;s Relay Competition" title="Women&#8217;s Relay Competition" srcset="https://blog.studmed.dk/wp-content/uploads/2015/03/relay-race-655353_1280-130x90.jpg 130w, https://blog.studmed.dk/wp-content/uploads/2015/03/relay-race-655353_1280-392x272.jpg 392w" sizes="(max-width: 130px) 100vw, 130px" />
				</a>
			</figure>
			<div class="article-content">
				<div class="above-entry-meta">
					<span class="cat-links">
						<a href="https://blog.studmed.dk/category/entertainment/" style="background:#81d742" rel="category tag">Entertainment</a>&nbsp;
						<a href="https://blog.studmed.dk/category/female/" style="background:#b5b5b5" rel="category tag">Female</a>&nbsp;
						<a href="https://blog.studmed.dk/category/health/" style="background:#b17fe2" rel="category tag">Health</a>&nbsp;
						<a href="https://blog.studmed.dk/category/sports/"  rel="category tag">Sports</a>&nbsp;
					</span>
				</div>
			<h3 class="entry-title">
				<a href="https://blog.studmed.dk/2015/03/24/womens-relay-competition/" title="Women&#8217;s Relay Competition">Women&#8217;s Relay Competition</a>
			</h3>
			<div class="below-entry-meta">
				<span class="posted-on">
					<a href="https://blog.studmed.dk/2015/03/24/womens-relay-competition/" title="7:48" rel="bookmark">
						<i class="fa fa-calendar-o"></i>
						<time class="entry-date published updated" datetime="2015-03-24T07:48:42+02:00">24. March 2015</time>
					</a>
				</span>
				<span class="byline">
					<span class="author vcard">
						<i class="fa fa-user"></i>
							<a class="url fn n" href="https://blog.studmed.dk/author/frold/" title="frold">frold</a>
					</span>
				</span>
				<span class="comments">
					<i class="fa fa-comment"></i>
						<a href="https://blog.studmed.dk/2015/03/24/womens-relay-competition/#respond">0</a>
				</span>
			</div>
		</div>
	</div>
</div>		<!-- </div> -->
</section>
*/

/* Style 2
<section id="colormag_featured_posts_vertical_widget-5" class="widget widget_featured_posts widget_featured_posts_vertical widget_featured_meta clearfix">		
	<h3 class="widget-title" style="border-bottom-color:#82ada1;"><span style="background-color:#82ada1;">FASHION</span></h3>								
	<div class="first-post">			
		<div class="single-article clearfix">
			<figure>
				<a href="https://blog.studmed.dk/2015/03/24/looks-from-the-aowsana-2015/" title="Looks from the Roswana, 2015"><img width="390" height="205" src="https://blog.studmed.dk/wp-content/uploads/2015/03/model-600225_1280-390x205.jpg" class="attachment-colormag-featured-post-medium size-colormag-featured-post-medium wp-post-image" alt="Looks from the Roswana, 2015" title="Looks from the Roswana, 2015" /></a>
			</figure>
			<div class="article-content">
				<div class="above-entry-meta">
					<span class="cat-links">
						<a href="https://blog.studmed.dk/category/fashion/" style="background:#82ada1" rel="category tag">Fashion</a>&nbsp;
						<a href="https://blog.studmed.dk/category/female/" style="background:#b5b5b5" rel="category tag">Female</a>&nbsp;
						<a href="https://blog.studmed.dk/category/style/"  rel="category tag">Style</a>&nbsp;
					</span>
				</div>					
				<h3 class="entry-title">
					<a href="https://blog.studmed.dk/2015/03/24/looks-from-the-aowsana-2015/" title="Looks from the Roswana, 2015">Looks from the Roswana, 2015</a>
				</h3>
				<div class="below-entry-meta">
					<span class="posted-on">
						<a href="https://blog.studmed.dk/2015/03/24/looks-from-the-aowsana-2015/" title="6:31" rel="bookmark">
							<i class="fa fa-calendar-o"></i> 
								<time class="entry-date published updated" datetime="2015-03-24T06:31:54+02:00">24. March 2015</time>
						</a>
					</span>
					<span class="byline">
						<span class="author vcard">
							<i class="fa fa-user"></i>
								<a class="url fn n" href="https://blog.studmed.dk/author/frold/" title="frold">frold</a>
						</span>
					</span>
					<span class="comments">
						<i class="fa fa-comment"></i>
							<a href="https://blog.studmed.dk/2015/03/24/looks-from-the-aowsana-2015/#respond">0</a>
					</span>
				</div>
				<div class="entry-content">
					<p>For Joesendra, this is only her second fashion week showing, following her presentation at a Fashion World on this January.</p>
				</div>
			</div>
		</div>
	</div>									
	<div class="following-post">			
		<div class="single-article clearfix">
			<figure>
				<a href="https://blog.studmed.dk/2015/03/24/color-your-hair-3/" title="Color your Hair"><img width="130" height="90" src="https://blog.studmed.dk/wp-content/uploads/2015/03/beauty-666605_1920-130x90.jpg" class="attachment-colormag-featured-post-small size-colormag-featured-post-small wp-post-image" alt="Color your Hair" title="Color your Hair" srcset="https://blog.studmed.dk/wp-content/uploads/2015/03/beauty-666605_1920-130x90.jpg 130w, https://blog.studmed.dk/wp-content/uploads/2015/03/beauty-666605_1920-392x272.jpg 392w" sizes="(max-width: 130px) 100vw, 130px" /></a>
			</figure>			
			<div class="article-content">
				<div class="above-entry-meta">
					<span class="cat-links">
						<a href="https://blog.studmed.dk/category/fashion/" style="background:#82ada1" rel="category tag">Fashion</a>&nbsp;
						<a href="https://blog.studmed.dk/category/general/" style="background:#a3d886" rel="category tag">General</a>&nbsp;
					</span>
				</div>					
				<h3 class="entry-title">
					<a href="https://blog.studmed.dk/2015/03/24/color-your-hair-3/" title="Color your Hair">Color your Hair</a>
				</h3>
				<div class="below-entry-meta">
					<span class="posted-on">
						<a href="https://blog.studmed.dk/2015/03/24/color-your-hair-3/" title="6:27" rel="bookmark">
							<i class="fa fa-calendar-o"></i> 
								<time class="entry-date published updated" datetime="2015-03-24T06:27:32+02:00">24. March 2015</time></a>
					</span>						
					<span class="byline">
						<span class="author vcard">
							<i class="fa fa-user"></i>
								<a class="url fn n" href="https://blog.studmed.dk/author/frold/" title="frold">frold</a>
						</span>
					</span>
					<span class="comments">
						<i class="fa fa-comment"></i>
							<a href="https://blog.studmed.dk/2015/03/24/color-your-hair-3/#respond">0</a>
					</span>
				</div>
			</div>
		</div>
		<div class="single-article clearfix">
			<figure>
				<a href="https://blog.studmed.dk/2015/03/24/new-styling-collections/" title="New Styling Collections">
					<img width="130" height="90" src="https://blog.studmed.dk/wp-content/uploads/2015/03/window-213496_1280-130x90.jpg" class="attachment-colormag-featured-post-small size-colormag-featured-post-small wp-post-image" alt="New Styling Collections" title="New Styling Collections" srcset="https://blog.studmed.dk/wp-content/uploads/2015/03/window-213496_1280-130x90.jpg 130w, https://blog.studmed.dk/wp-content/uploads/2015/03/window-213496_1280-392x272.jpg 392w" sizes="(max-width: 130px) 100vw, 130px" /></a>
			</figure>				
			<div class="article-content">
				<div class="above-entry-meta">
					<span class="cat-links">
						<a href="https://blog.studmed.dk/category/fashion/" style="background:#82ada1" rel="category tag">Fashion</a>&nbsp;
						<a href="https://blog.studmed.dk/category/news/"  rel="category tag">News</a>&nbsp;
						<a href="https://blog.studmed.dk/category/style/"  rel="category tag">Style</a>&nbsp;
					</span>
				</div>					
				<h3 class="entry-title">
					<a href="https://blog.studmed.dk/2015/03/24/new-styling-collections/" title="New Styling Collections">New Styling Collections</a>
				</h3>
				<div class="below-entry-meta">
					<span class="posted-on">
						<a href="https://blog.studmed.dk/2015/03/24/new-styling-collections/" title="6:25" rel="bookmark">
							<i class="fa fa-calendar-o"></i> 
								<time class="entry-date published updated" datetime="2015-03-24T06:25:39+02:00">24. March 2015</time></a>
					</span>
					<span class="byline">
						<span class="author vcard">
							<i class="fa fa-user"></i>
								<a class="url fn n" href="https://blog.studmed.dk/author/frold/" title="frold">frold</a>
						</span>
					</span>
					<span class="comments">
						<i class="fa fa-comment"></i>
							<a href="https://blog.studmed.dk/2015/03/24/new-styling-collections/#respond">0</a>
					</span>
				</div>
			</div>
		</div>
		<div class="single-article clearfix">
			<figure>
				<a href="https://blog.studmed.dk/2015/03/24/consectetur-adipiscing/" title="Consectetur adipiscing">
					<img width="130" height="90" src="https://blog.studmed.dk/wp-content/uploads/2015/03/relaxed-498245_1280-130x90.jpg" class="attachment-colormag-featured-post-small size-colormag-featured-post-small wp-post-image" alt="Consectetur adipiscing" title="Consectetur adipiscing" srcset="https://blog.studmed.dk/wp-content/uploads/2015/03/relaxed-498245_1280-130x90.jpg 130w, https://blog.studmed.dk/wp-content/uploads/2015/03/relaxed-498245_1280-392x272.jpg 392w" sizes="(max-width: 130px) 100vw, 130px" /></a>
			</figure>
		<div class="article-content">
			<div class="above-entry-meta">
				<span class="cat-links">
					<a href="https://blog.studmed.dk/category/fashion/" style="background:#82ada1" rel="category tag">Fashion</a>&nbsp;
				</span>
			</div>
			<h3 class="entry-title">
				<a href="https://blog.studmed.dk/2015/03/24/consectetur-adipiscing/" title="Consectetur adipiscing">Consectetur adipiscing</a>
			</h3>
			<div class="below-entry-meta">
				<span class="posted-on">
					<a href="https://blog.studmed.dk/2015/03/24/consectetur-adipiscing/" title="6:22" rel="bookmark"><i class="fa fa-calendar-o"></i> 
					<time class="entry-date published updated" datetime="2015-03-24T06:22:35+02:00">24. March 2015</time></a>
				</span>
				<span class="byline">
					<span class="author vcard">
						<i class="fa fa-user"></i>
							<a class="url fn n" href="https://blog.studmed.dk/author/frold/" title="frold">frold</a>
					</span>
				</span>
				<span class="comments">
					<i class="fa fa-comment"></i>
						<a href="https://blog.studmed.dk/2015/03/24/consectetur-adipiscing/#respond">0</a>
				</span>
			</div>
		</div>
	</div>
</div>	
</section>
*/
}