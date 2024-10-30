<?php
if ( !defined( 'ABSPATH' ) ) {
    exit(); // Exit if accessed directly
}

/**
*  This class encapsulates WC_Order
**/

class IT_WC_Order {

    private $order;
    
    // Singleton design pattern
    protected static $instance = NULL;
    
    // Method to return the singleton instance
    public static function get_instance() {
	    
        if ( null == self::$instance ) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    public function __construct( $order ) {
	    
	    $this->order = WC()->order_factory->get_order( $order );
    }
        
    public function get_order() {
	    
	    return $this->order;
    }
    
    public function update_status( $status ) {
	    $this->order->update_status( $status );
    }
    
    public function get_id() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_id();
	    }
	    else {
		    return $this->order->id;
	    }
    }
    
    public function get_payment_method() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_payment_method();
	    }
	    else {
		    return $this->order->payment_method;
	    }
    }
    
    public function get_billing_country() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_billing_country();
	    }
	    else {
		    return $this->order->billing_country;
	    }
    }
    
    public function get_shipping_country() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_shipping_country();
	    }
	    else {
		    return $this->order->shipping_country;
	    }
    }
    
    public function get_billing_state() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_billing_state();
	    }
	    else {
		    return $this->order->billing_state;
	    }
    }
    
    public function get_shipping_state() {
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_shipping_state();
	    }
	    else {
		    return $this->order->shipping_state;
	    }
    }
    
    public function get_billing_city() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_billing_city();
	    }
	    else {
		    return $this->order->billing_city;
	    }
    }
    
    public function get_shipping_city() {
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_shipping_city();
	    }
	    else {
		    return $this->order->shipping_city;
	    }
    }
    
    public function get_customer_id() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_customer_id();
	    }
	    else {
		    return $this->order->customer_user;
	    }
    }
    
    public function get_date_created() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_date_created();
	    }
	    else {
		    return $this->order->order_date;
	    }
    }
    
    public function get_shipping_total() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_shipping_total();
	    }
	    else {
		    return $this->order->order_shipping;
	    }
    }
    
    public function get_order_key() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_order_key();
	    }
	    else {
		    return $this->order->order_key;
	    }
    }
    
    public function get_version() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_version();
	    }
	    else {
		    return $this->order->order_version;
	    }
    }
    
    public function get_date_paid() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_date_paid();
	    }
	    else {
		    return $this->order->paid_date;
	    }
    }
    
    public function get_prices_include_tax() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_prices_include_tax();
	    }
	    else {
		    return $this->order->prices_include_tax;
	    }
    }
    
    public function get_payment_method_title() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_payment_method_title();
	    }
	    else {
		    return $this->order->payment_method_title;
	    }
    }
    
    public function get_customer_ip_address() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_customer_ip_address();
	    }
	    else {
		    return $this->order->customer_ip_address;
	    }
    }
    
    public function get_customer_note() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_customer_note();
	    }
	    else {
		    return $this->order->customer_note;
	    }
    }
    
    public function get_date_completed() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_date_completed();
	    }
	    else {
		    return $this->order->completed_date;
	    }
    }
    
    public function get_created_via() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_created_via();
	    }
	    else {
		    return $this->order->created_via;
	    }
    }
    
    public function get_customer_user_agent() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_customer_user_agent();
	    }
	    else {
		    return $this->order->customer_user_agent;
	    }
    }
    
    public function get_cart_hash() {

	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_cart_hash();
	    }
	    else {
		    return $this->order->cart_hash;
	    }
    }
    
    public function get_discount_tax() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_discount_tax();
	    }
	    else {
		    return $this->order->cart_discount_tax;
	    }
    }
    
    public function get_discount_total() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_discount_total();
	    }
	    else {
		    return $this->order->get_total_discount();
	    }
    }
    
    public function get_address( $type ) {
	    
	    return $this->order->get_address( $type );
    }
    
    public function get_line_subtotal( $item, $inc_tax = false, $round = true ) {
	    
	    return $this->order->get_line_subtotal( $item, $inc_tax = false, $round = true );
    }
    
    public function get_line_total( $item, $inc_tax = false, $round = true ) {
	    
	    return $this->order->get_line_total( $item, $inc_tax = false, $round = true );
    }
    
    public function get_product_from_item( $item ) {
	    
	    return $this->order->get_product_from_item( $item );
    }
    
    public function get_item_total( $item ) {
	    
	    return $this->order->get_item_total( $item );
    }
        
    public function get_amount(){
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->order->get_amount();
	    }
	    else {
		    if ( method_exists( $this->order, 'get_refund_amount' ) ) {
		    	return $this->order->get_refund_amount();
		    }
		    else {
			    return false;
		    }
	    }
    }
    
    public function get_qty_refunded_for_item( $key ) {
	    
	    return $this->order->get_qty_refunded_for_item( $key );
    }
    
    public function add_order_note( $note, $is_customer_note = 0, $added_by_user = false  ) {
	    
	    $this->order->add_order_note( $note, $is_customer_note = 0, $added_by_user = false );
    }
            
    public function get_items() {
	    
	    return $this->order->get_items();
    }
    
    public function add_product( $product, $quantity ) {
	    return $this->order->add_product( $product, $quantity );
    }
    
    public function it_get_total_refunded_for_item( $key, $type = 'line_item' ) {
	    
	    $total = 0;
		foreach ( $this->order->get_refunds() as $refund ) {
			foreach ( $refund->get_items( $type ) as $refunded_item ) {
				if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
					if ( absint( $refunded_item->get_meta( '_refunded_item_id' ) ) === $key ) {
						$total += $refunded_item->get_total();
					}
				}
				else {
					if ( isset( $refunded_item['item_meta']['_refunded_item_id'][0] ) && absint( $refunded_item['item_meta']['_refunded_item_id'][0] ) === $key ) {
						$total += $refunded_item['item_meta']['_line_total'][0];
					}
				}
			}
		}
		
		return $total;
    }
        
    // Acess to order fields via custom function 
    public function __call( $name, $arguments ) {
	    
	    if ( method_exists( $this->order, $name ) ) {
	    	return $this->order->$name( $arguments );
	    }
	    else {
		    return false;
	    }
    }
}