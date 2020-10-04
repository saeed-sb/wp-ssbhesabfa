<?php

/**
 * @link              https://www.hesabfa.com/
 * @since             1.0.0
 * @package           ssbhesabfa
 *
 * @wordpress-plugin
 * Plugin Name:       Hesabfa Accounting
 * Plugin URI:        https://www.hesabfa.com/
 * Description:       Connect Hesabfa Online Accounting to WooCommerce.
 * Version:           1.1.4
 * Author:            Saeed Sattar Beglou
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       ssbhesabfa
 * Domain Path:       /languages
 * WC requires at least: 3.0.0
 * WC tested up to: 4.4.1
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 */
define('SSBHESABFA_VERSION', '1.1.4');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ssbhesabfa-activator.php
 */
function activate_ssbhesabfa() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ssbhesabfa-activator.php';
    Ssbhesabfa_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ssbhesabfa-deactivator.php
 */
function deactivate_ssbhesabfa() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ssbhesabfa-deactivator.php';
    Ssbhesabfa_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ssbhesabfa' );
register_deactivation_hook( __FILE__, 'deactivate_ssbhesabfa' );

/**
 * The core plugin class that is used to define internationalization and
 * admin-specific hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ssbhesabfa.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ssbhesabfa() {
	$plugin = new Ssbhesabfa();
	$plugin->run();
}

run_ssbhesabfa();
