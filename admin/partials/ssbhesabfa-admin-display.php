<?php

/**
 * @class      Ssbhesabfa_Admin_Display
 * @version    1.1.6
 * @since      1.0.0
 * @package    ssbhesabfa
 * @subpackage ssbhesabfa/admin/display
 * @author     Saeed Sattar Beglou <saeed.sb@gmail.com>
 */

class Ssbhesabfa_Admin_Display {

    /**
    * Hook in methods
    * @since    1.0.0
    * @access   static
    */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'hesabfa_add_settings_menu'));
    }

    /**
    * @since    1.0.0
    * @access   public
    */
    public static function hesabfa_add_settings_menu() {
        add_options_page(__('Hesabfa Options', 'ssbhesabfa'), __('Hesabfa', 'ssbhesabfa'), 'manage_options', 'ssbhesabfa-option', array(__CLASS__, 'ssbhesabfa_option'));
    }

    /**
    * @since    1.0.0
    * @access   public
    */
    public static function ssbhesabfa_option() {
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $setting_tabs = apply_filters('ssbhesabfa_setting_tab', array(
                'home' => __('Home', 'ssbhesabfa'),
                'api' => __('API', 'ssbhesabfa'),
                'catalog' => __('Catalog', 'ssbhesabfa'),
                'customers' => __('Customers', 'ssbhesabfa'),
                'invoice' => __('Invoice', 'ssbhesabfa'),
                'payment' => __('Payment Methods', 'ssbhesabfa'),
                'export' => __('Export', 'ssbhesabfa'),
                'sync' => __('Sync', 'ssbhesabfa')
            ));
            $current_tab = (isset($_GET['tab'])) ? wc_clean($_GET['tab']) : 'home';
            ?>
            <h2 class="nav-tab-wrapper">
                <?php
                foreach ($setting_tabs as $name => $label)
                    echo '<a href="' . admin_url('admin.php?page=ssbhesabfa-option&tab=' . $name) . '" class="nav-tab ' . ($current_tab == $name ? 'nav-tab-active' : '') . '">' . $label . '</a>';
                ?>
            </h2>
            <?php
            foreach ($setting_tabs as $setting_tabkey => $setting_tabvalue) {
                switch ($setting_tabkey) {
                    case $current_tab:
                        do_action('ssbhesabfa_' . $setting_tabkey . '_setting_save_field');
                        do_action('ssbhesabfa_' . $setting_tabkey . '_setting');
                        break;
                }
            }
        } else {
            echo '<div class="wrap">' . __('Hesabfa Plugin requires the WooCommerce to work!, Please install/activate woocommerce and try again', 'ssbhesabfa') . '</div>';
        }
    }
}

Ssbhesabfa_Admin_Display::init();
