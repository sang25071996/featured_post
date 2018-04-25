<?php
/**
 * Plugin Name: Featured-post
 * Plugin URI: efe.com.vn
 * Description:featured post
 * Version: 1
 * Author: sang
 * Author UR:efe.com.vn
 * License: GPLv2
 */
		add_action( 'admin_enqueue_scripts', 'wpa_style_featured_post' );
		function wpa_style_featured_post() {
			wp_enqueue_style( 'wp-analytify-style', plugins_url('style/css/bootstrap.css', __FILE__));
			wp_enqueue_style( 'wp-analytify-style', plugins_url('style/style.css', __FILE__));
		}
		//them menu
		add_action('admin_menu', 'menu_featuredPost');
		function menu_featuredPost(){
			add_menu_page('featured-id','Feature Post','2','Feature Post','feature_post');
		}
		function feature_post() {?>
			<?php $categories =  get_categories();?>
			<br/>
				<select class="form-control" id="select" style="margin-top: 10px;">
					<option value="">All Category</option>
					<?php foreach ($categories as $key) { ?>
					<option value="<?php echo $key->slug ?>"><?php echo $key->cat_name ?></option>
					<?php	} ?>
				</select><br/>
			
				<select class="form-control" id="show">
				  <option value="list" selected>List</option>
				  <option value="grid">Grid</option>
				  <option value="slide">Slide</option>
				</select><br/>
				<input id="shortcode" type="submit" class="btn btn-success" value="ShortCode">
				<br/>
				<input id="short" type="text" class="form-control" style="margin-top: 10px;">
				<script type="text/javascript">
					 jQuery(document).ready(function($) {
					 	$("#shortcode").click(function(){
					 		$tr = $("#select").val();
					 		$th = $("#show").val();
					 		$("#short").val('[shortcode category= "'+$tr+'" method="'+$th+'"]');
					 	});
					 });
				</script>
			<?php 
		}
		//tao meta box trong post
		add_action( 'add_meta_boxes', 'feature_meta_box' );
		function feature_meta_box()
		{
				add_meta_box( 'thong-tin', 'Feature post','featured_callback','post');
		}
		//tao checkbox trong post
		function featured_callback($post){ 
			$value = get_post_meta($post->ID,'featured_posts', true );
			?>
			<span>Featured Post</span>
			<input type="checkbox" name="check" <?php echo ($value == 'on') ? 'checked' : '' ?>>
		<?php }
		add_action( 'save_post', 'save_postdata' );
		/* luu bai viet vao csdl khi check*/
		function save_postdata($post_id)
		{
			$mydata = sanitize_text_field($_POST['check']);
			update_post_meta($post_id, 'featured_posts', $mydata);
		}
		/* them cot feature post*/
	add_filter('manage_posts_columns', 'my_columns');
	function my_columns($columns) {
		$columns['column'] = 'Feature post';
		return $columns;
		}
		add_action('manage_posts_custom_column',  'my_show_columns');
	function my_show_columns($name) {
		global $post;
			switch ($name) {
			    case 'column':
			        $value = get_post_meta($post->ID,'featured_posts', true ); ?>
			        <input type="checkbox" class="check_post" value="<?php echo $post->ID ?>" <?php echo ($value == 'on') ? 'checked' : '' ?> >
			    <?php }
			}
			/* xử lý ajax */
	add_action('wp_ajax_check_featured_post','check_featured_post');
	function check_featured_post() 
	{
		if (isset($_POST['id']))
		{
			$Check = get_post_meta($_POST['id'], 'featured_posts', true);
			if($Check === 'on')
			{
				update_post_meta($_POST['id'], 'featured_posts', '');
			}else 
			{
				update_post_meta($_POST['id'], 'featured_posts', 'on');
			}
		}
	}
			/* luu gia tri */
	add_action('admin_footer','featured_post_ajax');
	function featured_post_ajax() {?>
		<script>
		    jQuery(document).ready(function($) {
		        $('.check_post').click(function(){
		            var id = jQuery(this).val();
		            jQuery.ajax({
		                url: ajaxurl,
		                type: 'post',
		                data: {
		                        'action': 'check_featured_post',
		                        'id': id
		                    },
		                success: function (data) {
		                        // alert(id)
		                    },
		                    error: function (errorThrown) {
		                    
		                    }
		                });
		              });
		    });
		</script>
	<?php }
	/* tạo short code */
		add_shortcode( 'shortcode','create_shortcode' );
		function create_shortcode($option){
			if ($option['method'] == 'grid') {
				
				$args = array(
				    'posts_per_page'   => -1,
				    'post_type'        => 'post',
				    'meta_key' => 'featured_posts',
				    'meta_value' => 'on',
				    'category_name'    => ''
				);
				$the_query = new WP_Query( $args ); ?>

				<?php if ( $the_query->have_posts() ) : ?>
					<div class="row">
						<div class="col-md-12 col-sm-12 col-xs-12" style="text-align: center;">
						<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>

									<div class="col-md-3">
										<div class="item">
							                <div style="background-image: url('<?php echo the_post_thumbnail_url(); ?> ');height: 300px;width:100%;background-position: center center;"></div>
							                <div class="info">
							                	<h2><?php  the_title(); ?></h2>
							                </div>
							            </div>
							        </div>
						<?php endwhile; ?>
						</div>
					</div>
				<?php wp_reset_postdata(); ?>

				<?php else : ?>
					<p><?php esc_html_e( 'no post' ); ?></p>
				<?php endif;
			}elseif ($option['method'] == 'slide') {
				create_slide($option);
			}else{
				create_list($option);
			}
			
		 }

		 function create_slide($option){
		 	global $post;
			$args = array(
				'posts_per_page'   => -1,
			    'post_type'        => 'post',
			    'meta_key' => 'featured_posts',
			    'meta_value' => 'on',
			    'category_name'    => $option['category']
				);
			$posts = new WP_Query( $args );
			?>
			<div class="row">
				<?php while ( $posts->have_posts() ) : $posts->the_post(); ?>
				<div class="col-md-4 col-sm-6 col-xs-12" style="text-align: center;">
					<div class="item">
		                <div style="background-image: url('<?php echo the_post_thumbnail_url(); ?>');height: 400px;border-radius: 50% 50%;background-position: center center;"></div>
		                <div class="info">
		                	<h2><?php  the_title(); ?></h2>
		                </div>
		            </div>
		        </div>
			 	<?php endwhile;?>
			 	<?php wp_reset_postdata(); ?>
		 	</div>
		 	<?php	
		 }

		 function create_list($option){
		 	global $post;
			$args = array(
				'posts_per_page'   => -1,
			    'post_type'        => 'post',
			    'meta_key' => 'featured_posts',
			    'meta_value' => 'on',
			    'category_name'    => $option['category']
				);
			$posts = new WP_Query( $args );
			?>
			<div class="row">
				<?php while ( $posts->have_posts() ) : $posts->the_post(); ?>
				<div class="col-md-12 col-sm-12 col-xs-12" style="text-align: center;">
					<div class="item">
		                <div style="background-image: url('<?php echo the_post_thumbnail_url(); ?>');height: 200px;"></div>
		                <div class="info">
		                	<h2><?php  the_title(); ?></h2>
		                </div>
		            </div>
		        </div>
			 	<?php endwhile;?>
			 	<?php wp_reset_postdata(); ?>
		 	</div>
		 	<?php	
		 }
	?>