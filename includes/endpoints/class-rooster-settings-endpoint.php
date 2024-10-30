<?php

if ( !defined( 'ABSPATH' ) ) {
    exit();
}

/**
* This class provides endpoints to access modules status
**/

class IT_RST_Settings_Endpoint {
    
    // Singleton design pattern
    protected static $instance = NULL;
    private $server;
    
    // Method to return the singleton instance
    public static function get_instance() {
        
        if ( null == self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
	    
	    $this->server = new WP_REST_Server();
    }    
    
    // Get rooster settigs
    public function get_settings( $request ) {	    
	    
	    global $wpdb, $it_rst_dashboard_settings, $it_rst_reporting_feature_settings;
	    
	    // Get data from post
		$params = $request->get_params();
		$key 	= $_SERVER['PHP_AUTH_USER'];
		$secret = $_SERVER['PHP_AUTH_PW'];
		
		$user_obj = self::get_user_by_wc_keys( $key, $secret );
		
		if ( $user_obj ) {
								
			// User display name
			$display_name = ( isset( $user_obj->display_name ) && $user_obj->display_name ) ? $user_obj->display_name : $user_obj->user_login;
			
			// User email
			$user_email   = ( isset( $user_obj->user_email )   && $user_obj->user_email )   ? $user_obj->user_email   : '';
			
			// Get user avatar
			$avatar = get_avatar_url( $user_email );
	
			// Site name
			$sitename 	= get_option( 'blogname' );
			$wc_version = ( get_option( 'woocommerce_version' ) ) ? get_option( 'woocommerce_version' ) : 'N/A';
	
			// Currency of WooCommerce Store
			$currency 	     = get_option( 'woocommerce_currency' );
			$order_statuses  = wc_get_order_statuses();
			$thousands_sep   = wc_get_price_thousand_separator();
			$decimals_sep    = wc_get_price_decimal_separator();
			$decimals_num    = wc_get_price_decimals();
			$api_version     = IT_RST_PLUGIN_API_VERSION;
			$weight_unit     = get_option('woocommerce_weight_unit');
			$dimentions_unit = get_option('woocommerce_dimension_unit');
			
			// Symbol of Currency
			$symbol = get_woocommerce_currency_symbol();
	
			// Currency position
			$currency_pos = get_option('woocommerce_currency_pos');
	
			// Number of clients
			$clients = $wpdb->get_var( "select count(*) from " . $wpdb->prefix . "usermeta where meta_value like '%customer%' and meta_key = '" . $wpdb->prefix . "capabilities'" );
	
			// Number of products
			$products   = $wpdb->get_var( "select count(*) from " . $wpdb->prefix . "posts where post_type = 'product' and post_status='publish'" );
			$parameters = array( 'period' => 'year' );
			$response   = self::get_sales_data( $parameters );
			$year 		= $response['sales']['total_sales'];
	
			// Sales
			$sales 		  = $year;
			$s 	          = (string) '&euro;';
			$key 		  = $_SERVER['PHP_AUTH_USER'];
			$secret 	  = $_SERVER['PHP_AUTH_PW'];
			
			$user_obj = self::get_user_by_wc_keys( $key, $secret );
			$user_id = ( isset( $user_obj->ID ) && $user_obj->ID ) ? $user_obj->ID : 0;
			
			if ( class_exists( 'RST_Reporting_Features' ) ) {
				$features_obj = RST_Reporting_Features::get_instance();
				
				// Get feature name
			    foreach ( $it_rst_reporting_feature_settings as $k => $v ) {
				    $it_rst_reporting_feature_settings[$k]['name'] = $features_obj->get_feature_name_by_key( $k );			    
			    }
				
				$features  		= $it_rst_reporting_feature_settings;
			}
			else {
				$features = array();
			}
			$dashboard_sett = $it_rst_dashboard_settings;
			
			$dashboard = array( 'fast_facts' => array(), 'overview' => array(), 'table' => array(), 'charts' => array() ); 
			
			foreach ( $dashboard_sett as $key => $elements ) {
				$elements_array = explode( ',', $elements );
				foreach ( $elements_array as $elm ) {
					$feature_obj 		   = $features[$elm];
					$feature_obj['slug']   = $elm;
					$dashboard[ $key ][]   = $feature_obj;
				}
			}
									
			ob_clean();
			
			return array( 'max_upload_size' => wp_max_upload_size(), 'api_version' => $api_version, 'display_name' => $display_name, 'avatar' => $avatar, 'site_name' => $sitename, 'total_clients' => $clients, 'sales' => round( $sales,2 ), 'currency' => $currency, 'currency_symbol' => html_entity_decode( $symbol,ENT_COMPAT, 'UTF-8' ), 'order_statuses' => $order_statuses, 'currency_pos' => $currency_pos, 'thousands_sep' => $thousands_sep, 'decimals_sep' => $decimals_sep, 'decimals_num' => $decimals_num, 'weight_unit' => $weight_unit, 'dimentions_unit' => $dimentions_unit, 'products' => $products, 'wc_version' => $wc_version, 'options' => array( 'dashboard' => $dashboard ) );
		}
		
		return array( 'display_name' => '', 'avatar' => '', 'site_name' => '', 'total_clients' => '', 'sales' => '', 'currency' => '', 'currency_symbol' => '', 'currency_pos' => '', 'products' => '' );	    
	}
	
	// Get user based on consumer key and secret
    public static function get_user_by_wc_keys( $consumer_key, $consumer_secret ) {
	    
  		global $wpdb;
  		$consumer_key = wc_api_hash( sanitize_text_field( $consumer_key ) );
  		$keys = $wpdb->get_row( $wpdb->prepare( "
  			SELECT user_id
  			FROM {$wpdb->prefix}woocommerce_api_keys
  			WHERE consumer_key = '%s' AND consumer_secret = '%s'
  		", $consumer_key, $consumer_secret ), ARRAY_A );
  		
  		$user = isset( $keys['user_id'] ) ? new WP_User( $keys['user_id'] ) : false;
  		return $user;  		
  	}
  	
  	// Get sales data based on filter
	private static function get_sales_data( $filter ) {
		
		// set date filtering
		$report = self::setup_report( $filter );

		// new customers
		$users_query = new WP_User_Query(
			array(
				'fields' => array( 'user_registered' ),
				'role'   => 'customer',
			)
		);

		$customers = $users_query->get_results();

		foreach ( $customers as $key => $customer ) {
			if ( strtotime( $customer->user_registered ) < $report->start_date || strtotime( $customer->user_registered ) > $report->end_date ) {
				unset( $customers[ $key ] );
			}
		}

		$total_customers = count( $customers );
		$report_data     = $report->get_report_data();
		$period_totals   = array();

		// setup period totals by ensuring each period in the interval has data
		for ( $i = 0; $i <= $report->chart_interval; $i ++ ) {

			switch ( $report->chart_groupby ) {
				case 'day' :
					$time = date( 'Y-m-d', strtotime( "+{$i} DAY", $report->start_date ) );
					break;
				default :
					$time = date( 'Y-m', strtotime( "+{$i} MONTH", $report->start_date ) );
					break;
			}

			// set the customer signups for each period
			$customer_count = 0;
			foreach ( $customers as $customer ) {
				if ( date( ( 'day' == $report->chart_groupby ) ? 'Y-m-d' : 'Y-m', strtotime( $customer->user_registered ) ) == $time ) {
					$customer_count++;
				}
 			}

			$period_totals[ $time ] = array(
				'sales'     => wc_format_decimal( 0.00, 2 ),
				'orders'    => 0,
				'items'     => 0,
				'tax'       => wc_format_decimal( 0.00, 2 ),
				'shipping'  => wc_format_decimal( 0.00, 2 ),
				'discount'  => wc_format_decimal( 0.00, 2 ),
				'customers' => $customer_count,
			);
		}

		// add total sales, total order count, total tax and total shipping for each period
		foreach ( $report_data->orders as $order ) {
			$time = ( 'day' === $report->chart_groupby ) ? date( 'Y-m-d', strtotime( $order->post_date ) ) : date( 'Y-m', strtotime( $order->post_date ) );

			if ( ! isset( $period_totals[ $time ] ) ) {
				continue;
			}

			$period_totals[ $time ]['sales']    = wc_format_decimal( $order->total_sales, 2 );
			$period_totals[ $time ]['tax']      = wc_format_decimal( $order->total_tax + $order->total_shipping_tax, 2 );
			$period_totals[ $time ]['shipping'] = wc_format_decimal( $order->total_shipping, 2 );
		}

		foreach ( $report_data->order_counts as $order ) {
			$time = ( 'day' === $report->chart_groupby ) ? date( 'Y-m-d', strtotime( $order->post_date ) ) : date( 'Y-m', strtotime( $order->post_date ) );

			if ( ! isset( $period_totals[ $time ] ) ) {
				continue;
			}
			$period_totals[ $time ]['orders']   = (int) $order->count;
		}

		// add total order items for each period
		foreach ( $report_data->order_items as $order_item ) {
			$time = ( 'day' === $report->chart_groupby ) ? date( 'Y-m-d', strtotime( $order_item->post_date ) ) : date( 'Y-m', strtotime( $order_item->post_date ) );

			if ( ! isset( $period_totals[ $time ] ) ) {
				continue;
			}
			$period_totals[ $time ]['items'] = (int) $order_item->order_item_count;
		}

		// add total discount for each period
		foreach ( $report_data->coupons as $discount ) {
			$time = ( 'day' === $report->chart_groupby ) ? date( 'Y-m-d', strtotime( $discount->post_date ) ) : date( 'Y-m', strtotime( $discount->post_date ) );

			if ( ! isset( $period_totals[ $time ] ) ) {
				continue;
			}
			$period_totals[ $time ]['discount'] = wc_format_decimal( $discount->discount_amount, 2 );
		}

		$sales_data  = array(
			'total_sales'       => $report_data->total_sales,
			'net_sales'         => $report_data->net_sales,
			'average_sales'     => $report_data->average_sales,
			'total_orders'      => $report_data->total_orders,
			'total_items'       => $report_data->total_items,
			'total_tax'         => wc_format_decimal( $report_data->total_tax + $report_data->total_shipping_tax, 2 ),
			'total_shipping'    => $report_data->total_shipping,
			'total_refunds'     => $report_data->total_refunds,
			'total_discount'    => $report_data->total_coupons,
			'totals_grouped_by' => $report->chart_groupby,
			'totals'            => $period_totals,
			'total_customers'   => $total_customers,
		);

		return array( 'sales' => apply_filters( 'woocommerce_api_report_response', $sales_data, $report, array(), null ) );
	}    
	
	private static function setup_report( $filter ) {

		include_once( WC()->plugin_path() . '/includes/admin/reports/class-wc-admin-report.php' );
		include_once( WC()->plugin_path() . '/includes/admin/reports/class-wc-report-sales-by-date.php' );

		$report = new WC_Report_Sales_By_Date();

		if ( empty( $filter['period'] ) ) {

			// custom date range
			$filter['period'] = 'custom';
			$_GET['start_date'] = $_GET['end_date'] = date( 'Y-m-d', current_time( 'timestamp' ) );
		} else {

			// ensure period is valid
			if ( ! in_array( $filter['period'], array( 'week', 'month', 'last_month', 'year' ) ) ) {
				$filter['period'] = 'week';
			}

			if ( 'week' === $filter['period'] ) {
				$filter['period'] = '7day';
			}
		}

		$report->calculate_current_range( $filter['period'] );
		return $report;
	}
}