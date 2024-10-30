<?php
	
if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

/**
* Abstract Order Class 
**/
class IT_SA_Order {

  // Class fields
  private static $instance;
  private $database      	= null;
  private $id	         	= '';
  private $order_id     	= '';
  private $date 		 	= '';
  private $total_gross 		= '';
  private $total_net    	= '';
  private $total_shipping   = '';
  private $total_refunded   = '';
  private $total_fees       = '';
  private $total_taxes 		= '';
  private $num_products  	= '';
  private $num_items  		= '';
  private $avg_product  	= '';
  private $status  			= '';
  private $customer  		= '';
  private $shipping  		= '';
  private $country  		= '';
  private $state  			= '';
  private $city  			= '';
  private $payment  		= '';
  private $operating_system = '';
  private $platform  	    = '';
  private $browser  	    = '';
  private $coupon  			= '';
  private $value_discount  	= '';

 /**
 * Class Constructor
 **/
  public function __construct( $id ) {
	  
	  $this->database = new RST_Reporting_Orders_Database();
	  $result 		  = $this->database->get_by( array( 'order_id' => $id ), '=' );
	  
	  if ( isset( $result[0] ) ) {
		  foreach ( $result[0] as $k => $v ) {
			  $this->$k = $v;
		  } 
	  }	  
  }
  
  /**
  * Get Order Items
  **/
  public function get_order_items() {
	  
	  $order_product_db = new RST_Reporting_Order_Products_Database();
	  $result   	    = $order_product_db->get_by( array( 'order_id' => $this->order_id ), '=' );
	  $order_items      = array();
	  foreach ( $result as $k => $v ) {
		  $order_items[] = new IT_SA_OrderProduct( $v->id );
	  }
	  
	  return $order_items;
  }
  
  /**
  * Get Order Events
  **/
  public function get_order_events() {
	  
	  $events_db        = new RST_Reporting_Events_Database();
	  $result   	    = $events_db->get_by( array( 'order_id' => $this->order_id ), '=' );
	  $order_events     = array();
	  foreach ( $result as $k => $v ) {
		  $order_events[] = new IT_SA_Event( $v->id );
	  }
	  
	  return $order_events;
  }
  
  /**
  * Class Getters
  **/
  public function get_database() {
	  return $this->database;
  }
  
  public function get_id() {
	  return $this->id;
  }
  
  public function get_order_id() {
	  return $this->order_id;
  }
    
  public function get_date() {
	  return $this->date;
  }
  
  public function get_total_gross() {
	  return $this->total_gross;
  }
  
  public function get_total_net() {
	  return $this->total_net;
  }
  
  public function get_total_shipping() {
	  return $this->total_shipping;
  }
  
  public function get_total_refunded() {
	  return $this->total_refunded;
  }
  
  public function get_total_fees() {
	  return $this->total_fees;
  }
  
  public function get_total_taxes() {
	  return $this->total_taxes;
  }
  
  public function get_number_products() {
	  return $this->num_products;
  }
  
  public function get_number_items() {
	  return $this->num_items;
  }
  
  public function get_average_product_price() {
	  return $this->avg_product;
  }
  
  public function get_order_status() {
	  return $this->status;
  }
  
  public function get_customer_id() {
	  return $this->customer;
  }
  
  public function get_shipping_method() {
	  return $this->shipping;
  }
  
  public function get_country() {
	  return $this->country;
  }
  
  public function get_state() {
	  return $this->state;
  }
  
  public function get_city() {
	  return $this->city;
  }
  
  public function get_payment_method() {
	  return $this->payment;
  }
  
  public function get_customer_operating_system() {
	  return $this->operating_system;
  }
  
  public function get_customer_platform() {
	  return $this->platform;
  }
  
  public function get_customer_browser() {
	  return $this->browser;
  }
  
  public function get_coupons() {
	  return $this->coupon;
  }
  
  public function get_total_discount() {
	  return $this->value_discount;
  }
    
  /**
  * Class Setters
  **/
  public function set_database( $database ) {
	  $this->database = $database;
  }
  
  public function set_id( $id ) {
	  $this->id = $id;
  }
  
  public function set_order_id( $order_id ) {
	  $this->order_id = $order_id;
  }
    
  public function set_date( $date ) {
	  $this->date = $date;
  }
  
  public function set_total_gross( $total_gross ) {
	  $this->total_gross = $total_gross;
  }
  
  public function set_total_net( $total_net ) {
	  $this->total_net = $total_net;
  }
  
  public function set_total_shipping( $total_shipping ) {
	  $this->total_shipping = $total_shipping;
  }
  
  public function set_total_refunded( $total_refunded) {
	  $this->total_refunded = $total_refunded;
  }
  
  public function set_total_fees( $total_fees ) {
	  $this->total_fees = $total_fees;
  }
  
  public function set_total_taxes( $total_taxes ) {
	  $this->total_taxes = $total_taxes;
  }
  
  public function set_number_products( $num_products ) {
	  $this->num_products = $num_products;
  }
  
  public function set_number_items( $num_items ) {
	  $this->num_items = $num_items;
  }
  
  public function set_average_product_price( $avg_product ) {
	  $this->avg_product = $avg_product;
  }
  
  public function set_order_status( $status ) {
	  $this->status = $status;
  }
  
  public function set_customer_id( $customer ) {
	  $this->customer = $customer;
  }
  
  public function set_shipping_method( $shipping ) {
	  $this->shipping = $shipping;
  }
  
  public function set_country( $country ) {
	  $this->country = $country;
  }
  
  public function set_state( $state ) {
	  $this->state = $state; 
  }
  
  public function set_city( $city ) {
	  $this->city = $city;
  }
  
  public function set_payment_method( $payment ) {
	  $this->payment = $payment;
  }
  
  public function set_customer_operating_system( $operating_system ) {
	  $this->operating_system = $operating_system;
  }
  
  public function set_customer_platform( $platform ) {
	  $this->platform = $platform;
  }
  
  public function set_customer_browser( $browser ) {
	  $this->browser = $browser;
  }
  
  public function set_coupons( $coupon ) {
	  $this->coupon = $coupon;
  }
  
  public function set_total_discount( $value_discount ) {
	  $this->value_discount = $value_discount;
  }
  
  // Transform object into array
  public function to_array() {
    $to_array = array();
    
    foreach ( $this as $k => $v ) {
	    $to_array[ $k ] = $v;
    }
	    
    return $to_array;
  }
}