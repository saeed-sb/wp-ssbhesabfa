<?php

/**
 * @class      Ssbhesabfa_Setting
 * @version    1.0.6
 * @since      1.0.0
 * @package    ssbhesabfa
 * @subpackage ssbhesabfa/admin/setting
 * @author     Saeed Sattar Beglou <saeed.sb@gmail.com>
 */

class Ssbhesabfa_Setting {

    /**
     * Hook in methods
     * @since    1.0.0
     * @access   static
     */
    public static function init() {
        add_action('ssbhesabfa_home_setting', array(__CLASS__, 'ssbhesabfa_home_setting'));

        add_action('ssbhesabfa_catalog_setting', array(__CLASS__, 'ssbhesabfa_catalog_setting'));
        add_action('ssbhesabfa_catalog_setting_save_field', array(__CLASS__, 'ssbhesabfa_catalog_setting_save_field'));

        add_action('ssbhesabfa_customers_setting', array(__CLASS__, 'ssbhesabfa_customers_setting'));
        add_action('ssbhesabfa_customers_setting_save_field', array(__CLASS__, 'ssbhesabfa_customers_setting_save_field'));

        add_action('ssbhesabfa_invoice_setting', array(__CLASS__, 'ssbhesabfa_invoice_setting'));
        add_action('ssbhesabfa_invoice_setting_save_field', array(__CLASS__, 'ssbhesabfa_invoice_setting_save_field'));

        add_action('ssbhesabfa_payment_setting', array(__CLASS__, 'ssbhesabfa_payment_setting'));
        add_action('ssbhesabfa_payment_setting_save_field', array(__CLASS__, 'ssbhesabfa_payment_setting_save_field'));

        add_action('ssbhesabfa_api_setting', array(__CLASS__, 'ssbhesabfa_api_setting'));
        add_action('ssbhesabfa_api_setting_save_field', array(__CLASS__, 'ssbhesabfa_api_setting_save_field'));

        add_action('ssbhesabfa_export_setting', array(__CLASS__, 'ssbhesabfa_export_setting'));

        add_action('ssbhesabfa_sync_setting', array(__CLASS__, 'ssbhesabfa_sync_setting'));
    }

    public static function ssbhesabfa_home_setting() {
        ?>
        <h1><?php esc_attr_e('Hesabfa Accounting', 'ssbhesabfa'); ?></h1>
        <p><?php esc_attr_e('This module helps connect your (online) store to Hesabfa online accounting software. By using this module, saving products, contacts, and orders in your store will also save them automatically in your Hesabfa account. Besides that, just after a client pays a bill, the receipt document will be stored in Hesabfa as well. Of course, you have to register your account in Hesabfa first. To do so, visit Hesabfa at the link here www.hesabfa.com and sign up for free. After you signed up and entered your account, choose your business, then in the settings menu/API, you can find the API keys for the business and import them to the plugin’s settings. Now your module is ready to use.', 'ssbhesabfa'); ?></p>
        <p><?php esc_attr_e('For more information and a full guide to how to use Hesabfa and WooCommerce Plugin, visit Hesabfa’s website and go to the “Accounting School” menu.', 'ssbhesabfa'); ?></p>
        <?php
    }


    public static function ssbhesabfa_catalog_setting_fields() {

        $fields[] = array('title' => __('Catalog Settings', 'ssbhesabfa'), 'type' => 'title', 'desc' => '', 'id' => 'catalog_options');

        $fields[] = array(
            'title' => __('Update Price', 'ssbhesabfa'),
            'desc' => __('Update Price after change in Hesabfa', 'ssbhesabfa'),
            'id' => 'ssbhesabfa_item_update_price',
            'default' => 'no',
            'type' => 'checkbox'
        );

        $fields[] = array(
            'title' => __('Update Quantity', 'ssbhesabfa'),
            'desc' => __('Update Quantity after change in Hesabfa', 'ssbhesabfa'),
            'id' => 'ssbhesabfa_item_update_quantity',
            'default' => 'no',
            'type' => 'checkbox'
        );

        $fields[] = array('type' => 'sectionend', 'id' => 'catalog_options');

        return $fields;
    }

