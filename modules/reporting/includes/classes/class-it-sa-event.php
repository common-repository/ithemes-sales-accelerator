<?php
	
if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

/**
* Abstract Event Class 
**/
class IT_SA_Event {

  // Class fields
  private static $instance;
  private $database      = null;
  private $id	         = '';
  private $order_id      = '';
  private $meta1 		 = '';
  private $meta2 		 = '';
  private $meta3 		 = '';
  private $type          = '';
  private $notes         = '';
  private $order_status  = '';
  private $datetime      = '';

 /**
 * Class Constructor
 *
 * @param parameters array
 **/
  public function __construct( $id ) {
	  
	  $this->database = new RST_Reporting_Events_Database();
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
    
  public function get_meta1() {
	  return $this->meta1;
  }
  
  public function get_meta2() {
	  return $this->meta2;
  }
  
  public function get_meta3() {
	  return $this->meta3;
  }
  
  public function get_type() {
	  return $this->type;
  }
  
  public function get_notes() {
	  return $this->notes;
  }
  
  public function get_order_status() {
	  return $this->order_status;
  }
  
  public function get_date() {
	  return $this->datetime;
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
  
  public function set_meta1( $meta1 ) {
	  $this->meta1 = $meta1;
  }
  
  public function set_meta2( $meta2 ) {
	  $this->meta2 = $meta2;
  }
  
  public function set_meta3( $meta3 ) {
	  $this->meta3 = $meta3;
  }
  
  public function set_type( $type ) {
	  $this->type = $type;
  }
  
  public function set_notes( $notes ) {
	  $this->notes = $notes;
  }
  
  public function set_order_status( $status ) {
	  $this->order_status = $status;
  }
  
  public function set_date( $date ) {
	  $this->datetime = $date;
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