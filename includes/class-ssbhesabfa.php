<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @class      Ssbhesabfa
 * @version    1.1.3
 * @since      1.0.0
 * @package    ssbhesabfa
 * @subpackage ssbhesabfa/includes
 * @author     Saeed Sattar Beglou <saeed.sb@gmail.com>
 */

class Ssbhesabfa {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Ssbhesabfa_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SSBHESABFA_VERSION' ) ) {
			$this->version = SSBHESABFA_VERSION;
		} else {
			$this->version = '1.1.3';
		}
		$this->plugin_name = 'ssbhesabfa';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Ssbhesabfa_Loader. Orchestrates the hooks of the plugin.
	 * - Ssbhesabfa_i18n. Defines internationalization functionality.
	 * - Ssbhesabfa_Admin. Defines all hooks for the admin area.
	 * - Ssbhesabfa_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ssbhesabfa-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ssbhesabfa-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ssbhesabfa-admin.php';

		/**
		 * The class responsible for defining all Hesabfa API methods
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ssbhesabfa-api.php';

		$this->loader = new Ssbhesabfa_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Ssbhesabfa_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
        $plugin_admin = new Ssbhesabfa_Admin( $this->get_plugin_name(), $this->get_version() );

        //Related to check DB ver on plugin update
        $this->loader->add_action( 'plugins_loaded', $plugin_admin, 'ssbhesabfa_update_db_check' );

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        //Related to webhook set
        $this->loader->add_action( 'init', $plugin_admin, 'ssbhesabfa_init_internal' );
        $this->loader->add_filter( 'query_vars', $plugin_admin, 'ssbhesabfa_query_vars' );
        $this->loader->add_action( 'parse_request', $plugin_admin, 'ssbhesabfa_parse_request' );

        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            //Check plugin live mode
            if (get_option('ssbhesabfa_live_mode')) {
                if (get_option('ssbhesabfa_hesabfa_default_currency') === 0) {
                    $this->loader->add_action('admin_notices', $plugin_admin, 'ssbhesabfa_currency_notice');
                }

                //Runs when a new order added.
//                $this->loader->add_action('woocommerce_thankyou', $plugin_admin, 'ssbhesabfa_hook_new_order', 10, 1);
//                $this->loader->add_action('woocommerce_new_order', $plugin_admin, 'ssbhesabfa_hook_new_order', 10, 1);
                $this->loader->add_action('woocommerce_order_status_changed', $plugin_admin, 'ssbhesabfa_hook_order_status_change', 10, 3);

                //Runs when an order paid.
//                $this->loader->add_action('woocommerce_payment_complete', $plugin_admin, 'ssbhesabfa_hook_payment_confirmation', 10, 1);
//                $this->loader->add_filter('woocommerce_payment_complete_order_status', $plugin_admin, 'ssbhesabfa_hook_payment_confirmation', 10, 1);
//                $this->loader->add_filter('woocommerce_order_status_completed', $plugin_admin, 'ssbhesabfa_hook_payment_confirmation', 10, 1);
                $this->loader->add_filter('woocommerce_order_status_changed', $plugin_admin, 'ssbhesabfa_hook_payment_confirmation', 10, 3);

                //Runs when a user's profile is first created.
                $this->loader->add_action('user_register', $plugin_admin, 'ssbhesabfa_hook_user_register');
//                $this->loader->add_action('woocommerce_new_customer', $plugin_admin, 'ssbhesabfa_hook_user_register');
//                $this->loader->add_action('woocommerce_created_customer', $plugin_admin, 'ssbhesabfa_hook_user_register');
                //Runs when a user updates personal options from the admin screen.
                $this->loader->add_action('personal_options_update', $plugin_admin, 'ssbhesabfa_hook_user_register');
                //Runs when a user's profile is updated.
                $this->loader->add_action('profile_update', $plugin_admin, 'ssbhesabfa_hook_user_register');
                //Runs when a user is deleted.
                $this->loader->add_action('delete_user', $plugin_admin, 'ssbhesabfa_hook_delete_user');

                //Runs when a product is added.
                $this->loader->add_action('woocommerce_new_product', $plugin_admin, 'ssbhesabfa_hook_new_product');
                //Runs when a product is updated.
                $this->loader->add_action('woocommerce_update_product', $plugin_admin, 'ssbhesabfa_hook_new_product');
                //Runs when a product is deleted.
                $this->loader->add_action('woocommerce_delete_product', $plugin_admin, 'ssbhesabfa_hook_delete_product');
            } elseif (!get_option('ssbhesabfa_live_mode')) {
                $this->loader->add_action('admin_notices', $plugin_admin, 'ssbhesabfa_live_mode_notice');
            }

            /*
             * Action - Ajax 'Export Tabs' from Hesabfa/Export
             * @since	1.0.0
             */
            $this->loader->add_filter('wp_ajax_adminExportProducts', $plugin_admin, 'adminExportProductsCallback');
            $this->loader->add_filter('wp_ajax_adminExportProductsOpeningQuantity', $plugin_admin, 'adminExportProductsOpeningQuantityCallback');
            $this->loader->add_filter('wp_ajax_adminExportCustomers', $plugin_admin, 'adminExportCustomersCallback');

            /*
             * Action - Ajax 'Sync Tabs' from Hesabfa/Sync
             * @since	1.0.0
             */
            $this->loader->add_filter('wp_ajax_adminSyncChanges', $plugin_admin, 'adminSyncChangesCallback');
            $this->loader->add_filter('wp_ajax_adminSyncProducts', $plugin_admin, 'adminSyncProductsCallback');
            $this->loader->add_filter('wp_ajax_adminSyncOrders', $plugin_admin, 'adminSyncOrdersCallback');

        } else {
            $this->loader->add_action('admin_notices', $plugin_admin, 'ssbhesabfa_missing_notice');
        }
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Ssbhesabfa_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
