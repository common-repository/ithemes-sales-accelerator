<?php
	
if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

/**
* This class handles free module queries cache
**/

class RST_Reporting_Queries_Cache {
	
	// Singleton design pattern
    protected static $instance = NULL;
    
    // Method to return the singleton instance
    public static function get_instance() {
	    
        if ( null == self::$instance ) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
	
	public function __construct() {
		
		// Delete customers transient
		add_action( 'delete_user', array( $this, 'delete_customers_transient' ) );
		add_action( 'user_register', array( $this, 'delete_customers_transient' ) );
		
		// Delete products reansient
		add_action( 'woocommerce_variation_set_stock', array( $this, 'delete_products_transient' ) );
		add_action( 'woocommerce_product_set_stock', array( $this, 'delete_products_transient' ) );
		
		add_action( 'before_delete_post', array( $this, 'delete_product_from_db' ), 10, 1 );	
	}
	
	public function delete_customers_transient( $user_id ) {
		
		delete_transient( 'it_rst_reporting_totalCustomers' );
	}
	
	public function delete_products_transient( $product ) {
		
		delete_transient( 'it_rst_reporting_outStock' );
		delete_transient( 'it_rst_reporting_productsAvailable' );
	}
	
	public function delete_product_from_db( $post_id ) {
		
		$post = get_post( $post_id );
		if ( $post->post_type != 'product' && $post->post_type != 'product_variation' ) {
	        return;
	    }
	    
	    $this->delete_products_transient( $post );
	}
	
	public function delete_orders_cache() {
		delete_transient( 'it_rst_reporting_totalSalesFF' );
	}
}