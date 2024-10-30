<?php
	
if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

/**
* Abstract Product Class 
**/
class IT_SA_Product {

  // Class fields
  private static $instance;
  private $database      = null;
  private $id	         = '';
  private $name     	 = '';
  private $image 		 = '';
  private $sku 		 	 = '';
  private $product_id    = '';

 /**
 * Class Constructor
 **/
  public function __construct( $id ) {
	  
	  $this->database = new RST_Reporting_Products_Database();
	  $result 		  = $this->database->get_by( array( 'product_id' => $id ), '=' );
	  
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
  
  public function get_name() {
	  return $this->name;
  }
    
  public function get_image() {
	  return $this->image;
  }
  
  public function get_sku() {
	  return $this->sku;
  }
  
  public function get_product_id() {
	  return $this->product_id;
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
    
  public function set_name( $name ) {
	  $this->name = $name;
  }
  
  public function set_image( $image ) {
	  $this->image = $image;
  }
  
  public function set_sku( $sku ) {
	  $this->sku = $sku;
  }
  
  public function set_product_id( $product_id ) {
	  $this->product_id = $product_id;
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