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
	public function enqueue_scripts() {

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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/facebookpress-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );	

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
	 * Handle requests.
	 *
	 * @since    1.0.0
	 */
	public function request_handler() {

		if ( isset( $_REQUEST['code'] ) && !empty( $_REQUEST['code'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'fp-nonce' ) ){
			Facebookpress::update_option('auth_token', $_GET['code']);
		}

		if ( isset( $_REQUEST['revoke'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'fp-nonce' ) ){
			Facebookpress::update_option('auth_token', '');
		}
		
	}
}
