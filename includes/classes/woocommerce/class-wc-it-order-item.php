<?php
if ( !defined( 'ABSPATH' ) ) {
    exit(); // Exit if accessed directly
}

/**
*  This class encapsulates WC_Order_Item
**/

class IT_WC_Order_Item {

    private $order_item_id    = 0;
    private $order_id 		  = 0;
    private $order_item_type  = '';
    
    // Singleton design pattern
    protected static $instance = NULL;
    
    // Method to return the singleton instance
    public static function get_instance() {
	    
        if ( null == self::$instance ) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    public function __construct( $order_item ) {
	    
	    global $wpdb;
	    $result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}woocommerce_order_items WHERE order_item_id = {$order_item}" );
	    
	    if ( isset( $result->order_id ) && $result->order_id ) {
		    $this->order_id = $result->order_id;
	    }
	    
	    if ( isset( $result->order_item_type ) && $result->order_item_type ) {
		    $this->order_item_type = $result->order_item_type;
	    }
	    	    
	    $this->order_item_id = $order_item;
    }    
    
    public function get_order() {
	    
	    $order = new IT_WC_Order( $this->order_id );
	    return $order;
    }
    
    public function get_order_id() {
	    
		return $this->order_id;
    }
    
    public function get_order_item_type() {
	    
		return $this->order_item_type;
    }
    
    public function get_product_id() {
	    
	    global $wpdb;
	    $result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id = {$this->order_item_id} AND meta_key = '_product_id'" );
	    
	    if( isset( $result->meta_value ) ){
		    return $result->meta_value;
	    }
	    	    
	    return 0;
    }
    
    
    public function get_variation_id() {
	    
	    global $wpdb;
	    $result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id = {$this->order_item_id} AND meta_key = '_variation_id'" );
	    
	    if ( isset( $result->meta_value ) ) {
		    return $result->meta_value;
	    }
	    	    
	    return 0;
    }
}