    public static function ssbhesabfa_catalog_setting() {
        $ssbhesabf_setting_fields = self::ssbhesabfa_catalog_setting_fields();
        $Html_output = new Ssbhesabfa_Html_output();
        ?>
        <form id="ssbhesabfa_form" enctype="multipart/form-data" action="" method="post">
            <?php $Html_output->init($ssbhesabf_setting_fields); ?>
            <p class="submit">
                <input type="submit" name="ssbhesabfa_integration" class="button-primary"
                       value="<?php esc_attr_e('Save changes', 'ssbhesabfa'); ?>"/>
            </p>
        </form>
        <?php
    }

    public static function ssbhesabfa_catalog_setting_save_field() {
        $ssbhesabf_setting_fields = self::ssbhesabfa_catalog_setting_fields();
        $Html_output = new Ssbhesabfa_Html_output();
        $Html_output->save_fields($ssbhesabf_setting_fields);
    }


    public static function ssbhesabfa_customers_setting_fields() {

        $fields[] = array('title' => __('Customers Settings', 'ssbhesabfa'), 'type' => 'title', 'desc' => '', 'id' => 'customer_options');

        $fields[] = array(
            'title' => __('Update Customer Address', 'ssbhesabfa'),
            'desc' => __('Choose when update Customer address in Hesabfa.', 'ssbhesabfa'),
            'id' => 'ssbhesabfa_contact_address_status',
            'type' => 'select',
            'options' => array('1' => __('Use first customer address', 'ssbhesabfa'), '2' => __('update address with Invoice address', 'ssbhesabfa'), '3' => __('update address with Delivery address', 'ssbhesabfa')),
        );

        $fields[] = array(
            'title' => __('Customer\'s Group', 'ssbhesabfa'),
            'desc' => __('Enter a Customer\'s Group in Hesabfa', 'ssbhesabfa'),
            'id' => 'ssbhesabfa_contact_node_family',
            'type' => 'text',
            'default' => 'مشتریان فروشگاه آن‌لاین'
        );

        $fields[] = array('type' => 'sectionend', 'id' => 'customer_options');

        return $fields;
    }

    public static function ssbhesabfa_customers_setting() {
        $ssbhesabf_setting_fields = self::ssbhesabfa_customers_setting_fields();
        $Html_output = new Ssbhesabfa_Html_output();
        ?>
        <form id="ssbhesabfa_form" enctype="multipart/form-data" action="" method="post">
            <?php $Html_output->init($ssbhesabf_setting_fields); ?>
            <p class="submit">
                <input type="submit" name="ssbhesabfa_integration" class="button-primary"
                       value="<?php esc_attr_e('Save changes', 'ssbhesabfa'); ?>"/>
            </p>
        </form>
        <?php
    }

    public static function ssbhesabfa_customers_setting_save_field() {
        $ssbhesabf_setting_fields = self::ssbhesabfa_customers_setting_fields();
        $Html_output = new Ssbhesabfa_Html_output();
        $Html_output->save_fields($ssbhesabf_setting_fields);
    }


