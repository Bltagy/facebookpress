<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://bltagy.com
 * @since      1.0.0
 *
 * @package    Facebookpress
 * @subpackage Facebookpress/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Facebookpress
 * @subpackage Facebookpress/admin
 * @author     Ahmed Bltagy <ahmed@bltagy.com>
 */
class Facebookpress_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Facebook SDK calss.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	public $sdk;



	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->sdk = new Facebookpress_SDK();

	}


	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {

		// Target a Specific Admin Page
		if ( 'facebookpress_page_facebookpress-setting-run' != $hook 
			&& 'toplevel_page_facebookpress-setting' != $hook )
			 return;
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Facebookpress_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Facebookpress_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/facebookpress-admin.css');

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/facebookpress-admin.js', array( 'jquery' ), $this->version, false );

		wp_enqueue_script( 'jquery-ui-progressbar');


		// Calculate progress bar step
		$wanted_count = ( Facebookpress::get_option('wanted_count') ) ? $post_type = Facebookpress::get_option('wanted_count') : 100;

		$step = ceil( 100 / ($wanted_count / 10) );

		wp_localize_script( $this->plugin_name, 'ajax_object', 
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'secure_ajax' => wp_create_nonce( "fp-ajax-nonce" ),
				'step' => $step
			) );	

	}	


	/**
	 * Retrieve post types.
	 *
	 * @since    1.0.0
	 */
	public function get_post_types() {
		$types = get_post_types();
		unset( $types['attachment'] );
		unset( $types['revision'] );
		unset( $types['nav_menu_item'] );
		return $types;
		
	}

	/**
	 * Retrieve post type categories.
	 *
	 * @since    1.0.0
	 * @return array terms
	 */
	public function get_post_cats( $post_type ) {
		$post_cat = get_object_taxonomies( $post_type, 'names' )[0];
		if ( empty( $post_cat ) ) return false;
		$post_terms = get_terms( array(
		    'taxonomy' => $post_cat,
		    'hide_empty' => false,
		) );
		$terms = array();
		foreach ($post_terms as $term) {
			$terms_array = array();
			$terms_array['term_id'] = $term->term_id;
			$terms_array['name'] = $term->name;
			$terms_array['slug'] = $term->slug;
			$terms[] = $terms_array;
		}

		return $terms;
	}

	/**
	 * Handle AJAX requests.
	 *
	 * @since    1.0.0
	 */
	public function cat_select_callback() {
		if ( !isset( $_REQUEST['type'] ) || empty( $_REQUEST['type'] ) ) wp_send_json_error();

		$post_type = $_REQUEST['type'];
		$terms = $this->get_post_cats( $post_type );
		if ( !empty( $terms ) )
			wp_send_json_success( $terms );

		wp_send_json_error();
	}

	/**
	 * Handle AJAX requests.
	 *
	 * @since    1.0.0
	 */
	public function run_importer_callback() {

		check_ajax_referer( 'fp-ajax-nonce', 'security' );
		$data = $_POST;
		$offset = ( ! isset( $data['offset'] ) || empty( $data['offset'] ) ) ? 0 : $data['offset'];
		$return_offset = $this->run_impoter( $offset );

		if ( $return_offset )
			wp_send_json_success( $return_offset );

		wp_send_json_error();
	}

	/**
	 * Handle requests.
	 *
	 * @since    1.0.0
	 */
	public function request_handler() {

		if ( isset( $_REQUEST['code'] ) && !empty( $_REQUEST['code'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'fp-nonce' ) && !isset($_POST['submit']) ){
			$token = $this->sdk->verify_token();
			Facebookpress::update_option('auth_token', $token);
			wp_redirect(admin_url( 'admin.php?page=facebookpress-setting'));
		}

		if ( isset( $_REQUEST['revoke'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'fp-nonce' ) ){
			Facebookpress::update_option('auth_token', '');
		}

		if ( isset( $_REQUEST['fp_action'] ) && $_REQUEST['fp_action'] == 'run_impoter' ){
			// $this->run_impoter();
		}
		
	}

	/**
	 * Run the importer
	 *
	 * @since    1.0.0
	 */
	public function run_impoter( $offset = 0 ) {
		$wanted_count = ( Facebookpress::get_option('wanted_count') ) ? $post_type = Facebookpress::get_option('wanted_count') : 100;
		$cache_data = get_transient( 'fp_ajax_data' );

		$fb_feed = ( ! $cache_data ) ? $fb_feed = $this->sdk->get_feed() : $cache_data;

		$count = 0;
		foreach ($fb_feed['data'] as $key => $post) {

			$count++; 

			$type = ( ! isset( $post['attachments']['data'][0]['type'] ) ) ? $post['type'] : $post['attachments']['data'][0]['type'] ;

			if ($type == 'status'){
				$type = 'post';
			}
			switch ( $type ) {
				case 'photo':
					$this->wp_insert_post( $post, 'post' );
					break;
				
				case 'album':
					$this->wp_insert_post( $post, 'album' );
					break;
				
				case 'event':
					$this->wp_insert_post( $post, 'event' );
					break;
				
				case 'video_inline':
					$this->wp_insert_post( $post, 'video' );
					break;
			}
			unset( $fb_feed['data'][$key] );

			if ( $count == 10 ){
				if ( empty($fb_feed['data']) ){

					delete_transient('fp_ajax_data');
					// debug($fb_feed);die;
					return false;

				}else{

					set_transient( 'fp_ajax_data', $fb_feed, 300 );
					// debug($fb_feed);die;
					return true;
					
				}
				break;
			}
		}
		return true;
	}

	/**
	 * Insert the fb feed data into the WP
	 * @param  array $feed_data 
	 * @param  string $feed_type
	 * @since 	1.0.0 
	 */
	public function wp_insert_post( $feed_data, $feed_type ){
		$post_type = Facebookpress::get_option('choose_post_type');

		$post_cat = Facebookpress::get_option('choose_category');

		$post_status = ( empty( Facebookpress::get_option('post_status') ) ) ? 'publish' : Facebookpress::get_option('post_status');

		$import_images = Facebookpress::get_option('import_images');

		$wp_post_type = $post_type[ $feed_type ];

		$category = $post_cat[ $feed_type ];
		if ( !isset( $feed_data['name'] ) ){
			
			$post_title = $feed_data['name'];
			
		}elseif( isset($feed_data['name']) && ( $feed_data['name'] == 'Timeline Photos' 
			|| strpos( $feed_data['name'] , 'cover photo') !== false 
			|| strpos( $feed_data['name'] , 'Photos from') !== false )
			 ){

			$post_title = wp_trim_words( $feed_data['message'], 6 );

		}else{

			$post_title = $feed_data['name'];

		}
		$content = ( isset( $feed_data['message'] ) ) ? $feed_data['message'] : "&nbsp;";
		$post_data = array(
		  'post_title'    => wp_strip_all_tags( $post_title ),
		  'post_content'  => $content,
		  'post_type' 	  => $wp_post_type,
		  'post_status'   => $post_status,
		  'post_category' => array( $category )
		);
		$id = wp_insert_post( $post_data );



		if ( !empty( $import_images ) ){
			if ( $feed_type == 'album' ){
				foreach ($feed_data['attachments']['data'][0]['subattachments']['data'] as $key => $media) {
						$img_url = trim($media['media']['image']['src']);

						$this->generate_featured_image( $img_url, $id, $key );
				}
			}else{
				$img_url = $feed_data['attachments']['data'][0]['media']['image']['src'];
				$this->generate_featured_image( $img_url, $id );
			}
		}

		if ( $feed_type == 'video' )
			$this->insert_video( $feed_data, $id );
	}

	/**
	 * upload and assign image to a post
	 * @param  string $image_url 
	 * @param  int $post_id
	 * @since 1.0.0
	 */
	public function generate_featured_image($image_url, $post_id, $key=0) {
	    $upload_dir = wp_upload_dir();
	    $image_data = file_get_contents($image_url);
	    $filename = basename($image_url);
	    $name = explode('?oh=', $filename);
	    $filename = $name[0];
	    // debug($filename);die;
	    if (wp_mkdir_p($upload_dir['path'])) {
	        $file = $upload_dir['path'] . '/' . $filename;
	    } else {
	        $file = $upload_dir['basedir'] . '/' . $filename;
	    }

	    file_put_contents($file, $image_data);

	    $wp_filetype = wp_check_filetype($filename, null);
	    $attachment = array(
	        'post_mime_type' => $wp_filetype['type'],
	        'post_title' => sanitize_file_name($filename),
	        'post_content' => '',
	        'post_status' => 'inherit',
	    );

	    $attach_id = wp_insert_attachment($attachment, $file, $post_id);

	    require_once ABSPATH . 'wp-admin/includes/image.php';

	    $attach_data = wp_generate_attachment_metadata($attach_id, $file);

	    $res1 = wp_update_attachment_metadata($attach_id, $attach_data);
	    if ( $key == 0 ){
	    	$res2 = set_post_thumbnail($post_id, $attach_id);
	    }else{
	    	$img_tag = wp_get_attachment_image( $attach_id, 'full', "", array( "class" => "img-responsive" ) );
	    	$content_post = get_post($post_id);
	    	$new_content = $content_post->post_content.$img_tag;
		    $args = array(
			      'ID'           => $post_id,
			      'post_content' => $new_content,
			  );
			// Update the post into the database
			wp_update_post( $args );	    	
	    }
	}

	public function insert_video( $video_data, $post_id ){

		$video_id = $video_data['attachments']['data'][0]['target']['id'];

		$video_width = $video_data['attachments']['data'][0]['media']['image']['width'];

		$video_height = $video_data['attachments']['data'][0]['media']['image']['height'];

		$content_post = get_post($post_id);

		$embed_html = '<iframe src="https://www.facebook.com/video/embed?video_id='.$video_id.'" width="'.$video_width.'" height="'.$video_height.'"  frameborder="0"></iframe>';

	    $new_content = $content_post->post_content.$embed_html;
		$args = array(
		     'ID'           => $post_id,
		      'post_content' => $new_content,
		 );
		wp_update_post( $args );	    	
	}

}
