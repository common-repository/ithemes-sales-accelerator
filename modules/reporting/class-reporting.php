<?php
	
if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

/**
* This class handles reporting module init 
**/

class RST_Reporting {
	
	public function __construct() {
		
    	$this->init();	    	
	}

	private function init() {
		
		$this->add_constants();
		$this->includes();
	}
	
	// Adds module constants
	public function add_constants() {
		
		define( 'IT_RST_REPORTING_ORDERS_DATABASE', 		IT_RST_PLUGIN_DATABASE_PREFIX . 'orders' );
		define( 'IT_RST_REPORTING_ORDER_PRODUCTS_DATABASE', IT_RST_PLUGIN_DATABASE_PREFIX . 'order_products' );
		define( 'IT_RST_REPORTING_PRODUCTS_DATABASE', 		IT_RST_PLUGIN_DATABASE_PREFIX . 'products' );
		define( 'IT_RST_REPORTING_EVENTS_DATABASE', 		IT_RST_PLUGIN_DATABASE_PREFIX . 'events' );
		define( 'IT_RST_REPORTING_MODULE_VERSION', 1.3 );
		define( 'IT_RST_REPORTING_MODULE_DB_VERSION', 1.2 );
		
		// Add global module settings variable
		global $it_rst_reporting_settings, $wpdb, $it_rst_dashboard_settings;
		$it_rst_reporting_settings = get_option( 'it_rooster_reporting_settings' );
		
		// If reporting settings do not exist yet create default options
		if ( !$it_rst_reporting_settings ) {
			$it_rst_reporting_settings = array( 'access_roles' => '', 'order_status' => 'processing,on-hold,completed', 'total_net' => 'total_shipping,total_taxes,total_fees', 'recent_orders_creation' => '1', 'recent_orders_updated' => '0', 'total_sales_gross' => '0', 'total_sales_net' => '1', 'product_cost' => '0', 'live_update' => '0' );
			
			update_option( 'it_rooster_reporting_settings', $it_rst_reporting_settings );
		}
		
		$it_rst_dashboard_settings = get_option( 'it_rooster_dashboard_settings' );
		
		// If dashboard settings do not exist yet create default options
		if ( !$it_rst_dashboard_settings ) {
			$it_rst_dashboard_settings = array( 'fast_facts' => 'products_available,total_customers,out_of_stock,total_sales_ff', 'overview' => 'best_country,total_coupons,total_refunds,shipping_method,total_sales,best_category', 'table' => 'best_customers', 'charts' => 'customers_guests,shipping_method' );
							
			update_option( 'it_rooster_dashboard_settings', $it_rst_dashboard_settings );
		}		
		
		$overview_array = ( isset( $it_rst_dashboard_settings[ 'overview' ] ) && $it_rst_dashboard_settings[ 'overview' ] ) ? explode( ',', $it_rst_dashboard_settings[ 'overview' ] ) : array(); 
		
		if ( defined( 'IT_RST_REPORTING_PREMIUM_ACTIVE' ) && count( $overview_array ) !== 9 ) {
			$it_rst_dashboard_settings[ 'overview' ] = 'total_sales,best_category,average_price_product,new_clients,total_refunds,best_country,average_price_order,best_product,total_coupons';
			update_option( 'it_rooster_dashboard_settings', $it_rst_dashboard_settings );
			
			$wpdb->delete( $wpdb->usermeta, array( 'meta_key' => 'it_rooster_box_coord' ) );
		}
		else if ( !defined( 'IT_RST_REPORTING_PREMIUM_ACTIVE' ) && isset( $it_rst_dashboard_settings[ 'overview' ] ) && count( $overview_array ) !== 6 ) {
			$it_rst_dashboard_settings[ 'overview' ] = 'best_country,total_coupons,total_refunds,shipping_method,total_sales,best_category';
			update_option( 'it_rooster_dashboard_settings', $it_rst_dashboard_settings );
			
			$wpdb->delete( $wpdb->usermeta, array( 'meta_key' => 'it_rooster_box_coord' ) );
		}
		
		$pro_active 	 = get_option( 'it_rooster_reporting_pro_active' );
		$just_downgraded = false;
		if ( !defined( 'IT_RST_REPORTING_PREMIUM_ACTIVE' ) ) {
			update_option( 'it_rooster_reporting_pro_active', false );
			if ( $pro_active ) {
				$just_downgraded = true;
			}
		}
		else {
			update_option( 'it_rooster_reporting_pro_active', true );
		}
		
		if ( $just_downgraded ) {
	    
			// Reset dashboard Settings
			$it_rst_dashboard_settings = array( 'fast_facts' => 'products_available,total_customers,out_of_stock,total_sales_ff', 'overview' => 'best_country,total_coupons,total_refunds,shipping_method,total_sales,best_category', 'table' => 'best_customers', 'charts' => 'customers_guests,shipping_method' );
							
			update_option( 'it_rooster_dashboard_settings', $it_rst_dashboard_settings );
		}
		
		// If reporting feature settings do not exist yet create default options
		global $it_rst_reporting_feature_settings;
		$it_rst_reporting_feature_settings = get_option( 'it_rooster_reporting_features' );
		
		if ( !$it_rst_reporting_feature_settings ) {
			
			$features 										= array();
			
			// Setup default refresh timers
			$features['products_available']['refresh'] 	  	= 100000;
			$features['waiting_payment']['refresh']	   	  	= 100000;
			$features['average_markup']['refresh']			= 100000;
			$features['total_customers']['refresh']    	  	= 100000;
			$features['available_quantities_ff']['refresh'] = 100000;
			$features['low_stock']['refresh'] 			  	= 100000;
			$features['waiting_shipped']['refresh']       	= 100000;
			$features['out_of_stock']['refresh'] 			= 100000;
			$features['in_stock']['refresh'] 				= 100000;
			$features['best_category']['refresh'] 	  		= 100000;
			$features['best_product']['refresh'] 		  	= 100000;
			$features['average_price_order']['refresh'] 	= 100000;
			$features['average_price_product']['refresh'] 	= 100000;
			$features['products_bought']['refresh'] 	  	= 100000;	
			$features['total_sales']['refresh'] 		  	= 100000;	
			$features['total_sales_ff']['refresh'] 		  	= 100000;	
			$features['new_clients']['refresh']			  	= 100000;
			$features['shipping_method']['refresh'] 	  	= 100000;
			$features['total_refunds']['refresh'] 		  	= 100000;
			$features['total_coupons']['refresh'] 		  	= 100000;
			$features['best_country']['refresh'] 	      	= 100000;
			$features['best_city']['refresh'] 	        	= 100000;
			$features['payment_methods']['refresh']  	  	= 100000;
			$features['category_by_country']['refresh']   	= 100000;
			$features['zone_distribution']['refresh'] 	  	= 100000;
			$features['orders_status']['refresh'] 		  	= 100000;
			$features['orders_operating_system']['refresh'] = 100000;
			$features['orders_browsers']['refresh']         = 100000;
			$features['type_products']['refresh'] 			= 100000;
			$features['customers_guests']['refresh'] 		= 100000;
			$features['top_countries']['refresh'] 			= 100000;
			$features['orders_placed_payed']['refresh'] 	= 100000;
			$features['returning_new']['refresh']			= 100000;
			$features['compare_products']['refresh'] 		= 100000;
			$features['spend_day']['refresh'] 				= 100000;
			$features['spend_week']['refresh'] 				= 100000;
			$features['spend_hour']['refresh'] 				= 100000;
			$features['best_customers']['refresh'] 			= 100000;
			
			// Setup default refresh live
			$features['products_available']['live'] 	  	= true;
			$features['waiting_payment']['live']	   	  	= true;
			$features['average_markup']['live']				= true;
			$features['total_customers']['live']    	  	= true;
			$features['available_quantities_ff']['live']  	= true;
			$features['low_stock']['live'] 			  		= true;
			$features['waiting_shipped']['live']       		= true;
			$features['out_of_stock']['live'] 			    = true;
			$features['in_stock']['live'] 			    	= true;
			$features['best_category']['live'] 	  			= true;
			$features['best_product']['live'] 		  		= true;
			$features['average_price_order']['live'] 		= true;
			$features['average_price_product']['live'] 		= true;
			$features['products_bought']['live'] 	  		= true;	
			$features['total_sales']['live'] 		  		= true;	
			$features['total_sales_ff']['live'] 		  	= true;	
			$features['new_clients']['live']			  	= true;
			$features['shipping_method']['live'] 	  		= true;
			$features['total_refunds']['live'] 		  		= true;
			$features['total_coupons']['live'] 		  		= true;
			$features['best_country']['live'] 	      		= true;
			$features['best_city']['live'] 	      	    	= true;
			$features['payment_methods']['live']  	  		= true;
			$features['category_by_country']['live']   		= true;
			$features['zone_distribution']['live'] 	  		= true;
			$features['orders_status']['live'] 		  		= true;
			$features['orders_operating_system']['live'] 	= true;
			$features['orders_browsers']['live']         	= true;
			$features['type_products']['live'] 				= true;
			$features['customers_guests']['live'] 			= true;
			$features['top_countries']['live'] 				= true;
			$features['orders_placed_payed']['live'] 		= true;
			$features['returning_new']['live']				= true;
			$features['compare_products']['live'] 			= true;
			$features['spend_day']['live'] 					= true;
			$features['spend_week']['live'] 				= true;
			$features['spend_hour']['live'] 				= true;
			$features['best_customers']['live'] 			= true;
							
			$it_rst_reporting_feature_settings = $features;
			update_option( 'it_rooster_reporting_features', $it_rst_reporting_feature_settings );
		}
	}
	
