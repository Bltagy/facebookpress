<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://bltagy.com
 * @since      1.0.0
 *
 * @package    Facebookpress
 * @subpackage Facebookpress/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Facebookpress
 * @subpackage Facebookpress/includes
 * @author     Ahmed Bltagy <ahmed@bltagy.com>
 */
class Facebookpress {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Facebookpress_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {	
		session_start();
		
		$this->plugin_name = 'facebookpress';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Facebookpress_Loader. Orchestrates the hooks of the plugin.
	 * - Facebookpress_i18n. Defines internationalization functionality.
	 * - Facebookpress_Admin. Defines all hooks for the admin area.
	 * - Facebookpress_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-facebookpress-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-facebookpress-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-facebookpress-setting.php';



		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-facebookpress-admin.php';

	
		/**
		 * Facebook SDK autoloader.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/sdk/autoload.php';

		/**
		 * The class responsible for defining facebook app configuration.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-facebookpress-sdk.php';

		$this->loader = new Facebookpress_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Facebookpress_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Facebookpress_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Facebookpress_Admin( $this->get_plugin_name(), $this->get_version() );

		$plugin_setting = new FacebookpressSetting( $plugin_admin );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_setting, 'facebookpress_setting_add_plugin_page' );
		
		$this->loader->add_action( 'admin_menu', $plugin_setting, 'facebookpress_setting_add_plugin_page_2' );

		$this->loader->add_action( 'admin_init', $plugin_setting, 'facebookpress_setting_page_init' );

		$this->loader->add_action( 'init', $plugin_admin, 'request_handler' );

		$this->loader->add_action( 'wp_ajax_cat_select', $plugin_admin, 'cat_select_callback' );

		$this->loader->add_action( 'wp_ajax_run_importer', $plugin_admin, 'run_importer_callback' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Facebookpress_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieves option value based on the option name.
	 *
	 * @since     1.0.0
	 * @return    string    the option value.
	 */
	public static function get_option( $name, $default = false ) {
		$option = get_option( 'facebookpress_setting_option', true );

		if ( false === $option ) {
			return $default;
		}

		if ( isset( $option[$name] ) ) {
			return $option[$name];
		} else {
			return $default;
		}
	}

	/**
	 * Update FP option value based on the option name.
	 *
	 * @since     1.0.0
	 */
	public static function update_option( $name, $value ) {
		$option = get_option( 'facebookpress_setting_option', true );
		$option = ( false === $option ) ? array() : (array) $option;
		$option = array_merge( $option, array( $name => $value ) );
		update_option( 'facebookpress_setting_option', $option );
	}

	/**
	 * Update FP option value based on the option name.
	 *
	 * @since     1.0.0
	 */
	public static function term_data( $slug ) {
		$post_type = Facebookpress::get_option('choose_post_type');
		$post_cat = get_object_taxonomies( $post_type, 'names' )[0];
		if ( empty( $post_type ) ) return false;

		$term = get_term_by('slug', $slug, $post_cat);
		return $term; 
	}

}
