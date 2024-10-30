<?php
/*
 * Plugin Name: iThemes Sales Accelerator
 * Plugin URI: https://ithemes.com/ithemes-sales-accelerator
 * Description: Transform your WordPress dashboard with dynamic reports so you can get detailed data and e-commerce insights about your WooCommerce store. 
 * Version: 1.2.11
 * Author: iThemes
 * Author URI: https://ithemes.com
 * Text Domain: ithemes-sales-accelerator
 * License: GPLv2
 * Domain Path: /assets/translations
 * WC requires at least: 3.0
 * WC tested up to: 3.5
 */

if (! defined( 'ABSPATH' ) ) {
    exit(); // Exit if accessed directly
}

global $wpdb;

// Define some constants
define( 'IT_RST_PLUGIN_FREE_ACTIVE', 1 );

define( 'IT_RST_PLUGIN_FILE', __FILE__ );
define( 'IT_RST_DEBUG_MODE', false );
define( 'IT_RST_PLUGIN_BASENAME', plugin_basename(__FILE__) );
define( 'IT_RST_PLUGIN_PATH', plugin_dir_path(__FILE__) );
define( 'IT_RST_PLUGIN_URL', plugin_dir_url(__FILE__) );
define( 'IT_RST_PLUGIN_VERSION', 1.3 );
define( 'IT_RST_PLUGIN_API_VERSION', '1.1' );
define( 'IT_RST_PLUGIN_DATABASE_PREFIX', $wpdb->prefix . 'rst_' );

// Autoload required files
require_once dirname( __FILE__ ) . '/includes/rooster-start.php';
require_once dirname( __FILE__ ) . '/includes/class-rooster-menu.php';
require_once dirname( __FILE__ ) . '/includes/class-rooster-assets-loader.php';
require_once dirname( __FILE__ ) . '/includes/class-rooster-modules-controller.php';
require_once dirname( __FILE__ ) . '/includes/class-rooster-endpoints.php';
require_once dirname( __FILE__ ) . '/includes/class-rooster-permissions.php';
require_once dirname( __FILE__ ) . '/includes/admin/class-rooster-settings.php';
require_once dirname( __FILE__ ) . '/includes/admin/class-rooster-notifications.php';
require_once dirname( __FILE__ ) . '/includes/admin/class-rooster-general-features.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-view-render.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-rest-response.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-list-table.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-base-functions.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-rooster-logger.php';
require_once dirname( __FILE__ ) . '/includes/external/class-ithemes-api.php';

// Encapsulation of WooCommerce classes
require_once dirname( __FILE__ ) . '/includes/classes/woocommerce/class-wc-it-product.php';
require_once dirname( __FILE__ ) . '/includes/classes/woocommerce/class-wc-it-order.php';
require_once dirname( __FILE__ ) . '/includes/classes/woocommerce/class-wc-it-order-item.php';

require_once dirname( __FILE__ ) . '/includes/endpoints/class-rooster-authentication-rest.php';
require_once dirname( __FILE__ ) . '/includes/database/class-rooster-database.php';

IT_RST_RoosterStart::get_instance();

// Deactivation hook
register_deactivation_hook( IT_RST_PLUGIN_FILE, array( 'IT_RST_RoosterStart', 'deactivate_plugin' ) );