	// Includes module files and assets
	private function includes() {
				
		include_once 'class-dashboard.php';
		include_once 'class-install.php';
		include_once 'endpoints/class-reporting-process-import.php';
		include_once 'includes/database/class-orders-database.php';
		include_once 'includes/database/class-products-database.php';
		include_once 'includes/database/class-events-database.php';
		include_once 'includes/class-orders-manager.php';
		include_once 'includes/class-products-manager.php';
		include_once 'includes/database/class-order-product-database.php';
		include_once 'includes/database/class-upgrade.php';
		include_once 'endpoints/class-reporting-grid.php';
		include_once 'endpoints/class-reporting-box.php';
		include_once 'endpoints/class-reporting-table.php';
		include_once 'endpoints/class-reporting-chart.php';
		include_once 'endpoints/class-reporting-import.php';
		include_once 'endpoints/class-reporting-menu.php';
		include_once 'endpoints/class-reporting-features.php';
		include_once 'includes/class-queries.php';
		include_once 'includes/class-queries-cache.php';
		include_once 'includes/class-settings.php';
		include_once 'includes/class-features.php';
		include_once 'includes/classes/class-it-sa-order.php';
		include_once 'includes/classes/class-it-sa-orderproduct.php';
		include_once 'includes/classes/class-it-sa-product.php';
		include_once 'includes/classes/class-it-sa-event.php';
		
		// Database update class
		new RST_Reporting_Install();
		
		// Dashboard Control class
		new RST_Reporting_Dashboard();
		
		// Reporting Settings class
		new RST_Reporting_Settings();
		
		// Order Sync Class
		new RST_Reporting_Orders();
		
		// Product Sync Class
		new RST_Reporting_Products();
		
		// Defines Reporting Features
		new RST_Reporting_Features();
		
		// Adds modules assets
		add_filter( 'it_rst_frontend_assets', array( $this, 'add_reporting_rooster_frontend_assets' ), 1, 1 );
		add_filter( 'it_rst_backend_assets',  array( $this, 'add_reporting_rooster_backend_assets' ), 1, 1 );
		
		// Adds module endpoints
		add_filter( 'it_rst_available_endpoints', 		   array( $this, 'add_reporting_rooster_endpoints' ), 1, 1 );
		add_filter( 'it_rst_available_executor_endpoints', array( $this, 'add_reporting_rooster_executor_endpoints' ), 1, 1 );
		
		// Adds database names for information on the core
		add_filter( 'it_rst_available_modules_db', array( $this, 'add_reporting_rooster_databases' ), 1, 1 );
		
		// Add module permission settings
		add_filter( 'it_rst_available_permissions', array( $this, 'add_reporting_permission_settings' ), 1, 1 );
		
		// Import via cronjob
		add_action( 'it_rooster_import_cron', array( $this, 'it_rooster_import_cron_run' ) );
					
		// Add dashboard submenu
		add_action( 'it_rst_menu_pages', array( $this,  'add_dashboard_submenu' ) );
		
		// Changed from paid version to free version
		add_action( 'it_rst_just_downgraded', array( $this,  'just_downgraded' ) );
		
		// Add reporting notifications
		add_filter( 'it_rst_available_notifications', array( $this, 'add_notifications' ), 1, 1 );
		
		// Check if import cronjob is needed
		$this->check_import_cronjob();
		
	}
		