    public static function ssbhesabfa_invoice_setting_fields() {
        $fields[] = array('title' => __('Invoice Settings', 'ssbhesabfa'), 'type' => 'title', 'desc' => '', 'id' => 'invoice_options');

        $fields[] = array(
            'title' => __('Add invoice in which status'),
            'id' => 'ssbhesabfa_invoice_status',
            'type' => 'multiselect',
            'options' => array(
                'pending' => __('Pending payment', 'ssbhesabfa'),
                'processing' => __('Processing', 'ssbhesabfa'),
                'on-hold' => __('On hold', 'ssbhesabfa'),
                'completed' => __('Completed', 'ssbhesabfa'),
                'cancelled' => __('Cancelled', 'ssbhesabfa'),
                'refunded' => __('Refunded', 'ssbhesabfa'),
                'failed' => __('Failed', 'ssbhesabfa'),
                'checkout-draft' => __('Draft', 'ssbhesabfa'),
            ),
        );

        $fields[] = array(
            'title' => __('Return sale invoice status'),
            'id' => 'ssbhesabfa_invoice_return_status',
            'type' => 'multiselect',
            'options' => array(
                'pending' => __('Pending payment', 'ssbhesabfa'),
                'processing' => __('Processing', 'ssbhesabfa'),
                'on-hold' => __('On hold', 'ssbhesabfa'),
                'completed' => __('Completed', 'ssbhesabfa'),
                'cancelled' => __('Cancelled', 'ssbhesabfa'),
                'refunded' => __('Refunded', 'ssbhesabfa'),
                'failed' => __('Failed', 'ssbhesabfa'),
                'checkout-draft' => __('Draft', 'ssbhesabfa'),
            ),
        );

        $fields[] = array('type' => 'sectionend', 'id' => 'invoice_options');

        return $fields;
    }

    public static function ssbhesabfa_invoice_setting() {
        $ssbhesabf_setting_fields = self::ssbhesabfa_invoice_setting_fields();
        $Html_output = new Ssbhesabfa_Html_output();
        ?>
        <form id="ssbhesabfa_form" enctype="multipart/form-data" action="" method="post">
            <?php $Html_output->init($ssbhesabf_setting_fields); ?>
            <p class="submit">
                <input type="submit" name="ssbhesabfa_integration" class="button-primary"
                       value="<?php esc_attr_e('Save changes', 'ssbhesabfa'); ?>"/>
            </p>
        </form>
        <?php
    }

    public static function ssbhesabfa_invoice_setting_save_field() {
        $ssbhesabf_setting_fields = self::ssbhesabfa_invoice_setting_fields();
        $Html_output = new Ssbhesabfa_Html_output();
        $Html_output->save_fields($ssbhesabf_setting_fields);
    }


    public static function ssbhesabfa_payment_setting_fields() {
        $banks = Ssbhesabfa_Setting::ssbhesabfa_get_banks();

        $payment_gateways = new WC_Payment_Gateways;
        $available_payment_gateways = $payment_gateways->get_available_payment_gateways();

        $fields[] = array('title' => __('Payment methods Settings', 'ssbhesabfa'), 'type' => 'title', 'desc' => '', 'id' => 'payment_options');

        $fields[] = array(
            'title' => __('Add payment in which status'),
            'id' => 'ssbhesabfa_payment_status',
            'type' => 'multiselect',
            'options' => array(
                'pending' => __('Pending payment', 'ssbhesabfa'),
                'processing' => __('Processing', 'ssbhesabfa'),
                'on-hold' => __('On hold', 'ssbhesabfa'),
                'completed' => __('Completed', 'ssbhesabfa'),
                'cancelled' => __('Cancelled', 'ssbhesabfa'),
                'refunded' => __('Refunded', 'ssbhesabfa'),
                'failed' => __('Failed', 'ssbhesabfa'),
                'checkout-draft' => __('Draft', 'ssbhesabfa'),
            ),
        );

        foreach ($available_payment_gateways as $gateway) {
            $fields[] = array(
                'title' => $gateway->title,
                'id' => 'ssbhesabfa_payment_method_' . $gateway->id,
                'type' => 'select',
                'options' => $banks,
            );
        }

        $fields[] = array('type' => 'sectionend', 'id' => 'payment_options');

        return $fields;
    }

    public static function ssbhesabfa_payment_setting() {
        $ssbhesabf_setting_fields = self::ssbhesabfa_payment_setting_fields();
        $Html_output = new Ssbhesabfa_Html_output();
        ?>
        <form id="ssbhesabfa_form" enctype="multipart/form-data" action="" method="post">
            <?php $Html_output->init($ssbhesabf_setting_fields); ?>
            <p class="submit">
                <input type="submit" name="ssbhesabfa_integration" class="button-primary"
                       value="<?php esc_attr_e('Save changes', 'ssbhesabfa'); ?>"/>
            </p>
        </form>
        <?php
    }

