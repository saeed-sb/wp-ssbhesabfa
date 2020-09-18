<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @class      Ssbhesabfa_Admin
 * @version    1.0.9
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
     * Action - Ajax 'export products Opening Quntity' from Hesabfa/Export tab
     * @since	1.0.6
     */
    public function adminExportProductsOpeningQuantityCallback() {
        if (is_admin() && (defined('DOING_AJAX') || DOING_AJAX)) {
            $func = new Ssbhesabfa_Admin_Functions();

            if (!$func->exportOpeningQuantity()) {
                $redirect_url = admin_url('admin.php?page=ssbhesabfa-option&tab=export&productOpeningQuantityExportResult=false');
            } else {
                $redirect_url = admin_url('admin.php?page=ssbhesabfa-option&tab=export&productOpeningQuantityExportResult=true');
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

            new Ssbhesabfa_Webhook();

            $redirect_url = admin_url('admin.php?page=ssbhesabfa-option&tab=sync$changesSyncResult=true');
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

                if ($syncOrders == false) {
                    $redirect_url = admin_url('admin.php?page=ssbhesabfa-option&tab=sync&orderSyncResult=0&fiscal=0');
                } elseif ($syncOrders == 'fiscalYearError') {
                    $redirect_url = admin_url('admin.php?page=ssbhesabfa-option&tab=sync&orderSyncResult=0&fiscal=1');
                } else {
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
        $function->setContactAddress($id_customer);
    }

    public function ssbhesabfa_hook_delete_user($id_customer)
    {
        $func = new Ssbhesabfa_Admin_Functions();
        $id_obj = $func->getObjectId('customer', $id_customer);
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
                $function->setOrder($id_order, 2);
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
        $function->setItem($id_product);
    }

    public function ssbhesabfa_hook_delete_product($id_product)
    {
        $func = new Ssbhesabfa_Admin_Functions();
        $id_obj = $func->getObjectId('product', $id_product);
        global $wpdb;
        $row = $wpdb->get_row("SELECT `id_hesabfa` FROM `".$wpdb->prefix."ssbhesabfa` WHERE `id` = $id_obj AND `obj_type` = 'product'");

        if (is_object($row)) {
            $hesabfaApi = new Ssbhesabfa_Api();
            $hesabfaApi->itemDelete($row->id_hesabfa);
        }

        global $wpdb;
        $wpdb->delete($wpdb->prefix.'ssbhesabfa', array('id_ps' => $id_product));

        Ssbhesabfa_Admin_Functions::log(array("Product deleted. Product ID: $id_product"));
    }
}