	public function add_reporting_permission_settings( $permissions ) {
		
		global $it_rst_reporting_settings;
        
	    $access_roles  	 = isset( $it_rst_reporting_settings['access_roles']) 			 ? explode( ',', $it_rst_reporting_settings['access_roles'] ) 			 : array();
	    $access_users  	 = isset( $it_rst_reporting_settings['module_roles_exceptions']) ? explode( ',', $it_rst_reporting_settings['module_roles_exceptions'] ) : array();
	    $access_roles[]  = 'administrator';
    
		$permissions['reporting'] = array( 'roles' => $access_roles, 'users' => $access_users );
								
		return $permissions;
	}
	
	public function add_reporting_rooster_databases( $databases ) {
		
		$databases[] = IT_RST_REPORTING_ORDERS_DATABASE;
		$databases[] = IT_RST_REPORTING_ORDER_PRODUCTS_DATABASE;
		$databases[] = IT_RST_REPORTING_PRODUCTS_DATABASE;
		
		return $databases;
	}
	
	public function add_reporting_rooster_executor_endpoints( $endpoints ) {
		
		return $endpoints;
	}
					
	public function add_reporting_rooster_endpoints ( $endpoints ) {
		
		$base = 'reporting';
		
		// Grid rest endpoint
		$endpoints['gridlayout'] = array( 'class' => 'IT_RST_RP_Grid', 'http_endpoint' => array( array( 'method' => WP_REST_Server::READABLE, 'endpoint' => "$base/loadgrid", 'callback' => 'loadgrid_json_endpoint', 'module' => $base ) ) );
		
		// Reset grid position
		$endpoints['reporting_grid_reset'] = array( 'class' => 'IT_RST_RP_Grid', 'http_endpoint' => array( array( 'method' => WP_REST_Server::READABLE, 'endpoint' => "$base/grid/reset", 'callback' => 'resetgrid_json_endpoint', 'module' => $base ) ) );
		
		// Box rest endpoint
		$endpoints['box'] 		 = array( 'class' => 'IT_RST_RP_Box', 'http_endpoint' => array( array( 'method' => WP_REST_Server::READABLE, 'endpoint' => "$base/boxcontent", 'callback' => 'contentBox_json_endpoint', 'module' => $base ) ) );
		
		// Table rest endpoint
		$endpoints['table'] 	 = array( 'class' => 'IT_RST_RP_Table', 'http_endpoint' => array( array( 'method' => WP_REST_Server::READABLE, 'endpoint' => "$base/tablecontent", 'callback' => 'contentTable_json_endpoint', 'module' => $base ) ) );
		
		// Chart rest endpoint
		$endpoints['chart'] 	 = array( 'class' => 'IT_RST_RP_Chart', 'http_endpoint' => array( array( 'method' => WP_REST_Server::READABLE, 'endpoint' => "$base/chartcontent", 'callback' => 'contentChart_json_endpoint', 'module' => $base ) ) );
		
		// Importing status rest endpoint
		$endpoints['reporting_is_importing'] = array( 'class' => 'IT_RST_RP_Import_Rest', 'http_endpoint' => array( array( 'method' => WP_REST_Server::READABLE, 'endpoint' => "$base/importing", 'callback' => 'importing_status_json', 'module' => $base ) ) );
		
		// Importing start rest endpoint
		$endpoints['reporting_start_importing'] = array( 'class' => 'IT_RST_RP_Import_Rest', 'http_endpoint' => array( array( 'method' => WP_REST_Server::READABLE, 'endpoint' => "$base/importing/start", 'callback' => 'importing_start', 'module' => $base ) ) );
		
		// Importing reset rest endpoint
		$endpoints['reporting_start_reset_importing'] = array( 'class' => 'IT_RST_RP_Import_Rest', 'http_endpoint' => array( array( 'method' => WP_REST_Server::READABLE, 'endpoint' => "$base/importing/reset", 'callback' => 'importing_reset', 'module' => $base ) ) );
		
		// Remove imported data endpoint
		$endpoints['reporting_delete_importing'] = array( 'class' => 'IT_RST_RP_Import_Rest', 'http_endpoint' => array( array( 'method' => WP_REST_Server::READABLE, 'endpoint' => "$base/importing/delete", 'callback' => 'importing_delete', 'module' => $base ) ) );
		
		// Get initial menu
		$endpoints['reporting_initial_menu'] = array( 'class' => 'IT_RST_RP_Menu', 'http_endpoint' => array( array( 'method' => WP_REST_Server::READABLE, 'endpoint' => "$base/menu", 'callback' => 'get_menu_data', 'module' => $base ) ) );
		
		// Set feature timers
		$endpoints['set_reporting_timers'] = array( 'class' => 'IT_RST_RP_Features', 'http_endpoint' => array( array( 'method' => WP_REST_Server::EDITABLE, 'endpoint' => "$base/timers", 'callback' => 'set_reporting_timers_json', 'module' => $base ) ) );
				
		return $endpoints;
	}
	
