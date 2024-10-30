<?php
	
if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

/**
* Abstract OrderProduct Class 
**/
class IT_SA_OrderProduct {

  // Class fields
  private static $instance;
  private $database      	 = null;
  private $id	         	 = 0;
  private $order_id     	 = 0;
  private $product           = 0;
  private $category 		 = '';
  private $price 		 	 = 0.00;
  private $quantity    		 = 0;
  private $quantity_refunded = 0;
  private $total_refunded    = 0.00;
  private $product_type      = '';
  private $datetime 		 = '';
  private $status  			 = '';

 /**
 * Class Constructor
 **/
  public function __construct( $id ) {
	  
	  $this->database = new RST_Reporting_Order_Products_Database();
	  $result 		  = $this->database->get_by( array( 'id' => $id ), '=' );
	  
	  if ( isset( $result[0] ) ) {
		  foreach ( $result[0] as $k => $v ) {
			  $this->$k = $v;
		  } 
	  }	  
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
  
  public function get_product_id() {
	  return $this->product;
  }
    
  public function get_category() {
	  return $this->category;
  }
  
  public function get_price() {
	  return $this->price;
  }
  
  public function get_quantity() {
	  return $this->quantity;
  }
  
  public function get_quantity_refunded() {
	  return $this->quantity_refunded;
  }
  
  public function get_total_refunded() {
	  return $this->total_refunded;
  }
  
  public function get_product_type() {
	  return $this->product_type;
  }
  
  public function get_date() {
	  return $this->datetime;
  }
  
  public function get_order_status() {
	  return $this->status;
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
  
  public function set_product_id( $product ) {
	  $this->product = $product;
  }
    
  public function set_category( $category ) {
	  $this->category = $category;
  }
  
  public function set_price( $price ) {
	  $this->price = $price;
  }
  
  public function set_quantity( $quantity ) {
	  $this->quantity = $quantity;
  }
  
  public function set_quantity_refunded( $quantity_refunded ) {
	  $this->quantity_refunded = $quantity_refunded;
  }
  
  public function set_total_refunded( $total_refunded ) {
	  $this->total_refunded = $total_refunded;
  }
  
  public function set_product_type( $product_type ) {
	  $this->product_type = $product_type;
  }
  
  public function set_date( $datetime ) {
	  $this->datetime = $datetime;
  }
  
  public function set_order_status( $status ) {
	  $this->status = $status;
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