    public static function ssbhesabfa_payment_setting_save_field() {
        $ssbhesabf_setting_fields = self::ssbhesabfa_payment_setting_fields();
        $Html_output = new Ssbhesabfa_Html_output();
        $Html_output->save_fields($ssbhesabf_setting_fields);
    }


    public static function ssbhesabfa_api_setting_fields() {

        $fields[] = array('title' => __('API Settings', 'ssbhesabfa'), 'type' => 'title', 'desc' => '', 'id' => 'api_options');

        $fields[] = array(
            'title' => __('Email', 'ssbhesabfa'),
            'desc' => __('Enter a Hesabfa email account', 'ssbhesabfa'),
            'id' => 'ssbhesabfa_account_username',
            'type' => 'email',
        );

        $fields[] = array(
            'title' => __('Password', 'ssbhesabfa'),
            'desc' => __('Enter a Hesabfa password', 'ssbhesabfa'),
            'id' => 'ssbhesabfa_account_password',
            'type' => 'password',
        );

        $fields[] = array(
            'title' => __('API Key', 'ssbhesabfa'),
            'desc' => __('Find API key in Setting->Financial Settings->API Menu', 'ssbhesabfa'),
            'id' => 'ssbhesabfa_account_api',
            'type' => 'text',
        );

        $fields[] = array('type' => 'sectionend', 'id' => 'api_options');

        return $fields;
    }

    public static function ssbhesabfa_api_setting() {
        $ssbhesabf_setting_fields = self::ssbhesabfa_api_setting_fields();
        $Html_output = new Ssbhesabfa_Html_output();
        ?>
        <form id="ssbhesabfa_form" enctype="multipart/form-data" action="" method="post">
            <?php $Html_output->init($ssbhesabf_setting_fields); ?>
            <p class="submit">
                <input type="submit" name="ssbhesabfa_integration" class="button-primary"
                       value="<?php esc_attr_e('Save changes', 'ssbhesabfa'); ?>"/>
            </p>
        </form>
        <?php
    }

    public static function ssbhesabfa_api_setting_save_field() {
        $ssbhesabf_setting_fields = self::ssbhesabfa_api_setting_fields();
        $Html_output = new Ssbhesabfa_Html_output();
        $Html_output->save_fields($ssbhesabf_setting_fields);

        Ssbhesabfa_Setting::ssbhesabfa_set_webhook();
    }


