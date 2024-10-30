<?php

if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}
	
/**
* This class handles module Features
**/

class RST_Reporting_Features {
	
	// Singleton design pattern
    protected static $instance = NULL;
    protected $features = array();
    
    // Method to return the singleton instance
    public static function get_instance() {
	    
        if ( null == self::$instance ) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
	
	public function __construct() {
		
		$this->define_features();
	}
	
	// Define reporting module features
	public function define_features(){		
		
		global $it_rst_reporting_feature_settings;
		
		$features = $it_rst_reporting_feature_settings;
		
		// Refresh
		$products_available_refresh   	    = ( isset( $features['products_available']['refresh'] ) && $features['products_available']['refresh'] ) ? $features['products_available']['refresh'] : 10000;
		$waiting_payment_refresh       	    = ( isset( $features['waiting_payment']['refresh'] ) && $features['waiting_payment']['refresh'] ) ? $features['waiting_payment']['refresh'] : 10000;
		$average_markup_refresh		   	    = ( isset( $features['average_markup']['refresh'] ) && $features['average_markup']['refresh'] ) ? $features['average_markup']['refresh'] : 10000;
		$total_customers_refresh 	   	    = ( isset( $features['total_customers']['refresh'] ) && $features['total_customers']['refresh'] ) ? $features['total_customers']['refresh'] : 10000;
		$available_quantities_refresh  	    = ( isset( $features['available_quantities_ff']['refresh'] ) && $features['available_quantities_ff']['refresh'] ) ? $features['available_quantities_ff']['refresh'] : 10000;
		$low_stock_refresh 			   	    = ( isset( $features['low_stock']['refresh'] ) && $features['low_stock']['refresh'] ) ? $features['low_stock']['refresh'] : 10000;
		$waiting_shipped_refresh 	   	    = ( isset( $features['waiting_shipped']['refresh'] ) && $features['waiting_shipped']['refresh'] ) ? $features['waiting_shipped']['refresh'] : 10000;
		$out_of_stock_refresh 		   	    = ( isset( $features['out_of_stock']['refresh'] ) && $features['out_of_stock']['refresh'] ) ? $features['out_of_stock']['refresh'] : 10000;
		$in_stock_refresh 		       	    = ( isset( $features['in_stock']['refresh'] ) && $features['in_stock']['refresh'] ) ? $features['in_stock']['refresh'] : 10000;
		$best_category_refresh 		   	    = ( isset( $features['best_category']['refresh'] ) && $features['best_category']['refresh'] ) ? $features['best_category']['refresh'] : 10000;
		$best_customers_refresh 	   	    = ( isset( $features['best_customers']['refresh'] ) && $features['best_customers']['refresh'] ) ? $features['best_customers']['refresh'] : 10000;
		$best_product_refresh 		   	    = ( isset( $features['best_product']['refresh'] ) && $features['best_product']['refresh'] ) ? $features['best_product']['refresh'] : 10000;
		$average_price_refresh 		   	    = ( isset( $features['average_price_order']['refresh'] ) && $features['average_price_order']['refresh'] ) ? $features['average_price_order']['refresh'] : 10000;
		$average_price_product_refresh 	    = ( isset( $features['average_price_product']['refresh'] ) && $features['average_price_product']['refresh'] ) ? $features['average_price_product']['refresh'] : 10000;
		$products_bought_refresh 	  	    = ( isset( $features['products_bought']['refresh'] ) && $features['products_bought']['refresh'] ) ? $features['products_bought']['refresh'] : 10000;
		$total_sales_refresh 		  	    = ( isset( $features['total_sales']['refresh'] ) && $features['total_sales']['refresh'] ) ? $features['total_sales']['refresh'] : 10000;	
		$total_sales_ff_refresh 	  	    = ( isset( $features['total_sales_ff']['refresh'] ) && $features['total_sales_ff']['refresh'] ) ? $features['total_sales_ff']['refresh'] : 10000;	
		$new_clients_refresh 		  	    = ( isset( $features['new_clients']['refresh'] ) && $features['new_clients']['refresh'] ) ? $features['new_clients']['refresh'] : 10000;
		$shipping_method_refresh 	  	    = ( isset( $features['shipping_method']['refresh'] ) && $features['shipping_method']['refresh'] ) ? $features['shipping_method']['refresh'] : 10000;
		$total_refunds_refresh 		  	    = ( isset( $features['total_refunds']['refresh'] ) && $features['total_refunds']['refresh'] ) ? $features['total_refunds']['refresh'] : 10000;
		$total_coupons_refresh 		  	    = ( isset( $features['total_coupons']['refresh'] ) && $features['total_coupons']['refresh'] ) ? $features['total_coupons']['refresh'] : 10000;
		$best_country_refresh 		  	    = ( isset( $features['best_country']['refresh'] ) && $features['best_country']['refresh'] ) ? $features['best_country']['refresh'] : 10000;
		$best_city_refresh 		     	    = ( isset( $features['best_city']['refresh'] ) && $features['best_city']['refresh'] ) ? $features['best_city']['refresh'] : 10000;
		$payment_methods_refresh 	  	    = ( isset( $features['payment_methods']['refresh'] ) && $features['payment_methods']['refresh'] ) ? $features['payment_methods']['refresh'] : 10000;
		$category_by_country_refresh  	    = ( isset( $features['category_by_country']['refresh'] ) && $features['category_by_country']['refresh'] ) ? $features['category_by_country']['refresh'] : 10000;
		$zone_distribution_refresh          = ( isset( $features['zone_distribution']['refresh'] ) && $features['zone_distribution']['refresh'] ) ? $features['zone_distribution']['refresh'] : 10000;
		$orders_status_refresh 		        = ( isset( $features['orders_status']['refresh'] ) && $features['orders_status']['refresh'] ) ? $features['orders_status']['refresh'] : 10000;
		$orders_operating_system_refresh    = ( isset( $features['orders_operating_system']['refresh'] ) && $features['orders_operating_system']['refresh'] ) ? $features['orders_operating_system']['refresh'] : 10000;
		$orders_browsers_refresh 		    = ( isset( $features['orders_browsers']['refresh'] ) && $features['orders_browsers']['refresh'] ) ? $features['orders_browsers']['refresh'] : 10000;
		$type_products_refresh 		 	    = ( isset( $features['type_products']['refresh'] ) && $features['type_products']['refresh'] ) ? $features['type_products']['refresh'] : 10000;
		$customers_guests_refresh 		    = ( isset( $features['customers_guests']['refresh'] ) && $features['customers_guests']['refresh'] ) ? $features['customers_guests']['refresh'] : 10000;
		$top_countries_refresh 			    = ( isset( $features['top_countries']['refresh'] ) && $features['top_countries']['refresh'] ) ? $features['top_countries']['refresh'] : 10000;
		$orders_placed_payed_refresh 	    = ( isset( $features['orders_placed_payed']['refresh'] ) && $features['orders_placed_payed']['refresh'] ) ? $features['orders_placed_payed']['refresh'] : 10000;
		$returning_new_refresh 			    = ( isset( $features['returning_new']['refresh'] ) && $features['returning_new']['refresh'] ) ? $features['returning_new']['refresh'] : 10000;
		$compare_products_refresh 		    = ( isset( $features['compare_products']['refresh'] ) && $features['compare_products']['refresh'] ) ? $features['compare_products']['refresh'] : 10000;
		$spend_day_refresh 				    = ( isset( $features['spend_day']['refresh'] ) && $features['spend_day']['refresh'] ) ? $features['spend_day']['refresh'] : 10000;
		$spend_week_refresh 			    = ( isset( $features['spend_week']['refresh'] ) && $features['spend_week']['refresh'] ) ? $features['spend_week']['refresh'] : 10000;
		$spend_hour_refresh 			    = ( isset( $features['spend_hour']['refresh'] ) && $features['spend_hour']['refresh'] ) ? $features['spend_hour']['refresh'] : 10000;
		$sales_by_channel_refresh 			= ( isset( $features['sales_by_channel']['refresh'] ) && $features['sales_by_channel']['refresh'] ) ? $features['sales_by_channel']['refresh'] : 10000;
		$stocks_by_warehouse_refresh		= ( isset( $features['stocks_by_warehouse']['refresh'] ) && $features['stocks_by_warehouse']['refresh'] ) ? $features['stocks_by_warehouse']['refresh'] : 10000;
		
		$best_customer_ff_refresh           =  10000;
		$customer_vs_guests_ff_refresh      =  10000;
		$recurringvsnewcustomers_ff_refresh =  10000;
		$product_variations_refresh		    =  10000;
		
		// Live updates
		$products_available_live         = ( isset( $features['products_available']['live'] ) ) ? $features['products_available']['live'] : true;
		$waiting_payment_live            = ( isset( $features['waiting_payment']['live'] )  ) ? $features['waiting_payment']['live'] : true;
		$average_markup_live	         = ( isset( $features['average_markup']['live'] ) ) ? $features['average_markup']['live'] : 10000;
		$total_customers_live 	         = ( isset( $features['total_customers']['live'] ) ) ? $features['total_customers']['live'] : true;
		$available_quantities_live       = ( isset( $features['available_quantities_ff']['live'] ) )  ? $features['available_quantities_ff']['live'] : true;
		$low_stock_live 		         = ( isset( $features['low_stock']['live'] )  ) ? $features['low_stock']['live'] : true;
		$waiting_shipped_live 	         = ( isset( $features['waiting_shipped']['live'] ) ) ? $features['waiting_shipped']['live'] : true;
		$out_of_stock_live 		         = ( isset( $features['out_of_stock']['live'] ) ) ? $features['out_of_stock']['live'] : true;
		$in_stock_live 			         = ( isset( $features['in_stock']['live'] ) ) ? $features['in_stock']['live'] : true;
		$best_category_live 	         = ( isset( $features['best_category']['live'] ) ) ? $features['best_category']['live'] : true;
		$best_product_live 			     = ( isset( $features['best_product']['live'] ) ) ? $features['best_product']['live'] : true;
		$average_price_live 		     = ( isset( $features['average_price_order']['live'] ) ) ? $features['average_price_order']['live'] : true;
		$average_price_product_live      = ( isset( $features['average_price_product']['live'] ) ) ? $features['average_price_product']['live'] : true;
		$products_bought_live 		     = ( isset( $features['products_bought']['live'] ) ) ? $features['products_bought']['live'] : true;	
		$best_customers_live 		     = ( isset( $features['best_customers']['live'] ) ) ? $features['best_customers']['live'] : true;	
		$total_sales_live 			     = ( isset( $features['total_sales']['live'] ) ) ? $features['total_sales']['live'] : true;	
		$total_sales_ff_live 		     = ( isset( $features['total_sales_ff']['live'] ) ) ? $features['total_sales_ff']['live'] : true;	
		$new_clients_live 			     = ( isset( $features['new_clients']['live'] ) ) ? $features['new_clients']['live'] : true;
		$shipping_method_live 		     = ( isset( $features['shipping_method']['live'] )  ) ? $features['shipping_method']['live'] : true;
		$total_refunds_live 		     = ( isset( $features['total_refunds']['live'] ) ) ? $features['total_refunds']['live'] : true;
		$total_coupons_live 		     = ( isset( $features['total_coupons']['live'] ) ) ? $features['total_coupons']['live'] : true;
		$best_country_live 			     = ( isset( $features['best_country']['live'] ) ) ? $features['best_country']['live'] : true;
		$best_city_live 			     = ( isset( $features['best_city']['live'] ) ) ? $features['best_city']['live'] : true;
		$payment_methods_live 		     = ( isset( $features['payment_methods']['live'] ) ) ? $features['payment_methods']['live'] : true;
		$category_by_country_live 	     = ( isset( $features['category_by_country']['live'] ) ) ? $features['category_by_country']['live'] : true;
		$zone_distribution_live 	     = ( isset( $features['zone_distribution']['live'] ) ) ? $features['zone_distribution']['live'] : true;
		$orders_status_live 		     = ( isset( $features['orders_status']['live'] ) ) ? $features['orders_status']['live'] : true;
		$orders_operating_system_live    = ( isset( $features['orders_operating_system']['live'] ) ) ? $features['orders_operating_system']['live'] : true;
		$orders_browsers_live 		     = ( isset( $features['orders_browsers']['live'] ) ) ? $features['orders_browsers']['live'] : true;
		$type_products_live 		     = ( isset( $features['type_products']['live'] ) ) ? $features['type_products']['live'] : true;
		$customers_guests_live 		     = ( isset( $features['customers_guests']['live'] ) ) ? $features['customers_guests']['live'] : true;
		$top_countries_live 		     = ( isset( $features['top_countries']['live'] ) ) ? $features['top_countries']['live'] : true;
		$orders_placed_payed_live 	     = ( isset( $features['orders_placed_payed']['live'] ) ) ? $features['orders_placed_payed']['live'] : true;
		$returning_new_live 		     = ( isset( $features['returning_new']['live'] ) ) ? $features['returning_new']['live'] : true;
		$compare_products_live 		     = ( isset( $features['compare_products']['live'] ) ) ? $features['compare_products']['live'] : true;
		$spend_day_live 			     = ( isset( $features['spend_day']['live'] ) )  ? $features['spend_day']['live'] : true;
		$spend_week_live 			     = ( isset( $features['spend_week']['live'] ) ) ? $features['spend_week']['live'] : true;
		$spend_hour_live 			     = ( isset( $features['spend_hour']['live'] ) ) ? $features['spend_hour']['live'] : true;
		$sales_by_channel_live	         = ( isset( $features['sales_by_channel']['live'] ) && $features['sales_by_channel']['live'] ) ? $features['sales_by_channel']['live'] : true;
		$stocks_by_warehouse_live		 = ( isset( $features['stocks_by_warehouse']['live'] ) && $features['stocks_by_warehouse']['live'] ) ? $features['stocks_by_warehouse']['live'] : 10000;
		
		$best_customer_ff_live        	 =  true;
		$customer_vs_guests_ff_live      =  true;
		$recurringvsnewcustomers_ff_live =  true;
		$product_variations_live		 =  true;
		
		$features = array( 
						array( 'key' => 'products_available', 'value' => 'Products Available', 'refresh' => $products_available_refresh, 'type' => array( 'fastfacts' ), 'premium' => array( false ), 'live' => $products_available_live ), 
						array( 'key' => 'waiting_payment', 'value' => 'Waiting Payment', 'refresh' => $waiting_payment_refresh, 'type' => array( 'fastfacts' ), 'premium' => array( true ), 'live' => $waiting_payment_live ), 
						array( 'key' => 'average_markup', 'value' => 'Average Markup', 'refresh' => $average_markup_refresh, 'type' => array( 'fastfacts' ), 'premium' => array( true ), 'live' => $average_markup_live ), 
						array( 'key' => 'total_customers', 'value' => 'Total Customers', 'refresh' => $total_customers_refresh, 'type' => array( 'fastfacts' ), 'premium' => array( false ), 'live' => $total_customers_live ),
						array( 'key' => 'available_quantities_ff', 'value' => 'Available Quantities', 'refresh' => $available_quantities_refresh, 'type' => array( 'fastfacts', 'table' ), 'premium' => array( true, true ), 'live' => $available_quantities_live, 'filterable' => false ),
						array( 'key' => 'low_stock', 'value' => 'Low Stock Products', 'refresh' => $low_stock_refresh, 'type' => array( 'fastfacts' ), 'premium' => array( true ), 'live' => $low_stock_live ),
						array( 'key' => 'waiting_shipped', 'value' => 'Waiting Shipment', 'refresh' => $waiting_shipped_refresh, 'type' => array( 'fastfacts' ), 'premium' => array( true ), 'live' => $waiting_shipped_live ),
						array( 'key' => 'out_of_stock', 'value' => 'Products Out of Stock', 'refresh' => $out_of_stock_refresh, 'type' => array( 'fastfacts', 'table' ), 'premium' => array( false, true ), 'live' => $out_of_stock_live, 'filterable' => false ),
						array( 'key' => 'in_stock', 'value' => 'Products In Stock', 'refresh' => $in_stock_refresh, 'type' => array( 'fastfacts', 'table' ), 'premium' => array( true, true ), 'live' => $in_stock_live, 'filterable' => false ),
						array( 'key' => 'best_category', 'value' => 'Best Category', 'refresh' => $best_category_refresh, 'type' => array( 'overview', 'table' ), 'premium' => array( false, true ), 'live' => $best_category_live, 'filterable' => true ),
						array( 'key' => 'best_product', 'value' => 'Best Product', 'refresh' => $best_product_refresh, 'type' => array( 'overview', 'table' ), 'premium' => array( true, true ), 'live' => $best_product_live, 'filterable' => true ),
						array( 'key' => 'average_price_order', 'value' => 'Average Order Amount', 'refresh' => $average_price_refresh, 'type' => array( 'overview' ), 'premium' => array( true ), 'live' => $average_price_live ),
						array( 'key' => 'average_price_product', 'value' => 'Average Product Amount per Order', 'refresh' => $average_price_product_refresh, 'type' => array( 'overview' ), 'premium' => array( true ), 'live' => $average_price_product_live ),
						array( 'key' => 'products_bought', 'value' => 'Products Bought', 'refresh' => $products_bought_refresh, 'type' => array( 'overview' ), 'premium' => array( true ), 'live' => $products_bought_live ),
						array( 'key' => 'total_sales', 'value' => 'Total Sales', 'refresh' => $total_sales_refresh, 'type' => array( 'overview' ), 'premium' => array( false ), 'live' => $total_sales_live ),
						array( 'key' => 'total_sales_web', 'value' => 'Total Sales (Web)', 'refresh' => $total_sales_refresh, 'type' => array( 'overview' ), 'premium' => array( true ), 'live' => $total_sales_live, 'min_pro_version' => 1.3 ),
						array( 'key' => 'total_sales_ebay', 'value' => 'Total Sales (Ebay)', 'refresh' => $total_sales_refresh, 'type' => array( 'overview' ), 'premium' => array( true ), 'live' => $total_sales_live, 'min_pro_version' => 1.3 ),
						array( 'key' => 'total_sales_amazon', 'value' => 'Total Sales (Amazon)', 'refresh' => $total_sales_refresh, 'type' => array( 'overview' ), 'premium' => array( true ), 'live' => $total_sales_live, 'min_pro_version' => 1.3 ),
						array( 'key' => 'total_sales_facebook', 'value' => 'Total Sales (Facebook)', 'refresh' => $total_sales_refresh, 'type' => array( 'overview' ), 'premium' => array( true ), 'live' => $total_sales_live, 'min_pro_version' => 1.4 ),
						array( 'key' => 'total_sales_google', 'value' => 'Total Sales (Google Merchant)', 'refresh' => $total_sales_refresh, 'type' => array( 'overview' ), 'premium' => array( true ), 'live' => $total_sales_live, 'min_pro_version' => 1.31 ),
						array( 'key' => 'total_sales_pos', 'value' => 'Total Sales (POS)', 'refresh' => $total_sales_refresh, 'type' => array( 'overview' ), 'premium' => array( true ), 'live' => $total_sales_live, 'min_pro_version' => 1.4 ),
						array( 'key' => 'total_sales_ff', 'value' => 'Total Sales', 'refresh' => $total_sales_ff_refresh, 'type' => array( 'fastfacts' ), 'premium' => array( false ), 'live' => $total_sales_ff_live, 'min_pro_version' => 1.3 ),
						array( 'key' => 'new_clients', 'value' => 'New Clients', 'refresh' => $new_clients_refresh, 'type' => array( 'overview' ), 'premium' => array( true ), 'live' => $new_clients_live ),
						array( 'key' => 'shipping_method', 'value' => 'Shipping Method', 'refresh' => $shipping_method_refresh, 'type' => array( 'overview', 'charts' ), 'premium' => array( false, false ), 'live' => $shipping_method_live, 'type_chart' => 'lines' ),
						array( 'key' => 'best_customers', 'value' => 'Best Customers', 'refresh' => $best_customers_refresh, 'type' => array( 'table' ), 'premium' => array( false ), 'live' => $best_customers_live, 'filterable' => true ),
						array( 'key' => 'total_refunds', 'value' => 'Refunds', 'refresh' => $total_refunds_refresh, 'type' => array( 'overview', 'table' ), 'premium' => array( false, true ), 'live' => $total_refunds_live, 'filterable' => true ),
						array( 'key' => 'total_coupons', 'value' => 'Total Coupons', 'refresh' => $total_coupons_refresh, 'type' => array( 'overview', 'table' ), 'premium' => array( false, true ), 'live' => $total_coupons_live, 'filterable' => true ),
						array( 'key' => 'best_country', 'value' => 'Best Country', 'refresh' => $best_country_refresh, 'type' => array( 'overview' ), 'premium' => array( false ), 'live' => $best_country_live ),
						array( 'key' => 'best_city', 'value' => 'Best City', 'refresh' => $best_city_refresh, 'type' => array( 'overview' ), 'premium' => array( true ), 'live' => $best_city_live, 'db_version_needed' => 1.04 ),
						array( 'key' => 'payment_methods', 'value' => 'Payment Methods', 'refresh' => $payment_methods_refresh, 'type' => array( 'table' ), 'premium' => array( true ), 'live' => $payment_methods_live, 'filterable' => true ),
						array( 'key' => 'category_by_country', 'value' => 'Category Distribution by Country', 'refresh' => $category_by_country_refresh, 'type' => array( 'table' ), 'premium' => array( true ), 'live' => $category_by_country_live, 'filterable' => true ),
						array( 'key' => 'zone_distribution', 'value' => 'Zone Distribution', 'refresh' => $zone_distribution_refresh, 'type' => array( 'table' ), 'premium' => array( true ), 'live' => $zone_distribution_live, 'filterable' => true ),
						array( 'key' => 'orders_status', 'value' => 'Orders by Order Status', 'refresh' => $orders_status_refresh, 'type' => array( 'table' ), 'premium' => true, 'live' => $orders_status_live, 'filterable' => true ),
						array( 'key' => 'orders_operating_system', 'value' => 'Orders by Operating System', 'refresh' => $orders_operating_system_refresh, 'type' => array( 'table' ), 'premium' => array( true ), 'live' => $orders_operating_system_live, 'filterable' => true ),
						array( 'key' => 'orders_browsers', 'value' => 'Orders by Browsers', 'refresh' => $orders_browsers_refresh, 'type' => array( 'table' ), 'premium' => array( true ), 'live' => $orders_browsers_live, 'filterable' => true ),
						array( 'key' => 'type_products', 'value' => 'Type of Products', 'refresh' => $type_products_refresh, 'type' => array( 'table' ), 'premium' => array( true ), 'live' => $type_products_live, 'filterable' => true ),
						array( 'key' => 'customers_guests', 'value' => 'Customers vs Guests', 'refresh' => $customers_guests_refresh, 'type' => array( 'charts' ), 'premium' => array( false ), 'live' => $customers_guests_live, 'type_chart' => 'donut' ),
						array( 'key' => 'top_countries', 'value' => 'Top Countries', 'refresh' => $top_countries_refresh, 'type' => array( 'charts', 'table' ), 'premium' => array( true, false ), 'live' => $top_countries_live, 'filterable' => true, 'type_chart' => 'lines' ),
						array( 'key' => 'orders_placed_payed', 'value' => 'Orders Placed vs Orders Paid', 'refresh' => $orders_placed_payed_refresh, 'type' => array( 'charts' ), 'premium' => array( true ), 'live' => $orders_placed_payed_live, 'type_chart' => 'donut' ),
						array( 'key' => 'returning_new', 'value' => 'Returning vs New Customers', 'refresh' => $returning_new_refresh, 'type' => array( 'charts' ), 'premium' => array( true ), 'live' => $returning_new_live, 'type_chart' => 'donut' ),
						
						// Names changed from Spend by to Sales by on v1.0.4
						array( 'key' => 'spend_day', 'value' => 'Sales by Day', 'refresh' => $spend_day_refresh, 'type' => array( 'charts', 'table' ), 'premium' => array( true, true ), 'live' => $spend_day_live, 'filterable' => true, 'type_chart' => 'lines' ),
						array( 'key' => 'spend_week', 'value' => 'Sales by Week', 'refresh' => $spend_week_refresh, 'type' => array( 'charts', 'table' ), 'premium' => array( true, true ), 'live' => $spend_week_live, 'filterable' => true, 'type_chart' => 'lines' ),
						array( 'key' => 'spend_hour', 'value' => 'Sales by Hour', 'refresh' => $spend_hour_refresh, 'type' => array( 'charts', 'table' ), 'premium' => array( true, true ), 'live' => $spend_hour_live, 'filterable' => true, 'type_chart' => 'lines' ),
						
						// POC features	
						array( 'key' => 'list_products', 'value' => 'List of Products', 'refresh' => 10000, 'type' => array( 'table' ), 'premium' => array( true ), 'live' => true, 'filterable' => true, 'available' => false ),
						array( 'key' => 'last_products', 'value' => 'Last Products', 'refresh' => 10000, 'type' => array( 'table' ), 'premium' => array( true ), 'live' => true, 'filterable' => true, 'available' => false ),
						array( 'key' => 'customer_orders', 'value' => 'Last Orders', 'refresh' => 10000, 'type' => array( 'table' ), 'premium' => array( true ), 'live' => true, 'filterable' => false, 'available' => false ),
						array( 'key' => 'list_orders', 'value' => 'Orders', 'refresh' => 10000, 'type' => array( 'table' ), 'premium' => array( false, true ), 'live' => true, 'filterable' => true, 'available' => false ),
						array( 'key' => 'list_customers', 'value' => 'Customers', 'refresh' => 10000, 'type' => array( 'table' ), 'premium' => array( false, true ), 'live' => true, 'filterable' => true, 'available' => false ),
						array( 'key' => 'best_customer_ff', 'value' => 'Best Customer', 'refresh' => $best_customer_ff_refresh, 'type' => array( 'fastfacts' ), 'premium' => array( true ), 'live' => $best_customer_ff_live, 'available' => false ),
						array( 'key' => 'customer_vs_guests_ff', 'value' => 'Customer vs Guests', 'refresh' => $customer_vs_guests_ff_refresh, 'type' => array( 'fastfacts' ), 'premium' => array( true ), 'live' => $customer_vs_guests_ff_live, 'available' => false ),
						array( 'key' => 'recurring_vs_new_customers_ff', 'value' => 'Recurring vs New Customers', 'refresh' => $recurringvsnewcustomers_ff_refresh, 'type' => array( 'fastfacts' ), 'premium' => array( true ), 'live' => $recurringvsnewcustomers_ff_live, 'available' => false ),
						array( 'key' => 'product_variations', 'value' => 'Product Variations', 'refresh' => $product_variations_refresh, 'type' => array( 'fastfacts' ), 'premium' => array( true ), 'live' => $product_variations_live, 'filterable' => true, 'available' => false ),
						array( 'key' => 'product_sales', 'value' => 'Product Sales', 'refresh' => 10000, 'type' => array( 'charts' ), 'premium' => array( true ), 'live' => true, 'type_chart' => 'lines', 'available' => false ),
						array( 'key' => 'sales_by_channel', 'value' => 'Sales by Channel', 'refresh' => $sales_by_channel_refresh, 'type' => array( 'charts' ), 'premium' => array( true ), 'live' => $sales_by_channel_live, 'type_chart' => 'lines', 'available' => true, 'min_pro_version' => 1.3 ),
						array( 'key' => 'stocks_by_warehouse', 'value' => 'Stock by Warehouse', 'refresh' => $stocks_by_warehouse_refresh, 'type' => array( 'charts' ), 'premium' => array( true ), 'live' => $stocks_by_warehouse_live, 'type_chart' => 'lines', 'available' => true, 'min_pro_version' => 1.3, 'required_module' => 'rooster_warehouses' ),
				);	
					
			$features = apply_filters( 'it_rst_reporting_features', $features );	
			$this->features = $features;            
	}
	
	// Get features by type
	public function get_features( $type = 'all', $premium = false, $hide_unavailable = false ) {

		if ( $type == 'all' ) {
			$all_features = array();
			foreach ( $this->features as $feature ) {
				if ( !isset( $feature['available'] ) || $feature['available'] ) {
					$all_features[] = $feature;
				}
			}
			return $all_features;
		}
		else {
			$db_version = get_option( 'it_rooster_reporting_db_version' );
			$db_upgrade = get_option( 'it_rooster_reporting_db_upgrade_status' );
			
			$filtered_features = array();
			foreach ( $this->features as $feature ) {
				if ( isset( $feature['type'] ) && in_array( $type, $feature['type'] ) && ( !isset( $feature['available'] ) || $feature['available'] ) ) {
					if ( !$hide_unavailable || !isset( $feature['db_version_needed'] ) || ( $feature['db_version_needed'] <= $db_version && !isset( $db_upgrade[str_replace( '.', '_', $feature['db_version_needed'] )] ) ) || ( $feature['db_version_needed'] <= $db_version && isset( $db_upgrade[str_replace( '.', '_', $feature['db_version_needed'] )]['status'] ) && $db_upgrade[str_replace( '.', '_', $feature['db_version_needed'] )]['status'] == 'completed' ) ) {
						$key = array_search( $type, $feature['type'] );
						if ( !$premium && $key !== false && isset( $feature['premium'][ $key ] ) && !$feature['premium'][ $key ] ) {
							$filtered_features[] = $feature;
						}
						else if ( $premium ) {
							$filtered_features[] = $feature;
						} 
					}
				}
			}
			
			return $filtered_features;
		}
	}
	
	// Get features name by key
	public function get_feature_name_by_key( $key ) {
		
		foreach ( $this->features as $feature ) {
			if ( $feature['key'] == $key ) {
				return $feature['value'];
			}
		}
		
		return 0;
	}
	
	// Get feature data by key
	public function get_feature_data_by_key( $key ) {
		
		foreach ( $this->features as $feature ) {
			if ( $feature['key'] == $key ) {
				return $feature;
			}
		}
		
		return 0;
	}
	
	// Checks if feature is filterable
	public function is_feature_filterable( $key ) {
		
		foreach ( $this->features as $feature ) {
			if ( $feature['key'] == $key ) {
				$return = isset( $feature['filterable'] ) ? $feature['filterable'] : false;
				return $return;
			}
		}
		
		return 0;
	}
	
	public function is_feature_version_ready( $key ) {
		foreach ( $this->features as $feature ) {
			if ( $feature['key'] == $key ) {
				$min_pro 		= isset( $feature['min_pro_version'] ) ? $feature['min_pro_version'] : 0;
				$require_module = isset( $feature['required_module'] ) ? $feature['required_module'] : '';
				$rp_version     = ( defined( 'IT_RST_RP_PLUGIN_VERSION' ) ) ? IT_RST_RP_PLUGIN_VERSION : 0;
				if ( $min_pro && $min_pro > $rp_version ) {
					return  __( 'The report', 'ithemes-sales-accelerator' ) . ' ' . $feature['value'] . ' ' . __( 'requires Reporting Pro version', 'ithemes-sales-accelerator' ) . ' ' . $feature['min_pro_version'] . ' ' . __( 'or newer', 'ithemes-sales-accelerator' ) . '.';
				}
				switch( $require_module ) {
					case 'rooster_warehouses':
					if ( !defined( 'IT_RST_WH_PREMIUM_ACTIVE' ) ) {
						return __( 'The report', 'ithemes-sales-accelerator' ) . ' ' . $feature['value'] . ' ' . __( 'requires the Inventory module to be active', 'ithemes-sales-accelerator' ) . '.';
					}
					default:
						break;
				}
			}
		}
		
		return '';
	}
	
	// Checks if chart is of Lines type
	public function is_lines_chart( $key ) {
		
		foreach ( $this->features as $feature ) {
			if ( $feature['key'] == $key ) {
				$return = isset( $feature['type_chart'] ) ? $feature['type_chart'] : '';
				return $return;
			}
		}
		
		return 0;
	}
}