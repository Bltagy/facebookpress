<?php

/**
 * The plugin bootstrap file
 *
 * 	
 * @link              http://bltagy.com
 * @since             1.0.0
 * @package           Facebookpress
 *
 * @wordpress-plugin
 * Plugin Name:       FacebookPress
 * Plugin URI:        http://bltagy.com/facepress
 * Description:       Allows you to import the feed of a public page or profile on your website as post or custom post type.
 * Version:           1.0.0
 * Author:            Ahmed Bltagy
 * Author URI:        http://bltagy.com
 * License:           GPL-3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       facebookpress
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-facebookpress.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_facebookpress() {

	$plugin = new Facebookpress();
	$plugin->run();

}
run_facebookpress();
