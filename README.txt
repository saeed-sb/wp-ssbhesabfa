=== Hesabfa Accounting ===
Contributors: saeedsb
Tags: accounting cloud hesabfa
Requires at least: 5.2
Tested up to: 5.5
Requires PHP: 5.6
Stable tag: 1.0.8
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect Hesabfa Online Accounting to WooCommerce.

== Description ==
This plugin helps connect your (online) store to Hesabfa online accounting software. By using this plugin, saving products, contacts, and orders in your store will also save them automatically in your Hesabfa account. Besides that, just after a client pays a bill, the receipt document will be stored in Hesabfa as well. Of course, you have to register your account in Hesabfa first. To do so, visit Hesabfa at the link here www.hesabfa.com and sign up for free. After you signed up and entered your account, choose your business, then in the settings menu/API, you can find the API keys for the business and import them to the plugin settings. Now your module is ready to use.

For more information and a full guide to how to use Hesabfa and WooCommerce Plugin, visit Hesabfa’s website and go to the “Accounting School” menu.

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/hesabfa-accounting` directory, or install the hesabfa plugin through the WordPress plugins screen directly.
2. Activate the plugin through the \'Plugins\' screen in WordPress
3. Use the Settings->Hesabfa screen to configure the plugin

== Screenshots ==
1. Catalog setting page
2. Customers setting page
3. Invoice setting page
4. Payment Methods setting page
5. API setting page
6. Export setting page
7. Sync setting page

== Changelog ==
= 1.0.0 - 07.03.2020 =
* Initial stable release.

= 1.0.1 - 07.17.2020 =
* Fix invoiceSavePayment date error.
* add select in which order status add Payment and Invoice.
* limit item name length to 100 character.

= 1.0.2 - 07.17.2020 =
* change some translation strings.

= 1.0.3 - 07.19.2020 =
* use getObjectId() function.
* fix API limit request.
* fix update item before add invoice.

= 1.0.4 - 07.22.2020 =
* change 'not set!' to translatable string.
* fix 100 character limit in item name.

= 1.0.5 - 08.01.2020 =
* add a payment method (No need to set) for COD payment.

= 1.0.6 - 08.08.2020 =
* set reference in ReturnSaleInvoice
* add FiscalYear check
* add itemUpdateOpeningQuantity method
* add Return Sale invoice on canceled order status and sync orders
* add Export product opening quantity
* add validEmail function
* delete item when product deleted
* delete contact when customer deleted
* change order reference to order ID
* fix notice messages

= 1.0.7 - 04.10.2020 =
* compatible with product variations
* add ssbhesabfa_db_version option
* fix getObjectId bug

= 1.0.8 - 10.10.2020 =
* fix fiscalYear checker
* fix empty customer name bug
* fix show notice
* add GuestCustomer function
* add getContactCodeByEmail function
* add DebugMode
* fix webhook quantity change bug

== Upgrade Notice ==
Automatic updates should work smoothly, but we still recommend you back up your site.
