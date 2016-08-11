<?php

/**
 * @link              https://seankennedy.com.au/
 * @since             1.0.0
 * @package           Shortcode_Inserter
 *
 * @wordpress-plugin
 * Plugin Name:       Shortcode Inserter
 * Plugin URI:        https://github.com/sean-kennedy/shortcode-inserter/
 * Description:       A TinyMCE button and shortcode loader for easily inserting custom shortcodes. Auto detection and loading of shortcodes from a theme directory with auto generated tinyMCE button.
 * Version:           1.0.1
 * Author:            Sean Kennedy
 * Author URI:        https://seankennedy.com.au/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       shortcode-inserter
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plugin update checker
 */
require 'plugin-update-checker/plugin-update-checker.php';

$plugin_updater = PucFactory::getLatestClassVersion('PucGitHubChecker');

$shortcode_inserter_update_checker = new $plugin_updater(
    'https://github.com/sean-kennedy/shortcode-inserter/',
    __FILE__,
    'master'
);

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-shortcode-inserter.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_shortcode_inserter() {
	
	define( 'SHORTCODE_INSERTER_PLUGIN_DIR_ROOT', plugin_dir_path( __FILE__ ) );
	define( 'SHORTCODE_INSERTER_PLUGIN_URI_ROOT', plugin_dir_url( __FILE__ ) );

	$plugin = new Shortcode_Inserter();
	$plugin->run();

}
run_shortcode_inserter();