    public static function ssbhesabfa_export_setting() {
        // Export - Bulk product export offers
        $productExportResult = (isset($_GET['productExportResult'])) ? (bool)wc_clean($_GET['productExportResult']) : null;
        if (!is_null($productExportResult) && $productExportResult) {
            $processed = (isset($_GET['processed'])) ? wc_clean($_GET['processed']) : null;
            echo '<div class="updated">';
            echo '<p>' . sprintf(__('Export product completed. %s product added/updated.', 'ssbhesabfa'), $processed);
            echo '</div>';
        } elseif (!is_null($productExportResult) && !$productExportResult) {
            echo '<div class="updated">';
            echo '<p>' . __('Export products fail. Please check the log file.', 'ssbhesabfa');
            echo '</div>';
        }

        // Export - Product opening quantity export offers
        $productOpeningQuantityExportResult = (isset($_GET['productOpeningQuantityExportResult'])) ? (bool)wc_clean($_GET['productOpeningQuantityExportResult']) : null;
        if (!is_null($productOpeningQuantityExportResult) && $productOpeningQuantityExportResult) {
            echo '<div class="updated">';
            echo '<p>' . __('Export product opening quantity completed.');
            echo '</div>';
        } elseif (!is_null($productOpeningQuantityExportResult) && !$productOpeningQuantityExportResult) {
            echo '<div class="updated">';
            echo '<p>' . __('Export product opening quantity fail. Please check the log file.', 'ssbhesabfa');
            echo '</div>';
        }

        // Export - Bulk customer export offers
        $customerExportResult = (isset($_GET['customerExportResult'])) ? wc_clean($_GET['customerExportResult']) : null;

        if (!is_null($customerExportResult) && $customerExportResult) {
            $processed = (isset($_GET['processed'])) ? wc_clean($_GET['processed']) : null;
            echo '<div class="updated">';
            echo '<p>' . sprintf(__('Export customers completed. %s product added/updated.', 'ssbhesabfa'), $processed);
            echo '</div>';
        } elseif (!is_null($customerExportResult) && !$customerExportResult) {
            echo '<div class="updated">';
            echo '<p>' . __('Export customers fail. Please check the log file.', 'ssbhesabfa');
            echo '</div>';
        }

        ?>
        <div class="notice notice-info">
            <p><?php echo __('Export can take several minutes.', 'ssbhesabfa') ?></p>
        </div>
            <br>
            <form id="ssbhesabfa_export_products" autocomplete="off"
                  action="<?php echo admin_url('admin.php?page=ssbhesabfa-option&tab=export'); ?>"
                  method="post">
                <div>
                    <div>
                        <label for="ssbhesabfa-export-product-submit"></label>
                        <div>
                            <button class="button button-primary" id="ssbhesabfa-export-product-submit"
                                    name="ssbhesabfa-export-product-submit"><?php echo __('Export Products', 'ssbhesabfa'); ?></button>
                        </div>
                    </div>
                    <p><?php echo __('Export and add all online store products to Hesabfa', 'ssbhesabfa'); ?></p>
                </div>
            </form>
            <br>
            <form id="ssbhesabfa_export_products_opening_quantity" autocomplete="off"
                  action="<?php echo admin_url('admin.php?page=ssbhesabfa-option&tab=export'); ?>"
                  method="post">
                <div>
                    <div>
                        <label for="ssbhesabfa-export-product-opening-quantity-submit"></label>
                        <div>
                            <button class="button button-primary" id="ssbhesabfa-export-product-opening-quantity-submit"
                                    name="ssbhesabfa-export-product-opening-quantity-submit"><?php echo __('Export Products opening quantity', 'ssbhesabfa'); ?></button>
                        </div>
                    </div>
                    <p><?php echo __('Export the products quantity and record the \'products opening quantity\' in the Hesabfa', 'ssbhesabfa'); ?></p>
                </div>
            </form>
            <br>
            <form id="ssbhesabfa_export_customers" autocomplete="off"
                  action="<?php echo admin_url('admin.php?page=ssbhesabfa-option&tab=export'); ?>"
                  method="post">
                <div>
                    <div>
                        <label for="ssbhesabfa-export-customer-submit"></label>
                        <div>
                            <button class="button button-primary" id="ssbhesabfa-export-customer-submit"
                                    name="ssbhesabfa-export-customer-submit"><?php echo __('Export Customers', 'ssbhesabfa'); ?></button>
                        </div>
                    </div>
                    <p><?php echo __('Export and add all online store customers to Hesabfa.', 'ssbhesabfa'); ?></p>
                </div>
            </form>
        </div>
        <?php
    }

