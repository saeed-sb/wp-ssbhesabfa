<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @class      Ssbhesabfa_Admin
 * @version    1.1.5
 * @since      1.0.0
 * @package    ssbhesabfa
 * @subpackage ssbhesabfa/admin
 * @author     Saeed Sattar Beglou <saeed.sb@gmail.com>
 */
class Ssbhesabfa_Admin {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
        $this->load_dependencies();
    }

	/**
	 * Check DB ver on plugin update and do necessary actions
	 *
	 * @since    1.0.7
	 */
	public function ssbhesabfa_update_db_check() {
        $current_db_ver = get_site_option('ssbhesabfa_db_version');
        if ($current_db_ver === false || $current_db_ver < 1.1) {
            global $wpdb;
            $table_name = $wpdb->prefix . "ssbhesabfa";

            $sql = "ALTER TABLE $table_name
                    ADD `id_ps_attribute` INT(11) UNSIGNED NULL DEFAULT 0 AFTER id_ps;";

            if ($wpdb->query($sql) !== false) {
                update_option('ssbhesabfa_db_version', 1.1);
            }
        }
    }

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ssbhesabfa_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ssbhesabfa_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ssbhesabfa-admin.css', array(), $this->version, 'all' );
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
		 * defined in Ssbhesabfa_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ssbhesabfa_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ssbhesabfa-admin.js', array('jquery'), $this->version, false );
	}

    private function load_dependencies() {
        /**
         * The class responsible for defining all actions that occur in the Dashboard
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/ssbhesabfa-admin-display.php';

        /**
         * The class responsible for defining function for display Html element
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/ssbhesabfa-html-output.php';

        /**
         * The class responsible for defining function for display general setting tab
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/ssbhesabfa-admin-setting.php';

        /**
         * The class responsible for defining function for admin area
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/ssbhesabfa-admin-functions.php';
    }

    /**
     * WC missing notice for the admin area.
     *
     * @since    1.0.0
     */
    public function ssbhesabfa_missing_notice() {
        echo '<div class="error"><p>' . sprintf(__('Hesabfa Plugin requires the %s to work!', 'ssbhesabfa'), '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">' . __('WooCommerce', 'ssbhesabfa') . '</a>') . '</p></div>';
    }

    /**
     * Hesabfa Plugin Live mode notice for the admin area.
     *
     * @since    1.0.0
     */
    public function ssbhesabfa_live_mode_notice() {
        echo '<div class="error"><p>' . __('Hesabfa Plugin need to connect to Hesabfa Accounting, Please check the API credential!', 'ssbhesabfa') . '</p></div>';
    }

    /**
     * Missing hesabfa default currency notice for the admin area.
     *
     * @since    1.0.0
     */
    public function ssbhesabfa_currency_notice() {
        echo '<div class="error"><p>' . __('Hesabfa Plugin cannot works! because WooCommerce currency in not match with Hesabfa.', 'ssbhesabfa') . '</p></div>';
    }

    /*
     * Action - Ajax 'export products' from Hesabfa/Export tab
     * @since	1.0.0
     */
    public function adminExportProductsCallback() {
        if (is_admin() && (defined('DOING_AJAX') || DOING_AJAX)) {
            $func = new Ssbhesabfa_Admin_Functions();
            $update_count = $func->exportProducts();

            if ($update_count === false) {
                $redirect_url = admin_url('admin.php?page=ssbhesabfa-option&tab=export&productExportResult=false');
            } else {
                $redirect_url = admin_url('admin.php?page=ssbhesabfa-option&tab=export&productExportResult=true&processed=' . $update_count);
            }
            echo $redirect_url;

            die(); // this is required to return a proper result
        }
    }

    /*
     * Action - Ajax 'export products Opening Quantity' from Hesabfa/Export tab
     * @since	1.0.6
     */
    public function adminExportProductsOpeningQuantityCallback() {
        if (is_admin() && (defined('DOING_AJAX') || DOING_AJAX)) {
            $func = new Ssbhesabfa_Admin_Functions();
            switch ($func->exportOpeningQuantity()) {
                case 'true':
                    update_option('ssbhesabfa_use_export_product_opening_quantity', true);
                    $redirect_url = admin_url('admin.php?page=ssbhesabfa-option&tab=export&productOpeningQuantityExportResult=true');
                    break;
                case 'false':
                    $redirect_url = admin_url('admin.php?page=ssbhesabfa-option&tab=export&productOpeningQuantityExportResult=false');
                    break;
                case 'shareholderError':
                    $redirect_url = admin_url('admin.php?page=ssbhesabfa-option&tab=export&productOpeningQuantityExportResult=false&shareholderError=true');
                    break;
                case 'noProduct':
                    $redirect_url = admin_url('admin.php?page=ssbhesabfa-option&tab=export&productOpeningQuantityExportResult=false&noProduct=true');
                    break;
            }
            echo $redirect_url;

            die(); // this is required to return a proper result
        }
    }

    /*
     * Action - Ajax 'export customers' from Hesabfa/Export tab
     * @since	1.0.0
     */
    public function adminExportCustomersCallback() {
        if (is_admin() && (defined('DOING_AJAX') || DOING_AJAX)) {
            $func = new Ssbhesabfa_Admin_Functions();
            $update_count = $func->exportCustomers();
            if ($update_count === false) {
                $redirect_url = admin_url('admin.php?page=ssbhesabfa-option&tab=export&customerExportResult=false');
            } else {
                $redirect_url = admin_url('admin.php?page=ssbhesabfa-option&tab=export&customerExportResult=true&processed=' . $update_count);
            }
            echo $redirect_url;

            die(); // this is required to return a proper result
        }
    }

    /*
     * Action - Ajax 'Sync Changes' from Hesabfa/Sync tab
     * @since	1.0.0
     */
    public function adminSyncChangesCallback() {
        if (is_admin() && (defined('DOING_AJAX') || DOING_AJAX)) {
            include (plugin_dir_path( __DIR__ ) . 'includes/class-ssbhesabfa-webhook.php');
            new Ssbhesabfa_Webhook();

            $redirect_url = admin_url('admin.php?page=ssbhesabfa-option&tab=sync&changesSyncResult=true');
            echo $redirect_url;

            die(); // this is required to return a proper result
        }
    }

    /*
     * Action - Ajax 'Sync Products' from Hesabfa/Sync tab
     * @since	1.0.0
     */
    public function adminSyncProductsCallback() {
        if (is_admin() && (defined('DOING_AJAX') || DOING_AJAX)) {
            $func = new Ssbhesabfa_Admin_Functions();
            if ($func->syncProducts()) {
                $redirect_url = admin_url('admin.php?page=ssbhesabfa-option&tab=sync&productSyncResult=true');
            } else {
                $redirect_url = admin_url('admin.php?page=ssbhesabfa-option&tab=sync&productSyncResult=false');
            }
            echo $redirect_url;

            die(); // this is required to return a proper result
        }
    }

    /*
     * Action - Ajax 'Sync Orders from Hesabfa/Sync tab
     * @since	1.0.0
     */
    public function adminSyncOrdersCallback() {
        if (is_admin() && (defined('DOING_AJAX') || DOING_AJAX)) {
            $errors = false;

            if (isset($_POST["date"])) {
                $from_date = wc_clean($_POST['date']);
            } else {
                $errors = true;
            }

            // return
            if (!$errors) {
                $func = new Ssbhesabfa_Admin_Functions();
                $syncOrders = $func->syncOrders($from_date);

                switch ($syncOrders) {
                    case false:
                        $redirect_url = admin_url('admin.php?page=ssbhesabfa-option&tab=sync&orderSyncResult=false');
                        break;
                    case 'fiscalYearError':
                        $redirect_url = admin_url('admin.php?page=ssbhesabfa-option&tab=sync&orderSyncResult=false&fiscal=true');
                        break;
                    case 'activationDateError':
                        $redirect_url = admin_url('admin.php?page=ssbhesabfa-option&tab=sync&orderSyncResult=false&activationDate=true');
                        break;
                    case 'inputDateError':
                        $redirect_url = admin_url('admin.php?page=ssbhesabfa-option&tab=sync&orderSyncResult=false');
                        break;
                    case 'zeroProduct':
                        $redirect_url = admin_url('admin.php?page=ssbhesabfa-option&tab=sync&orderSyncResult=true&processed=0');
                        break;
                    default:
                        $redirect_url = admin_url('admin.php?page=ssbhesabfa-option&tab=sync&orderSyncResult=true&processed=' . count($syncOrders));
                }

                echo $redirect_url;
            }
            die(); // this is required to return a proper result
        }
    }

    //This functions related to set webhook
    public function ssbhesabfa_init_internal()
    {
        add_rewrite_rule( 'ssbhesabfa-webhook.php$', 'index.php?ssbhesabfa_webhook=1', 'top' );
    }

    public function ssbhesabfa_query_vars( $query_vars )
    {
        $query_vars[] = 'ssbhesabfa_webhook';
        return $query_vars;
    }

    public function ssbhesabfa_parse_request( &$wp )
    {
        if ( array_key_exists( 'ssbhesabfa_webhook', $wp->query_vars ) ) {
            include (plugin_dir_path( __DIR__ ) . 'includes/ssbhesabfa-webhook.php');
            exit();
        }
        return;
    }

    //Hooks
    //Contact
    public function ssbhesabfa_hook_user_register($id_customer)
    {
        $function = new Ssbhesabfa_Admin_Functions();
        $function->setContact($id_customer);
    }

    public function ssbhesabfa_hook_delete_user($id_customer)
    {
        $func = new Ssbhesabfa_Admin_Functions();
        $id_obj = $func->getObjectId('customer', $id_customer);
        if ($id_obj != false) {
            global $wpdb;
            $row = $wpdb->get_row("SELECT `id_hesabfa` FROM `".$wpdb->prefix."ssbhesabfa` WHERE `id` = $id_obj AND `obj_type` = 'customer'");

            if (is_object($row)) {
                $hesabfaApi = new Ssbhesabfa_Api();
                $hesabfaApi->contactDelete($row->id_hesabfa);
            }

            global $wpdb;
            $wpdb->delete($wpdb->prefix.'ssbhesabfa', array('id_ps' => $id_customer));

            Ssbhesabfa_Admin_Functions::log(array("Customer deleted. Customer ID: $id_customer"));
        }
    }

    //Invoice
    public function ssbhesabfa_hook_new_order($id_order)
    {
        $function = new Ssbhesabfa_Admin_Functions();
        $function->setOrder($id_order);
    }

    public function ssbhesabfa_hook_order_status_change($id_order, $from, $to)
    {
        foreach (get_option('ssbhesabfa_invoice_status') as $status) {
            if ($status == $to) {
                $function = new Ssbhesabfa_Admin_Functions();
                $function->setOrder($id_order);
            }
        }

        foreach (get_option('ssbhesabfa_invoice_return_status') as $status) {
            if ($status == $to) {
                $function = new Ssbhesabfa_Admin_Functions();
                $function->setOrder($id_order, 2, $function->getInvoiceCodeByOrderId($id_order));
            }
        }
    }

    public function ssbhesabfa_hook_payment_confirmation($id_order, $from, $to)
    {
        foreach (get_option('ssbhesabfa_payment_status') as $status) {
            if ($status == $to) {
                $function = new Ssbhesabfa_Admin_Functions();
                $function->setOrderPayment($id_order);
            }
        }
    }

    //Item
    public function ssbhesabfa_hook_new_product($id_product)
    {
        $function = new Ssbhesabfa_Admin_Functions();
        $function->setItems(array($id_product));
    }

    public function ssbhesabfa_hook_save_product_variation($id_attribute)
    {
        //change hesabfa item code
        $variable_field_id = "ssbhesabfa_hesabfa_item_code_" . $id_attribute;
        $code = $_POST[$variable_field_id];
        $id_product = $_POST['product_id'];

        if (isset($code)) {
            global $wpdb;
            $row = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."ssbhesabfa` WHERE `id_hesabfa` = ".$code." AND `obj_type` = 'product'");

            if (is_object($row)) {
                if ($row->id_ps == $id_product && $row->id_ps_attribute == $id_attribute) {
                    return false;
                }

                echo '<div class="error"><p>' . __('The new Item code already used for another Item', 'ssbhesabfa') . '</p></div>';
                Ssbhesabfa_Admin_Functions::log(array("The new Item code already used for another Item. Product ID: $id_product"));
            } else {
                $wpdb->update($wpdb->prefix . 'ssbhesabfa', array(
                    'id_hesabfa' => (int)$code,
                ), array(
                    'id_ps' => $id_product,
                    'id_ps_attribute' => $id_attribute,
                    'obj_type' => 'product',
                ));
            }
        }

        //add attribute if not exists
        $func = new Ssbhesabfa_Admin_Functions();
        $code = $func->getItemCodeByProductId($id_product, $id_attribute);
        if (!$code) {
            $func->setItems(array($id_product));
        }
    }

    //ToDo: check why base product not deleted
    public function ssbhesabfa_hook_delete_product($id_product)
    {
        $func = new Ssbhesabfa_Admin_Functions();
        $hesabfaApi = new Ssbhesabfa_Api();
        global $wpdb;

        $variations = $func->getProductVariations($id_product);
        if ($variations != false) {
            foreach ($variations as $variation) {
                $id_attribute = $variation->get_id();
                $code = $func->getItemCodeByProductId($id_product, $id_attribute);
                if ($code != false) {
                    $hesabfaApi->itemDelete($code);

                    $wpdb->delete($wpdb->prefix.'ssbhesabfa', array('id_hesabfa' => $code, 'obj_type' => 'product'));
                    Ssbhesabfa_Admin_Functions::log(array("Product variation deleted. Product ID: $id_product-$id_attribute"));
                }
            }
        }

        $code = $func->getItemCodeByProductId($id_product);
        if ($code != false) {
            $hesabfaApi->itemDelete($code);

            $wpdb->delete($wpdb->prefix.'ssbhesabfa', array('id_hesabfa' => $code, 'obj_type' => 'product'));
            Ssbhesabfa_Admin_Functions::log(array("Product deleted. Product ID: $id_product"));
        }
    }

    public function ssbhesabfa_hook_delete_product_variation($id_attribute)
    {
//        $func = new Ssbhesabfa_Admin_Functions();
        $hesabfaApi = new Ssbhesabfa_Api();
        global $wpdb;

//        $code = $func->getItemCodeByProductId($id_product, $id_attribute);
        $row = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."ssbhesabfa` WHERE `id_ps_attribute` = $id_attribute AND `obj_type` = 'product'");

        if (is_object($row)) {
            $hesabfaApi->itemDelete($row->id_hesabfa);

            $wpdb->delete($wpdb->prefix.'ssbhesabfa', array('id' => $row->id));
            Ssbhesabfa_Admin_Functions::log(array("Product variation deleted. Product ID: $row->id_ps-$id_attribute"));
        }
    }

    public function ssbhesabfa_hook_product_options_general_product_data()
    {
        $value = isset($_GET['post']) ? Ssbhesabfa_Admin_Functions::getItemCodeByProductId($_GET['post']) : '';
        $args = array(
            'id' => 'ssbhesabfa_hesabfa_item_code_0',
            'label' => __( 'Hesabfa base item code', 'ssbhesabfa' ),
            'desc_tip' => true,
            'description' => __( 'The base Item code of this product in Hesabfa, if you want to map this product to another item in Hesabfa, enter the new Item code.', 'ssbhesabfa' ),
            'value' => $value,
            'type' => 'number',
        );
        woocommerce_wp_text_input($args);
    }

    public function ssbhesabfa_hook_process_product_meta($post_id)
    {
        $itemCode = $_POST['ssbhesabfa_hesabfa_item_code_0'];

        if (isset($itemCode)) {
            global $wpdb;
            $row = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."ssbhesabfa` WHERE `id_hesabfa` = ".$itemCode." AND `obj_type` = 'product'");

            if (is_object($row)) {
                //ToDo: show error to customer in BO
                echo '<div class="error"><p>' . __('The new Item code already used for another Item', 'ssbhesabfa') . '</p></div>';
                Ssbhesabfa_Admin_Functions::log(array("The new Item code already used for another Item. Product ID: $post_id"));
            } else {
                $wpdb->update($wpdb->prefix . 'ssbhesabfa', array(
                    'id_hesabfa' => (int)$_POST['ssbhesabfa_hesabfa_item_code_0'],
                ), array(
                    'id_ps' => $post_id,
                    'id_ps_attribute' => 0,
                    'obj_type' => 'product',
                ));
            }
        }
    }

    public function ssbhesabfa_hook_product_after_variable_attributes($loop, $variation_data, $variation)
    {
        $value = isset($_POST['product_id']) ? Ssbhesabfa_Admin_Functions::getItemCodeByProductId($_POST['product_id'], $variation->ID) : '';
        $args = array(
            'id' => 'ssbhesabfa_hesabfa_item_code_' . $variation->ID,
            'label' => __('Hesabfa variable item code', 'ssbhesabfa'),
            'desc_tip' => true,
            'description' => __('The variable Item code of this product variable in Hesabfa, if you want to map this product to another item in Hesabfa, enter the new Item code.', 'ssbhesabfa'),
            'value' => $value,
        );
        woocommerce_wp_text_input($args);
    }
}
