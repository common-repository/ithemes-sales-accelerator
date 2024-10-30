<?php
	
if (! defined('ABSPATH')) {
    exit();
}

/**
* This class handles database upgrade
**/

class RST_Reporting_Database_Upgrade {
	
	private $current_version  = 0;
	private $previous_version = 0;
	
    public function __construct( $new_version = 0, $old_version = 0 ) {
	    $this->current_version  = $new_version;
	    $this->previous_version = $old_version;
    }
    
    
    // Checks if update is needed, and add to option in that case
    public function check_if_upgrade_is_needed() {
	    
	    if ( $this->previous_version ) {
		    $updates = ( get_option( 'it_rooster_reporting_db_upgrade_status' ) ) ? get_option( 'it_rooster_reporting_db_upgrade_status' ) : array();
		    if ( $this->previous_version <= 1.1 ){			    
			    if ( !isset( $updates['1_1'] ) ) {
				    $updates['1_1'] = array( 'status' => 'pending', 'type' => 'multiple', 'progress' => 0, 'imported' => 0, 'total' => 0 );
				}
			}
			
			update_option( 'it_rooster_reporting_db_upgrade_status', $updates );
	    }
    }
    
    // Initializes upgrade process
    public function initialize_upgrade( $force_update = false, $dismiss = false ) {
	    
	    if ( $dismiss ) {
		    update_option( 'it_rooster_reporting_db_upgrade_status', array() );
	    }
	    else{
		    if ( !$force_update ) {
			    $updates = ( get_option( 'it_rooster_reporting_db_upgrade_status' ) ) ? get_option( 'it_rooster_reporting_db_upgrade_status' ) : array();
			    
			    // Sets pending and failed updates to running
			    foreach ( $updates as $k => $v ){
				    if ( $v['status'] == 'pending' || $v['status'] == 'failed' ) {
					    if ( isset( $updates[$k]['status'] ) ) {
					    	$updates[$k]['status'] = 'running';
					    }
				    }
			    }
			    
			    update_option( 'it_rooster_reporting_db_upgrade_status', $updates );
		    }
		    
		    wp_schedule_event( time(), '1min', 'it_rooster_import_cron' );
		    $instance 	  = IT_RST_Endpoints::get_instance();
			$result_start = $instance->call_endpoint( 'reporting_start_db_upgrade' );			
		}
    }
    
    // Check if upgrade is needed, run it if so
    public function run_upgrade() {
	    
	    $updates = ( get_option( 'it_rooster_reporting_db_upgrade_status' ) ) ? get_option( 'it_rooster_reporting_db_upgrade_status' ) : array();
	    
	    $result  = 'done';
	    foreach ( $updates as $k => $update ) {
		    $upgrade_function = "upgrade_$k";
		    if ( $update['status'] == 'running' ) {
		    	$this->$upgrade_function( $updates );
		    	$result = 'running';
		    }
	    }
	    
	    return $result;
    }
    
    // Run 1.1 db upgrade
    public function upgrade_1_1( $updates ) {
	    
	    try {
			global $wpdb;
			$product_db      = new RST_Reporting_Order_Products_Database();
		    $order_status 	 = array_keys( wc_get_order_statuses() );	    		    	    
		    $orders_total 	 = $wpdb->get_row( "SELECT COUNT(*) as count FROM $wpdb->posts WHERE post_type = 'shop_order' AND post_status IN ('" . implode( '\',\'', $order_status ) . "')" );
		    $total_orders 	 = ( $orders_total->count ) ? $orders_total->count : 0;
		    
		    $orders_database = new RST_Reporting_Orders_Database();
		    $count           = 0;
		    		    
		    // Get orders to import
		    $orders = get_posts( array(
			    'posts_per_page' => 400,
			    'meta_query' 	 => array(
				    'relation' => 'AND',
			        array(
			            'key' 		=> 'it_rooster_reporting_imported',
			            'compare' 	=> 'EXISTS',
			        ),
			        array(
			            'key' 		=> 'it_rooster_reporting_db_version',
			            'compare' 	=> '<',
			            'value' 	=> '1.11',
			        ),
				),
			    'post_type'   	 => 'shop_order',
			    'post_status' 	 => $order_status,
			    'fields' 		 => 'ids',
			) );
			
			if ( !empty( $orders ) ) {
				foreach ( $orders as $order_id ){
					
					// Add city to custom orders table
					$city  = ( get_post_meta( $order_id, '_billing_city', true ) ) ? get_post_meta( $order_id, '_billing_city', true ) : get_post_meta( $order_id, '_shipping_city', true );
					$orders_database->update( array( 'city' => $city ), array( 'order_id' => $order_id ) );
					
					// Add categories to variations
					$order_obj = new IT_WC_Order( $order_id );
					foreach ( $order_obj->get_items() as $item ) {
						if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {   
							$product 		= $item->get_product_id();
						    if( $item->get_variation_id() ){
						    	$product 	= $item->get_variation_id();
						    }
						}
						else {
							$product 		= $item['product_id'];
						    if( $item['variation_id'] ){
						    	$product 	= $item['variation_id'];
						    }
						}
						$product_obj	= new IT_WC_Product( $product );
						$type  			= $product_obj->get_type();
						
						if ( $type == 'variation' ) {
							$main_product = $product_obj->get_parent_id();
							$categories   = '';						
							$terms 			= get_the_terms( $main_product, 'product_cat' );
			    		    
						    if ( $terms ) {
								foreach ( $terms as $term ) {
									if ( $categories ) {
								    	$categories .= ',' . $term->name;
								    }
								    else {
									    $categories = $term->name;
								    }
								}
							}
							
							if ( $categories ) {
								$product_db->update( array( 'category' => $categories ), array( 'order_id' => $order_id, 'product' => $product )  );
							}
						}
			    	} 					
					
					update_post_meta( $order_id, 'it_rooster_reporting_db_version', '1.11' );
					$count++;
				}
				
				$imported 		 = $updates['1_1']['imported'] + $count;
				$progress        = round( ( $imported / $total_orders ) * 100 );
				if ( $progress > 100 ) {
					$progress = 100;
				}
				
				$updates['1_1'] = array( 'status' => 'running', 'type' => 'multiple', 'progress' => $progress, 'imported' => $imported, 'total' => $total_orders );
				update_option( 'it_rooster_reporting_db_upgrade_status', $updates );
			}
			else {
				$updates = ( get_option( 'it_rooster_reporting_db_upgrade_status' ) ) ? get_option( 'it_rooster_reporting_db_upgrade_status' ) : array();
				$updates['1_1'] = array( 'status' => 'completed', 'type' => 'multiple', 'progress' => 100, 'imported' => 0, 'total' => 0 );
				update_option( 'it_rooster_reporting_db_upgrade_status', $updates );
			}
			
			return true;
		} catch ( Exception $e ) {
			if ( isset( $updates['1_1'] ) ) {
				$updates['1_1'] = array( 'status' => 'failed', 'type' => 'multiple', 'progress' => 0, 'imported' => 0, 'total' => 0 );
				update_option( 'it_rooster_reporting_db_upgrade_status', $updates );
			}
			return false;
		}
    }
}