    public static function ssbhesabfa_sync_setting() {
        // Sync - Bulk changes sync offers
        $changesSyncResult = (isset($_GET['changesSyncResult'])) ? (bool)wc_clean($_GET['changesSyncResult']) : false;
        if ($changesSyncResult) {
            echo '<div class="updated">';
            echo '<p>' . __('Sync completed; All hesabfa changes synced successfully.', 'ssbhesabfa');
            echo '</div>';
        }

        // Sync - Bulk product sync offers
        $productSyncResult = (isset($_GET['productSyncResult'])) ? (bool)wc_clean($_GET['productSyncResult']) : null;
        if (!is_null($productSyncResult) && $productSyncResult) {
            echo '<div class="updated">';
            echo '<p>' . __('Sync completed; All products added/updated.', 'ssbhesabfa');
            echo '</div>';
        } elseif (!is_null($productSyncResult) && !$productSyncResult) {
            echo '<div class="updated">';
            echo '<p>' . __('Sync completed; No product added/updated.', 'ssbhesabfa');
            echo '</div>';
        }

        // Sync - Bulk invoice sync offers
        $orderSyncResult = (isset($_GET['orderSyncResult'])) ? (bool)wc_clean($_GET['orderSyncResult']) : null;
        $fiscal = (isset($_GET['fiscal'])) ? (bool)wc_clean($_GET['fiscal']) : null;

        if (!is_null($orderSyncResult) && $orderSyncResult) {
            $processed = (isset($_GET['processed'])) ? wc_clean($_GET['processed']) : null;
            echo '<div class="updated">';
            echo '<p>' . sprintf(__('Order sync completed. %s order added.', 'ssbhesabfa'), $processed);
            echo '</div>';
        } elseif (!is_null($orderSyncResult) && !$orderSyncResult) {
            if (!is_null($fiscal) && $fiscal) {
                echo '<div class="error">';
                echo '<p>' . __('The date entered is not within the fiscal year.', 'ssbhesabfa');
                echo '</div>';
            } elseif (!is_null($fiscal) && !$fiscal) {
                echo '<div class="error">';
                echo '<p>' . __('Cannot sync orders. Please enter valid Date format.', 'ssbhesabfa');
                echo '</div>';
            }
        }
        ?>

        <div class="notice notice-info">
            <p><?php echo __('Sync can take several minutes.', 'ssbhesabfa') ?></p>
        </div>

        <br>
        <form id="ssbhesabfa_sync_changes" autocomplete="off"
              action="<?php echo admin_url('admin.php?page=ssbhesabfa-option&tab=sync'); ?>"
              method="post">
            <div>
                <div>
                    <label for="ssbhesabfa-sync-changes-submit"></label>
                    <div>
                        <button class="button button-primary" id="ssbhesabfa-sync-changes-submit"
                                name="ssbhesabfa-sync-changes-submit"><?php echo esc_attr_e('Sync Changes', 'ssbhesabfa'); ?></button>
                    </div>
                </div>
                <p><?php echo __('Sync all Hesabfa changes with Online Store.', 'ssbhesabfa'); ?></p>
            </div>
        </form>
        <br>
        <form id="ssbhesabfa_sync_products" autocomplete="off"
              action="<?php echo admin_url('admin.php?page=ssbhesabfa-option&tab=sync'); ?>"
              method="post">
            <div>
                <div>
                    <label for="ssbhesabfa-sync-products-submit"></label>
                    <div>
                        <button class="button button-primary" id="ssbhesabfa-sync-products-submit"
                                name="ssbhesabfa-sync-products-submit"><?php echo __('Sync Products Quantity and Price', 'ssbhesabfa'); ?></button>
                    </div>
                </div>
                <p><?php echo __('Sync quantity and price of products in hesabfa with online store.', 'ssbhesabfa'); ?></p>
            </div>
        </form>
        <br>
        <form id="ssbhesabfa_sync_orders" autocomplete="off"
              action="<?php echo admin_url('admin.php?page=ssbhesabfa-option&tab=sync'); ?>"
              method="post">
            <div>
                <div>
                    <label for="ssbhesabfa-sync-orders-submit"></label>
                    <div>
                        <input type="date" id="ssbhesabfa_sync_order_date" name="ssbhesabfa_sync_order_date" value="" class="datepicker" />
                        <button class="button button-primary" id="ssbhesabfa-sync-orders-submit"
                                name="ssbhesabfa-sync-orders-submit"><?php echo __('Sync Orders', 'ssbhesabfa'); ?></button>
                    </div>
                </div>
                <p><?php echo __('Sync/Add orders in online store with hesabfa from above date.', 'ssbhesabfa'); ?></p>
            </div>
        </form>
        <?php
    }


