<?php
	
if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

add_filter( 'it_rst_available_modules', 'it_rst_available_modules', 10, 1 );

function it_rst_available_modules( $modules ) {
	
	$reporting = array( 'slug' => 'rooster_reporting', 'menu_name' => __( 'Dashboard', 'ithemes-sales-accelerator'), 'available' => true, 'menu_slug' => 'ithemes-sales-acc-plugin-reporting-dashboard', 'name' => 'Reporting', 'class' => 'RST_Reporting', 'description' => 'Add advanced reporting features to WooCommerce.', 'sales_page' => 'https://ithemes.com/sales-accelerator/woocommerce-reports/', 'path' => 'reporting/class-reporting.php', 'image' => IT_RST_PLUGIN_URL . '/assets/img/reports_grey.svg', 
	'modal_txt' => '<p>iThemes Sales Accelerator allows you get detailed data and e-commerce insights about your online store.</p>
												<p>With the Reports Module, you can activate advanced reports so you can see sales and customer stats that matter most.</p>
												<p>Your WordPress dashboard will be transformed so that all the most important stats about your store are in one place, organized into dynamic charts, graphs and tables. With customizable settings, you can choose which stats to include in your reports.</p>' );
												
	$warehouses 	= array( 'slug' => 'rooster_warehouses', 'sales_page' => 'https://ithemes.com/sales-accelerator/woocommerce-inventory/', 'external' => true, 'available' => false, 'name' => 'Inventory', 'class' => 'RST_Warehouses', 'description' => 'Manage the stock of your products with multiple warehouses.', 'path' => 'warehouses/class-warehouses.php', 'image' => IT_RST_PLUGIN_URL . '/assets/img/inventory_grey.svg', 'modal_txt' => '<p>The Warehouses Module will add the ability to manage the stock of your products with multiple warehouses.</p>' );
	
	$omnichannel  	= array( 'slug' => 'rooster_omnichannel','menu_slug' => 'ithemes-sales-acc-plugin-omnichannel-main', 'sales_page' => 'https://ithemes.com/sales-accelerator/woocommerce-omnichannel/', 'external' => true, 'available' => false, 'name' => 'MultiChannel', 'class' => 'RST_OmniChannel', 'description' => 'Connects your WooCommerce store with eBay, Amazon, Facebook and Google Merchant.', 'path' => 'omnichannel/class-omnichannel.php', 'image' => IT_RST_PLUGIN_URL . '/assets/img/omnichannel_grey.svg', 'modal_txt' => '<p>The MultiChannel Module will add the ability to connect your WooCommerce store with Ebay.</p>' );
	
	$abandoned_cart = array( 'slug' => 'rooster_abandoned_cart', 'external' => true, 'available' => false, 'name' => 'Abandoned Cart', 'class' => 'RST_Abandoned_Cart', 'description' => 'Adds abandoned cart capabilities to WooCommerce.', 'path' => 'abandoned-cart/class-abandoned-cart.php', 'image' => IT_RST_PLUGIN_URL . '/assets/img/abandoned_cart_grey.svg', 'modal_txt' => '<p>The Abandoned cart module adds abandoned cart capabilities to WooCommerce.</p>' );
			
	$modules[] = $reporting;
	$modules[] = $warehouses;
	$modules[] = $omnichannel;
	$modules[] = $abandoned_cart;
	return $modules;
}