<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.mattcromwell.com
 * @since             1.0.0
 * @package           Mc_Functionality
 *
 * @wordpress-plugin
 * Plugin Name:       MC Functionality
 * Plugin URI:        https://www.mattcromwell.com/mc-functionality
 * Description:       A functionality plugin you can build within WP Admin. Think "Code Snippets" but better performance and security. 
 * Version:           1.0.0
 * Author:            Matt Cromwell
 * Author URI:        https://www.mattcromwell.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mc-functionality
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MC_FUNCTIONALITY_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-mc-functionality-activator.php
 */
function activate_mc_functionality() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mc-functionality-activator.php';
	Mc_Functionality_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-mc-functionality-deactivator.php
 */
function deactivate_mc_functionality() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mc-functionality-deactivator.php';
	Mc_Functionality_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_mc_functionality' );
register_deactivation_hook( __FILE__, 'deactivate_mc_functionality' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-mc-functionality.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_mc_functionality() {

	$plugin = new Mc_Functionality();
	$plugin->run();

}
run_mc_functionality();