	public function add_reporting_rooster_frontend_assets( $assets ) {
		
		return $assets;
	}
	
	public function add_reporting_rooster_backend_assets( $assets ) {
		
			global $it_rst_reporting_settings;
			
			$permissions = IT_RST_Permissions::get_instance();
	    		    		    		    		    
		    // Show menu and submenus only to users with selected roles
		    if ( $permissions->check_permission( 'reporting' ) ) {
			    
			    $dashboard_url = get_dashboard_url();
			    $about_url	   = menu_page_url( 'ithemes-sales-acc-plugin-about', false );

				global $wpdb;
				$startD_shop = $wpdb->get_var("select DATE_FORMAT(date,'%Y-%m-%d') from " . IT_RST_REPORTING_ORDERS_DATABASE . " order by date asc limit 1");
				$assets[] = array( 'slug' => 'it_rst_utils_js', 'path' => 'modules/reporting/assets/js/rst_utils.js', 'type' => 'js', 'scope' => array( 'dashboard_on' ) );
				$assets[] = array( 'slug' => 'it_rst_resposnive_js', 'path' => 'modules/reporting/assets/js/rst_responsive.js', 'type' => 'js', 'scope' => array( 'dashboard_on' ) );
				$assets[] = array( 'slug' => 'it_rst_chart_js', 'path' => 'modules/reporting/assets/js/rst_chart.js', 'type' => 'js', 'scope' => array( 'dashboard_on' ), 'localize' => array( 'it_rst_js_data' => array( 'currency_symbol' => get_woocommerce_currency_symbol(), 'currency_position' => get_option( 'woocommerce_currency_pos' ) ) ) );
				$assets[] = array( 'slug' => 'it_rst_grid_js', 'path' => 'modules/reporting/assets/js/rst_grid.js', 'type' => 'js', 'localize' => array( 'rst_ajax_settings' => array( 'root' => esc_url_raw( rest_url() ), 'nonce' => wp_create_nonce( 'wp_rest' ), 'start_date_shop' => $startD_shop ) ), 'scope' => array( 'dashboard_on' ) );
				$assets[] = array( 'slug' => 'it_rst_gridstack_css', 'path' => 'modules/reporting/assets/css/gridstack.min.css', 'type' => 'css', 'scope' => array( 'dashboard_on' ) );
				$assets[] = array( 'slug' => 'it_rst_jquery_ui_js', 'url' => '//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.0/jquery-ui.js', 'type' => 'js', 'scope' => array( 'dashboard_on' ) );
				$assets[] = array( 'slug' => 'it_rst_lodash_js', 'url' => '//cdnjs.cloudflare.com/ajax/libs/lodash.js/3.5.0/lodash.min.js', 'type' => 'js', 'scope' => array( 'dashboard_on' ) );
				$assets[] = array( 'slug' => 'it_rst_chartjs_js', 'url' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js', 'type' => 'js', 'scope' => array( 'dashboard_on' ) );
				$assets[] = array( 'slug' => 'it_rst_gridstack_js', 'path' => 'modules/reporting/assets/js/gridstack.min.js', 'type' => 'js', 'scope' => array( 'dashboard_on' ) );
				$assets[] = array( 'slug' => 'it_rst_screenfull_js', 'path' => 'modules/reporting/assets/js/screenfull.js', 'type' => 'js', 'scope' => array( 'dashboard_on' ) );
				$assets[] = array( 'slug' => 'it_rst_datatable_js', 'url' => '//cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js', 'type' => 'js', 'scope' => array( 'dashboard_on' ) );
				$assets[] = array( 'slug' => 'it_rst_materialize_css','url' => '//cdnjs.cloudflare.com/ajax/libs/materialize/0.97.5/css/materialize.min.css', 'type' => 'css', 'scope' => array( 'dashboard_on' ) );
				$assets[] = array( 'slug' => 'it_rst_rst_admin_css', 'path' => 'modules/reporting/assets/css/style.css', 'type' => 'css', 'scope' => array( 'dashboard_on' ) );	
				$assets[] = array( 'slug' => 'it_rst_rst_responsive_css', 'path' => 'modules/reporting/assets/css/responsive.css', 'type' => 'css', 'scope' => array( 'dashboard_on' ) );
				$assets[] = array( 'slug' => 'it_rst_admin_js', 'path' => 'modules/reporting/assets/js/rst_switch.js', 'type' => 'js', 'localize' => array( 'it_rst_dashboard_url' => $dashboard_url, 'it_rst_about_url' => $about_url ) );					
				$assets[] = array( 'slug' => 'it_rst_switch_css', 'path' => 'modules/reporting/assets/css/switch.css', 'type' => 'css' );
				$assets[] = array( 'slug' => 'it_rst_import_progressbar_js', 'path' => 'modules/reporting/assets/js/rst_import_progressbar.js', 'type' => 'js', 'scope' => array( 'plugin_pages', 'dashboard_on' ) );
				$assets[] = array( 'slug' => 'it_rst_reporting_settings_js', 'path' => 'modules/reporting/assets/js/rst_reporting_settings.js', 'type' => 'js', 'scope' => array( 'plugin_pages', 'dashboard_on' ), 'localize' => array( 'it_rst_dashboard_url' => $dashboard_url ) );
				
				$premium = ( defined( 'IT_RST_REPORTING_PREMIUM_ACTIVE' ) ) ? true : false;
				
				// Adds to admin-js features through it_admin_settings variable for fastfacts, overview, table and charts
				foreach ( $assets as $k => $asset ) {
					if ( $asset['slug'] == 'admin-js' ) {
						if ( isset( $asset['localize']['it_admin_settings'] ) ) {
														
							$features = RST_Reporting_Features::get_instance();
							$fastfacts = $features->get_features( 'fastfacts', $premium, true );
							$overview  = $features->get_features( 'overview',  $premium, true );
							$table     = $features->get_features( 'table', 	   $premium, true );
							$charts    = $features->get_features( 'charts',    $premium, true );
																						
							// Adds user exceptions to reporting module
						    $users_exception 		= array();
						    $current_exceptons		= isset( $it_rst_reporting_settings['module_roles_exceptions'] ) ? explode( ',', $it_rst_reporting_settings['module_roles_exceptions'] ) : array();
						  
						    foreach ( $current_exceptons as $user ) {
						       $users_exception[] = current( new WP_User( $user ) );
						    }
						    
							$assets[ $k ]['localize']['it_admin_settings']['reporting_exceptions'] = $users_exception;
							$assets[ $k ]['localize']['it_admin_settings']['fastfacts'] 		   = $fastfacts;  
							$assets[ $k ]['localize']['it_admin_settings']['overview']  		   = $overview;
							$assets[ $k ]['localize']['it_admin_settings']['table'] 	  		   = $table;
							$assets[ $k ]['localize']['it_admin_settings']['charts']    		   = $charts;
						}
						break;
					}
				}				
			}
		return $assets;
	}
    
    public function check_import_cronjob() {
	    
	    if ( !wp_next_scheduled ( 'it_rooster_import_cron' ) ) {
		    $import_status = get_option( 'it_rooster_reporting_import_status' );
		    if ( !$import_status || !isset( $import_status['progress'] ) || $import_status['progress'] < 100 ) {
			    wp_schedule_event( time(), '1min', 'it_rooster_import_cron' );
			    
			    $instance = IT_RST_Endpoints::get_instance();
				$result_start = $instance->call_endpoint( 'reporting_start_importing' );
		    }
	    }
    }
    
    public function it_rooster_import_cron_run() {
	    
	    $rp_import = new IT_RST_RP_Import();
    }
    
    public function add_dashboard_submenu() {
	    
	     // Settings submenu
        add_submenu_page( 'ithemes-sales-acc', __( 'SalesAccelerator - Dashboard', 'ithemes-sales-accelerator' ), __( 'Dashboard', 'ithemes-sales-accelerator' ), 'read', 'ithemes-sales-acc-plugin-reporting-dashboard', array(
            $this,
            'dashboard_page',
        ) );
    }
    
    public function dashboard_page() {
	    
	    $dashboard = ( get_option('rst_dashboard') ) ? '?rst_dashboard=1' : '';
		$dashboard_url = get_dashboard_url() . $dashboard;
		wp_safe_redirect( $dashboard_url );
    }
    
    public function just_downgraded() {
	    
		// Reset dashboard Settings
		$it_rst_dashboard_settings = array( 'fast_facts' => 'products_available,total_customers,out_of_stock,total_sales_ff', 'overview' => 'best_country,total_coupons,total_refunds,shipping_method,total_sales,best_category', 'table' => 'best_customers', 'charts' => 'customers_guests,shipping_method' );
						
		update_option( 'it_rooster_dashboard_settings', $it_rst_dashboard_settings );
    }
    
    public function add_notifications( $notifications ) {
	    
	    $db_update    	 = ( get_option( 'it_rooster_reporting_db_upgrade_status' ) ) ? get_option( 'it_rooster_reporting_db_upgrade_status' ) : array();
	    $is_pending   	 = false;
	    $is_running   	 = false;
	    $is_failed    	 = false;
	    $is_completed 	 = false;
	    $progress     	 = 0;
	    $current_process = '';
	    $admin_url       = admin_url() . 'admin.php?page=ithemes-sales-acc-plugin-database-upgrade';
	    	    
	    foreach ( $db_update as $k => $v ) {
		    if ( isset( $v['status'] ) ){
			    switch ( $v['status'] ){
				    case 'pending':
				    	$is_pending   = true;
				    	break;
				    case 'running':
				    	$is_running   	 = true;
				    	$progress        = ( isset( $v['progress'] ) ) ? $v['progress'] : 0;
				    	$current_process = str_replace( '_', '.', $k );
				    	break;
				     case 'failed':
				    	$is_failed    = true;
				    	break;
				    case 'completed':
				    	$is_completed = true;
				    	break;
			    }
		    }
	    }
	    
	    if ( $is_pending ) {
		    $upgrade_page = $admin_url . '&module=reporting';
		    $db_update_message = '<p><strong>' . __( 'iThemes Sales Accelerator data update', 'ithemes-sales-accelerator' ) . '</strong> &#8211; ' . __( 'We need to upgrade your plugin database to the latest version.', 'ithemes-sales-accelerator' ) . "</p>";
		    $db_update_button = "<p class='submit'><a class='it_rst_upgrade_db button-primary' href='$upgrade_page'>" . __( 'Start the updater', 'ithemes-sales-accelerator' ) . '</a></p>';
		    
		    $db_update_admin_notification = "<div class='updated it_rst_db_upgrade'>$db_update_message $db_update_button</div>";
		    $notifications['reporting_db_upgrade'] = array( 'admin_notification' => $db_update_admin_notification, 'about_notification' => $db_update_admin_notification, 'status' => 'pending', 'progress' => $progress );
	    }
	    else if ( $is_running ) {
		    $upgrade_page 	   = $admin_url . '&module=reporting&force=true';
		    $status_message    = ( $current_process ) ? __( "Reporting $current_process db upgrade is running", 'ithemes-sales-accelerator' ) . ' (' . __( "$progress% complete", 'ithemes-sales-accelerator' ) . ').' : '';
		    $db_update_message = '<p><strong>' . __( 'iThemes Sales Accelerator data update', 'ithemes-sales-accelerator' ) . '</strong> &#8211; ' . __( 'Your database is being upgraded in the background:', 'ithemes-sales-accelerator' ) . ' ' . $status_message . '</p>';
		    $db_update_button  = "<p class='submit'><a class='it_rst_upgrade_db_reset button-primary' href='$upgrade_page'>" . __( 'Restart updater', 'ithemes-sales-accelerator' ) . '</a></p>';
		    
		    $db_update_admin_notification = "<div class='updated it_rst_db_upgrade'>$db_update_message $db_update_button</div>";
		    $notifications['reporting_db_upgrade'] = array( 'admin_notification' => $db_update_admin_notification, 'about_notification' => $db_update_admin_notification, 'status' => 'running', 'progress' => $progress );
	    }
	    else if ( $is_failed ) {
		    $upgrade_page = $admin_url . '&module=reporting';
		    $db_update_message = '<p><strong>' . __( 'iThemes Sales Accelerator data update', 'ithemes-sales-accelerator' ) . '</strong> &#8211; ' . __( 'There was an error while upgrading your database. Try again and contact support if the problem persists', 'ithemes-sales-accelerator' ) . "</p>";
		    $db_update_button = "<p class='submit'><a class='button-primary' href='$upgrade_page'>" . __( 'Restart updater', 'ithemes-sales-accelerator' ) . '</a></p>';
		    
		    $db_update_admin_notification = "<div class='updated it_rst_db_upgrade'>$db_update_message $db_update_button</div>";
		    $notifications['reporting_db_upgrade'] = array( 'admin_notification' => $db_update_admin_notification, 'about_notification' => $db_update_admin_notification, 'failed' => 'pending', 'progress' => $progress );
	    }
	    else if ( $is_completed ) {
		    $upgrade_page = $admin_url . '&module=reporting&dismiss_notice=true';
		    $db_update_message = '<p><strong>' . __( 'iThemes Sales Accelerator data update', 'ithemes-sales-accelerator' ) . "</strong> &#8211; " . __( 'Database upgrade completed!', 'ithemes-sales-accelerator' ) . "</p>";
		    $db_update_button = "<p class='submit'><a class='button-primary' href='$upgrade_page'>" . __( 'Dismiss', 'ithemes-sales-accelerator' ) . '</a></p>';
		    
		    $db_update_admin_notification = "<div class='updated it_rst_db_upgrade'>$db_update_message $db_update_button</div>";
		    $notifications['reporting_db_upgrade'] = array( 'admin_notification' => $db_update_admin_notification, 'about_notification' => $db_update_admin_notification, 'status' => 'completed', 'progress' => $progress );
	    }
	    
	    return $notifications;
    }    
}