    public static function ssbhesabfa_set_webhook() {
        $url = get_site_url() . '/index.php?ssbhesabfa_webhook=1&token=' . substr(wp_hash(AUTH_KEY . 'ssbhesabfa/webhook'), 0, 10);

        $hookPassword = get_option('ssbhesabfa_webhook_password');

        $ssbhesabfa_api = new Ssbhesabfa_Api();
        $response = $ssbhesabfa_api->settingSetChangeHook($url, $hookPassword);

        if (is_object($response)) {
            if ($response->Success) {
                update_option('ssbhesabfa_live_mode', 1);

                //set the last log ID if is not set
                $changes = $ssbhesabfa_api->settingGetChanges();
                if ($changes->Success) {
                    if (get_option('ssbhesabfa_last_log_check_id') == 0) {
                        $lastChange = end($changes->Result);
                        update_option('ssbhesabfa_last_log_check_id', $lastChange->Id);
                    }
                } else {
                    echo '<div class="error">';
                    echo '<p>' . __('Cannot check the last change ID. Error Message: ', 'ssbhesabfa')  . $changes->ErrorMessage . '</p>';
                    echo '</div>';
                }

                //check the Hesabfa default currency
                $default_currency = $ssbhesabfa_api->settingGetCurrency();
                if ($default_currency->Success) {
                    $woocommerce_currency = get_woocommerce_currency();
                    $hesabfa_currency = $default_currency->Result->Currency;
                    if ($hesabfa_currency == $woocommerce_currency || ($hesabfa_currency == 'IRR' && $woocommerce_currency == 'IRT')) {
                        update_option('ssbhesabfa_hesabfa_default_currency', $hesabfa_currency);
                    } else {
                        update_option('ssbhesabfa_hesabfa_default_currency', 0);
                        update_option('ssbhesabfa_live_mode', 0);

                        echo '<div class="error">';
                        echo '<p>' . __('Hesabfa and WooCommerce default currency must be same.');
                        echo '</div>';
                    }
                } else {
                    echo '<div class="error">';
                    echo '<p>' . __('Cannot check the Hesabfa default currency. Error Message: ') . $default_currency->ErrorMessage . '</p>';
                    echo '</div>';
                }

                if (get_option('ssbhesabfa_live_mode')) {
                    echo '<div class="updated">';
                    echo '<p>' . __('API Setting updated. Test Successfully', 'ssbhesabfa') . '</p>';
                    echo '</div>';
                }
            } else {
                update_option('ssbhesabfa_live_mode', 0);

                echo '<div class="error">';
                echo '<p>' . __('Cannot set Hesabfa webHook. Error Message:') . $response->ErrorMessage . '</p>';
                echo '</div>';
            }
        } else {
            update_option('ssbhesabfa_live_mode', 0);

            echo '<div class="error">';
            echo '<p>' . __('Cannot connect to Hesabfa servers. Please check your Internet connection') . '</p>';
            echo '</div>';
        }

        return $response;
    }

    public static function ssbhesabfa_get_banks()
    {
        $ssbhesabfa_api = new Ssbhesabfa_Api();
        $banks = $ssbhesabfa_api->settingGetBanks();

        if (is_object($banks) && $banks->Success) {
            $available_banks = array();
            $available_banks[-1] = __('No need to set!');
            foreach ($banks->Result as $bank) {
                if ($bank->Currency == get_woocommerce_currency() || (get_woocommerce_currency() == 'IRT' && $bank->Currency == 'IRR')) {
                    $available_banks[$bank->Code] = $bank->Name . ' - ' . $bank->Branch . ' - ' . $bank->AccountNumber;
                }
            }

            if (empty($available_banks)) {
                $available_banks[0] = __('Define at least one bank in Hesabfa', 'ssbhesabfa');
            }

            return $available_banks;
        } else {
            update_option('ssbhesabfa_live_mode', 0);

            echo '<div class="error">';
            echo '<p>' . __('Cannot get Banks detail.', 'ssbhesabfa') . '</p>';
            echo '</div>';

            return array('0' => __('Cannot get Banks detail.', 'ssbhesabfa'));
        }
    }
}

Ssbhesabfa_Setting::init();
