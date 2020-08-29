<?php

/**
 * @author     Saeed Sattar Beglou <saeed.sb@gmail.com>
 * @since      1.0.0
 *
 * @package    ssbhesabfa
 */

// If uninstall not called from WordPress, then exit.
if (!defined( 'WP_UNINSTALL_PLUGIN')) {
	exit;
}

global $wpdb;
$options = $wpdb->get_results("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '%ssbhesabfa%'");
foreach ($options as $option) {
    delete_option($option->option_name);
}

$wpdb->delete($wpdb->prefix.'ssbhesabfa');