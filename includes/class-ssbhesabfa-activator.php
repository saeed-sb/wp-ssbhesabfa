<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @class      Ssbhesabfa_Activator
 * @version    1.0.6
 * @since      1.0.0
 * @package    ssbhesabfa
 * @subpackage ssbhesabfa/includes
 * @author     Saeed Sattar Beglou <saeed.sb@gmail.com>
 */
class Ssbhesabfa_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        add_option('ssbhesabfa_webhook_password', bin2hex(openssl_random_pseudo_bytes(16)));
        add_option('ssbhesabfa_last_log_check_id', 0);
        add_option('ssbhesabfa_live_mode', 0);
        add_option('ssbhesabfa_contact_address_status', 1);
        add_option('ssbhesabfa_contact_node_family', 'مشتریان فروشگاه آن‌لاین');

        self::ssbhesabfaـcreate_database_table();
	}

    private function ssbhesabfaـcreate_database_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "ssbhesabfa";
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "
            CREATE TABLE IF NOT EXISTS `$table_name` (
                `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `obj_type` varchar(32) NOT NULL,
                `id_hesabfa` int(11) UNSIGNED NOT NULL,
                `id_ps` int(11) UNSIGNED NOT NULL,
                PRIMARY KEY  (`id`)
            ) $charset_collate;
        ";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
