<?php
	
if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

/**
* This class handles module database instalation 
**/

class RST_Reporting_Install {
	
	public $db_version = IT_RST_REPORTING_MODULE_DB_VERSION;
	
	public function __construct() {
		
    	$this->update_db_check();
	}

	public function install() {
		
		try {
			global $wpdb;
			
			// Database names
			$table_name_orders 			= IT_RST_REPORTING_ORDERS_DATABASE;
			$table_name_order_products 	= IT_RST_REPORTING_ORDER_PRODUCTS_DATABASE;
			$table_name_products	 	= IT_RST_REPORTING_PRODUCTS_DATABASE;
			$table_name_events		 	= IT_RST_REPORTING_EVENTS_DATABASE;
			
			// Use wpdb Charset collation
			$charset_collate 			= $wpdb->get_charset_collate();
						
			// Create orders table
			$sql_orders = "CREATE TABLE $table_name_orders (
	    	    id INT NOT NULL AUTO_INCREMENT,
	    	    order_id INT NOT NULL,
	    	    date DATETIME NOT NULL,
		        total_gross DECIMAL(12,4) NOT NULL,
				total_net DECIMAL(12,4) NOT NULL,
				total_shipping DECIMAL(12,4) NOT NULL,
				total_refunded DECIMAL(12,4) NOT NULL,
				total_fees DECIMAL(12,4) NOT NULL,
				total_taxes DECIMAL(12,4) NOT NULL,
				num_products INT NOT NULL,
				num_items INT NOT NULL,
				avg_product DECIMAL(12,4) NOT NULL,
				status VARCHAR(50) NOT NULL,
				customer INT NOT NULL,
				shipping VARCHAR(100) NOT NULL,
				country VARCHAR(100) NOT NULL,
				state VARCHAR(100) NULL,
				city VARCHAR(100) NULL,
				payment VARCHAR(100) NOT NULL,
				operating_system VARCHAR(50) NOT NULL,
				platform VARCHAR(30) NOT NULL,
				browser VARCHAR(50) NOT NULL,
				coupon VARCHAR(50),
				value_discount DECIMAL(12,4),
				source VARCHAR(30) DEFAULT 'web',
				PRIMARY KEY id (id)
			) $charset_collate;";
	
	        require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
	        dbDelta( $sql_orders );
	        
	        // Create orders products table
	        $sql_order_products = "CREATE TABLE $table_name_order_products (
	    	    id INT NOT NULL AUTO_INCREMENT,
	    	    order_id INT NOT NULL,
	    	    product INT NOT NULL,
		        category TEXT,
				price DECIMAL(12,4) NOT NULL,
				quantity INT NOT NULL,
				quantity_refunded INT NOT NULL,
				total_refunded DECIMAL(12,4) NOT NULL,
				product_type VARCHAR(100) NOT NULL,
				datetime DATETIME NOT NULL,
				status VARCHAR(50) NOT NULL,
				PRIMARY KEY id (id)
			) $charset_collate;";
	
	        dbDelta( $sql_order_products );
	        
	        // Create products table
	        $sql_products = "CREATE TABLE $table_name_products (
	    	    id INT NOT NULL AUTO_INCREMENT,
	    	    name VARCHAR(100) NOT NULL,
	    	    image VARCHAR(200),
		        sku VARCHAR(50),
				product_id INT NOT NULL,
				PRIMARY KEY id (id)
			) $charset_collate;";
	
	        dbDelta( $sql_products );
	        
	        // Create events table
	        $sql_events = "CREATE TABLE $table_name_events (
	    	    id INT NOT NULL AUTO_INCREMENT,
	    	    order_id INT NOT NULL,
	    	    meta1 VARCHAR(80) NOT NULL,
	    	    meta2 VARCHAR(80) NOT NULL,
	    	    meta3 VARCHAR(255) NULL,
		        type INT NOT NULL,
		        notes VARCHAR(80) NOT NULL,
				datetime DATETIME NOT NULL,
				order_status VARCHAR(50) NULL,
				PRIMARY KEY id (id)
			) $charset_collate;";
	
	        dbDelta( $sql_events );
	        	        
	        // Check if database update script is needed
	        $db_upgrade = new RST_Reporting_Database_Upgrade( $this->db_version, get_option( 'it_rooster_reporting_db_version' ) );
	        $db_upgrade->check_if_upgrade_is_needed();
	        
	        update_option( 'it_rooster_reporting_db_version', $this->db_version );
	        
        } catch ( Exception $e ) {}
	}

	// Check if database needs update
	public function update_db_check() {
		
		// If database version is different from the current one
	    if ( get_option( 'it_rooster_reporting_db_version' ) != $this->db_version ) {
	        $this->install();
	    }
		
		// If plugin has been recently reactivated run database update once if needed
	    else if ( get_option( 'it_rooster_deactivated' ) ) {
		    $this->install();
		    delete_option( 'it_rooster_deactivated' );
	    }
	}
}