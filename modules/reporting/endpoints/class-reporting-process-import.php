<?php
	
if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

/**
* This class handles initial import management endpoints
**/

class IT_RST_RP_Import {
    
    // Singleton design pattern
    protected static $instance 	 = NULL;
    public static $new_orders 	 = 0;
    public static $new_products  = 0;
    
    // Method to return the singleton instance
    public static function get_instance(){
        if ( null == self::$instance ) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    public function __construct(){
		
	    // Start importing data
	    $this->startImport();
    }        
    
    /* 
	    Saves order data into custom database
    */
    public function syncOrder( $order_id ) {
	    
	    try {
		    $order 	   = new IT_WC_Order( $order_id );
		    $order_obj = $order->get_order();
		    
		    if ( is_object( $order_obj ) && $order_obj instanceof WC_Order ) {
			    			    
		    	// Gets needed data
			    $status 		= $order->get_status();
			    $date 		 	= ( $order->get_date_created() ) ? $order->get_date_created() : current_time( 'mysql' );
			    
			    if ( $date instanceof WC_DateTime ) {
				    $date = $date->date_i18n('Y-m-d H:i:s');
			    }
			    
			    $gross 		 	= ( $order->get_total() ) ? $order->get_total() : 0.0000;
			    			    			    
			    // Calculate totals on orders that lack a total
			    if ( !$gross ) {
				     $order->calculate_totals();
				     if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
					 	$order->save();
				    }
				    $gross 		= ( $order->get_total() ) ? $order->get_total() : 0.0000;
			    }
			    
			    $net    	 	= ( $order->get_subtotal() ) ? $order->get_subtotal() : 0.0000; 
			    $taxes 			= ( $order->get_total_tax() ) ? $order->get_total_tax() : 0.0000;
			    $shipping    	= ( $order->get_shipping_total() ) ? $order->get_shipping_total() : 0.0000; 
			    $fees           = ( $order->get_fees() ) ? $order->get_fees() : array();
			    $refunded 		= ( $order->get_total_refunded() ) ? $order->get_total_refunded() : 0.0000;
			    $customer    	= ( $order->get_customer_id() ) ? $order->get_customer_id() : 0;
			    $shipping_metd  = $order->get_shipping_method();
			    $country		= ( $order->get_shipping_country() ) ? $order->get_shipping_country() : $order->get_billing_country();
			    $state			= ( $order->get_shipping_state() ) ? $order->get_shipping_state() : $order->get_billing_state();
			    $city			= ( $order->get_shipping_city() ) ? $order->get_shipping_city() : $order->get_billing_city();
			    
			    if ( $country && isset( WC()->countries->countries[ $country ] ) && WC()->countries->countries[ $country ] ) {
				    $country = WC()->countries->countries[ $country ]; 
			    }
			    
			    $fees_total 	= 0;
			    foreach( $fees as $k => $fee ) {
				    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
				    	$fees_total += $fee->get_total();
				    }
				    else {
					    $fees_total += $fee['line_total'];
				    }
			    }
			    				    
			    $payment		= $order->get_payment_method();
			    $coupons        = ( $order->get_used_coupons() ) ? $order->get_used_coupons() : array();
			    $coupons	    = implode( ',', $coupons );
			    $discount       = ( $order->get_discount_total() ) ? $order->get_discount_total() : 0.0000;
			    $net		   -= $discount;
			    $total_products = count( $order->get_items() );
			    $user_agent 	= get_post_meta( $order->get_id(), '_customer_user_agent', true );
				$items 			= array();
				$item_qtt 		= 0;
				$item_total     = 0.00;  
				$avg_product    = 0.00;
				$items_nbr      = 0;
			    		    
			    foreach ( $order->get_items() as $k => $v ) {
				    				    				    				    
				    $new_item = $this->syncItemMeta( $v ,$order, $k, $status );
				    // Inserts item meta data
				    if ( $new_item ) {
					    $items[] = $new_item;
				    }
				    else {
					    // Error occured while syncing order data, so remove everything related to that order
					    $order_product_database = new RST_Reporting_Order_Products_Database();
					    $order_database 		= new RST_Reporting_Order_Products_Database();
					    $order_product_database->delete( array( 'order_id' => $order_id ) );
					    $order_database->delete( array( 'order_id' => $order_id ) );
		    
					    return false;
				    }
				    
				    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {   
					    if ( $v->get_quantity() ) {
						    $item_qtt      	+= $v->get_quantity(); 
						    $item_total     += number_format( ( float ) abs( $v->get_total() ) / $v->get_quantity(), 4 );
						    $items_nbr++;
					    }
				    }
				    else {
					    if ( $v['qty'] ) { 
						    $item_qtt      	+= $v['qty']; 
						    $item_total     += number_format( ( float ) abs( $v['line_total'] ) / $v['qty'], 4 );
						    $items_nbr++;
					    }
				    }
			    }
			    
			    if ( $items_nbr ) {
			    	$avg_product = number_format( ( float ) abs( $item_total ) / $items_nbr, 4 );
			    }
			    
			    $refunds_data = array();
			    			    
			    // Inserts refunds into events table
			    foreach ( $order->get_refunds() as $refund ) {		
				    					
					$refund_items = $refund->get_items();
					$total_qty    = 0;
					$total_items  = 0;
					
					if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
						$refund_amount = $refund->get_amount();
					}
					else {
						$refund_amount = $refund->get_refund_amount();
					}
					
					if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
						$refund_id 		 = $refund->get_id();
						$date_ref 	   	 = $refund->get_date_created();
						$date_formated   = $date_ref->date_i18n( 'Y-m-d H:i:s' );
					}
					else {
						$refund_id 		 = $refund->id;
						$date_formated 	 = $refund->date;
					}
		
					if ( !empty( $refund_items ) ) {
						foreach ( $refund_items as $item ) {
														
							if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
								$product_id   = ( $item->get_variation_id() ) ? $item->get_variation_id() : $item->get_product_id();
								$quantity     = abs( $item->get_quantity() );
								$total        = number_format( ( float ) abs( $item->get_total() ) + abs( $item->get_total_tax() ), 4 );
								$total_items += $total;
							}
							else {
								$product_id   = ( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'];
								$quantity     = abs( $item['qty'] );
								$total        = number_format( ( float ) abs( $item['line_total'] ) + abs( $item['line_tax'] ), 4 );
								$total_items += $total;
							}
							
							$categories = '';
						    $terms = get_the_terms( $product_id, 'product_cat' );
						    if ( $terms ) {
								foreach ( $terms as $term ) {
									if( $categories ){
								    	$categories .= ',' . $term->name;
								    }
								    else {
									    $categories = $term->name;
								    }
								}
							}
														
							if ( $quantity > 0 ) {													
								$refunds_data[]     = array( 'order_id' => $order_id, 'meta1' => $product_id, 'meta2' => $total, 'meta3' => $categories, 'type' => 2, 'notes' => "refund #$refund_id", 'datetime' => $date_formated, 'order_status' =>  $status );			
							}		
							
							if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) ) {
								$total_qty += $quantity;	
							}		
						}		
						
						if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) ) {
							if ( $total_qty == 0 ) {
								$refund_amount      = number_format( ( float ) abs( $refund_amount ), 4 );
								$refunds_data[]     = array( 'order_id' => $order_id, 'meta1' => 0, 'meta2' => $refund_amount, 'meta3' => '', 'type' => 2, 'notes' => "refund #$refund_id", 'datetime' => $date_formated, 'order_status' =>  $status );							
							}
						}
						if ( $total_items < $refund_amount ) {
							$difference 	= number_format( ( float ) abs( $refund_amount - $total_items ), 4 );
							if ( $difference ) {
								$refunds_data[] = array( 'order_id' => $order_id, 'meta1' => 0, 'meta2' => $difference, 'meta3' => '', 'type' => 2, 'notes' => "refund #$refund_id", 'datetime' => current_time( 'mysql' ), 'order_status' =>  $status );
							}
						}
					}
					else {
						$refund_amount     = number_format( ( float ) abs( $refund_amount ), 4 );
						$refunds_data[]    = array( 'order_id' => $order_id, 'meta1' => 0, 'meta2' => $refund_amount, 'meta3' => '', 'type' => 2, 'type' => 2, 'notes' => "refund #$refund_id", 'datetime' => $date_formated, 'order_status' =>  $status );						
					}
					
			    }
			    			    
			    $browser       = '';
			    $os 		   = '';
			    $platform      = '';
			    $source 	   = 'web';
			    
			    if ( get_post_meta( $order_id, '_pos', TRUE ) ) {
				    $source = 'pos';
			    }
			    else if( get_post_meta( $order_id, 'wc_pos_order_type', TRUE ) == 'POS' ) {
				    $source = 'pos';
			    }
			    else if ( get_post_meta( $order_id, '_it_omni_ebay_order_id', TRUE ) ) {
				    $source = 'ebay';
			    }
			    else if ( get_post_meta( $order_id, '_it_omni_amazon_order_id', TRUE ) ) {
				    $source = 'amazon';
			    }
			    else if ( get_post_meta( $order_id, '_it_omni_referer_source', TRUE ) ) {
				    $source = get_post_meta( $order_id, '_it_omni_referer_source', TRUE );
			    }
			    
			    if ( $user_agent ) {
				    $ua_obj 	= $this->getBrowser( $user_agent, TRUE );
				    $browser 	= isset( $ua_obj['name'] ) ? $ua_obj['name'] : '';
				    $platform 	= isset( $ua_obj['type'] ) ? $ua_obj['type'] : '';
				    $os 		= isset( $ua_obj['platform'] ) ? $ua_obj['platform'] : '';
			    }
			    
			    // Save order data into custom database
			    $order_data    = array( 'order_id' 		   => $order_id, 
			    						'date' 			   => $date, 
			    						'total_gross' 	   => $gross, 
			    						'total_net' 	   => $net, 
			    						'total_fees' 	   => $fees_total,  
			    						'total_shipping'   => $shipping, 
			    						'total_refunded'   => $refunded, 
			    						'total_taxes' 	   => $taxes, 
			    						'num_products' 	   => $total_products, 
			    						'num_items' 	   => $item_qtt, 
			    						'avg_product' 	   => $avg_product, 
			    						'status' 		   => $status, 
			    						'customer' 		   => $customer, 
			    						'shipping' 		   => $shipping_metd, 
			    						'country' 		   => $country, 
			    						'state' 		   => $state, 
			    						'city' 		 	   => $city, 
			    						'payment' 		   => $payment, 
			    						'operating_system' => $os, 
			    						'platform' 		   => $platform, 
			    						'browser' 		   => $browser, 
			    						'coupon' 		   => $coupons, 
			    						'value_discount'   => $discount,
			    						'source'		   => $source
			    					  );
			    			    
			    return array( 'order' => $order_data, 'items' => $items, 'refunds' => $refunds_data );
			    
		    }
	    } catch ( Exception $e ) {
		    
		    // Error occurred while importing order, delete it
		    $order_product_database = new RST_Reporting_Order_Products_Database();
		    $order_database 	    = new RST_Reporting_Order_Products_Database();
		    $order_product_database->delete( array( 'order_id' => $order_id ) );
		    $order_database->delete( array( 'order_id' => $order_id ) );
		    
		    return false;
	    }
	    
	    return false;
    }
    
    /* 
	    Saves order item meta into custom database
    */
    public function syncItemMeta( $item, $order, $key, $status ) {
	    
	    try {
		    
		    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {   
			    $product 		= $item->get_product_id();
			    if ( $item->get_variation_id() ) {
			    	$product 	= $item->get_variation_id();
			    }
			    
			    $price      	= $item->get_total();
			    $quantity      	= $item->get_quantity(); 
		    }
		    else {
			    $product 		= $item['product_id'];
			    if ( $item['variation_id'] ) {
			    	$product 	= $item['variation_id'];
			    }
			    
			    $price      	= $item['line_total'];
			    $quantity      	= $item['qty']; 
		    }
		    		    
		    $product_obj	= new IT_WC_Product( $product );
		    $refunded_qty   = abs( $order->get_qty_refunded_for_item( $key ) );
		    $refunded_tot   = abs( $order->it_get_total_refunded_for_item( $key ) );
		    $type  			= $product_obj->get_type();
		    $categories 	= '';
		    /*if ( $type == 'variation' ) {
			    $main_product = $product_obj->get_parent_id();
		    } 
		    else {
			    $main_product = $product;
		    }*/
		    $main_product = $product;
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
			
			$date = ( $order->get_date_created() ) ? $order->get_date_created() : current_time( 'mysql' );
			
			if ( $date instanceof WC_DateTime ) {
				$date  = $date->date_i18n('Y-m-d H:i:s');
			}
		    
		    $item_data = array( 'order_id' 		 	 => $order->get_id(), 
		    				    'product' 			 => $product, 
		    					'category' 			 => $categories, 
		    					'price' 			 => $price, 
		    					'quantity' 		 	 => $quantity, 
		    					'product_type' 	 	 => $type, 
		    					'quantity_refunded'  => $refunded_qty, 
		    					'total_refunded' 	 => $refunded_tot, 
		    					'status' 			 => $status, 
		    					'datetime' 		 	 => $date, 
		    				  );
		    
		    return $item_data;
	    }
	    catch ( Exception $e ) {
		    return false;
	    }
	    
	    return false;
    }
    
    /* 
	    Saves product data into custom database
    */  
    public function syncProduct( $product_id ) {
	    try {
		    $product = new IT_WC_Product( $product_id );
		    		    
		    $sku 	  		= $product->get_sku();
		    $name 	  		= $product->get_name();
		    $image_id 		= $product->get_image_id();
		    $image 	  	  	= wp_get_attachment_url( $image_id );
		    $product_data 	= array( 'product_id' => $product_id, 
		    						 'sku' 		  => $sku, 
		    						 'name' 	  => $name, 
		    						 'image' 	  => $image,
		    					   );
		    
		    return $product_data;		    
	    }
	    catch ( Exception $e ) {
		    return false;
	    }
	    
	    return false;
    }
    
    public function startImport() {
	    
	    // Check if there is no other instance importing at the same time
	    if ( !get_option( 'it_rooster_reporting_is_importing' ) ) {
		    
		    try {
			    global $wpdb;
			    $memory_limit_str = ini_get('memory_limit');
			    $multiplier 	  = 1;

			    if ( strpos( $memory_limit_str, 'K' ) !== false ) {
				    $multiplier = 1000;
			    }
			    else if ( strpos( $memory_limit_str, 'M' ) !== false ) {
				    $multiplier = 1000000;
			    }
			    else if ( strpos( $memory_limit_str, 'G' ) !== false ) {
				    $multiplier = 1000000000;
			    }
			    
			    $memory_limit = intval( $memory_limit_str ) * $multiplier;
			    $limit_count  = (int) ( $memory_limit ) / 4400000;  
			    $new_orders   = 0;
			    $new_products = 0;
			    $end 		  = false;
			    
			    // Get import data
			    $import_data  = get_option( 'it_rooster_reporting_import_status' );
			    
			    // Flag that defines that an instance of importing is already running
			    update_option( 'it_rooster_reporting_is_importing', 1 );
			    
			    $order_status = array_keys( wc_get_order_statuses() );
			    		    		    
			    $products_total = $wpdb->get_row( "SELECT COUNT(*) as count FROM $wpdb->posts WHERE ( post_type = 'product' OR post_type = 'product_variation' ) AND post_status = 'publish'" );
			    
			    $orders_total   = $wpdb->get_row( "SELECT COUNT(*) as count FROM $wpdb->posts WHERE post_type = 'shop_order' AND post_status IN ('" . implode( '\',\'', $order_status ) . "')" );
			    		    		    		    		    
			    $total_orders   = ( $orders_total->count ) 	 ? $orders_total->count     : 0;
			    $total_products = ( $products_total->count ) ? $products_total->count   : 0;
			    		    
			    // Get orders to import
			    $orders = get_posts( array(
				    'posts_per_page' => 650,
				    'meta_query' 	 => array(
					    'relation' => 'AND',
				        array(
				            'key' 		=> 'it_rooster_reporting_imported',
				            'compare' 	=> 'NOT EXISTS',
				        ),
				        array(
				            'relation' => 'OR',
					        array(
					            'key' 		=> 'it_rooster_reporting_import_failure',
					            'value'     => 5,
					            'compare' 	=> '<',
				            ),
				            array(
					            'key' 		=> 'it_rooster_reporting_import_failure',
					            'compare'     => 'NOT EXISTS',
				            ),
				        ),
					),
				    'post_type'   	 => 'shop_order',
				    'post_status' 	 => array_keys( wc_get_order_statuses() ),
				    'fields' 		 => 'ids'
				) );
																
				if ( !empty( $orders ) ) {
					
					$orders_insert  = array();
					$items_insert   = array();
					$refunds_insert = array();
					
					// Loop though orders
					foreach ( $orders as $k => $v ) {
						
						$new_order = $this->syncOrder( $v );
						 
						// Sucess importing order
						if ( $new_order ) {
							$orders_insert[] = $new_order['order'];
							$items_new 	     = $new_order['items'];
							$refunds_new 	 = $new_order['refunds'];
							
							foreach ( $items_new as $new_item ) {
								$items_insert[] = $new_item;
							}
							
							foreach ( $refunds_new as $new_refund ) {
								$refunds_insert[] = $new_refund;
							}
							
							$new_orders++;
																																			
							if ( count( $orders_insert ) >= $limit_count ) {
								
								$this->wp_insert_rows( $orders_insert, IT_RST_REPORTING_ORDERS_DATABASE , 'orders');
								
								foreach ( $orders_insert as $order_insert ) {
									update_post_meta( $order_insert['order_id'], 'it_rooster_reporting_imported', 1 );
									update_post_meta( $order_insert['order_id'], 'it_rooster_reporting_db_version', IT_RST_REPORTING_MODULE_DB_VERSION );
								}
								
								$orders_insert 		= array();
								$import_data_update = array();
								
								if ( isset( $import_data['imported_orders'] ) ) {
										$import_data_update['imported_orders'] = $new_orders + $import_data['imported_orders'];
								}
								else {
									$import_data_update['imported_orders'] = $new_orders;
								}
								if ( !isset( $import_data['imported_products'] ) ) {
									$import_data_update['imported_products'] = 0;
								}
								else {
									$import_data_update['imported_products'] = $import_data['imported_products'];
								}
																	
								$import_data_update['total_products'] 	 = $total_products;
								$import_data_update['total_orders'] 	 = $total_orders;
								
								$import_data_update['progress']		  	 = ( ( $import_data_update['imported_orders'] + $import_data_update['imported_products'] ) / ( $total_orders + $total_products ) ) * 100;
								update_option( 'it_rooster_reporting_import_status', $import_data_update );
							}
							
							if ( count( $items_insert ) >= $limit_count ) {
								$this->wp_insert_rows( $items_insert, IT_RST_REPORTING_ORDER_PRODUCTS_DATABASE, 'items' );
								$items_insert = array();
							}			
							
							if ( count( $refunds_insert ) >= $limit_count ) {								
								$this->wp_insert_rows( $refunds_insert, IT_RST_REPORTING_EVENTS_DATABASE, 'refunds' );
								$refunds_insert = array();
							}		
						}
				
						// Failed to import order
						else {
							$failure = get_post_meta( $v, 'it_rooster_reporting_import_failure' );
							$failure++;
							update_post_meta( $v, 'it_rooster_reporting_import_failure', $failure );
						}
					}
					
					if ( !empty( $orders_insert ) ) {
						$this->wp_insert_rows( $orders_insert, IT_RST_REPORTING_ORDERS_DATABASE, 'orders' );
						
						foreach ( $orders_insert as $order_insert ) {
							update_post_meta( $order_insert['order_id'], 'it_rooster_reporting_imported', 1 );
							update_post_meta( $order_insert['order_id'], 'it_rooster_reporting_db_version', IT_RST_REPORTING_MODULE_DB_VERSION );
						}
					}
					
					if ( !empty( $items_insert ) ) {
						$this->wp_insert_rows( $items_insert, IT_RST_REPORTING_ORDER_PRODUCTS_DATABASE, 'items' );
					}
					
					if ( !empty( $refunds_insert ) ) {
						$this->wp_insert_rows( $refunds_insert, IT_RST_REPORTING_EVENTS_DATABASE, 'refunds' );
					}
					
					// Free memory
					unset( $orders_insert );
					unset( $items_insert );
					unset( $refunds_insert );
				}
				
				// If there are no orders to import start importing products
				else {
					
					// Get products to import
					$products = get_posts( array(
					    'posts_per_page' => 650,
					    'meta_query' 	 => array(
						    'relation' => 'AND',
					        array(
					            'key' 		=> 'it_rooster_reporting_imported',
					            'compare' 	=> 'NOT EXISTS',
					        ),
					        array(
						        'relation' => 'OR',
						        array(
						            'key' 		=> 'it_rooster_reporting_import_failure',
						            'value'     => 5,
						            'compare' 	=> '<',
					            ),
					            array(
						            'key' 		=> 'it_rooster_reporting_import_failure',
						            'compare'     => 'NOT EXISTS',
					            )
							)
						),
					    'post_type'   	 => array('product', 'product_variation'),
					    'post_status' 	 => 'publish',
					    'fields' 		 => 'ids',
					) );
					if ( !empty( $products ) ) {
						
						$products_insert = array();
						// Loop through products
						foreach ( $products as $k => $v ) {
							
							$new_product = $this->syncProduct( $v );
							// Sucess importing product
							
							if ( $new_product ) {
								$products_insert[] = $new_product;
								$new_products++;
								
								if ( count( $products_insert ) >= $limit_count ) {
								
									$this->wp_insert_rows( $products_insert, IT_RST_REPORTING_PRODUCTS_DATABASE, 'products' );
									
									foreach ( $products_insert as $product_insert ) {
										update_post_meta( $product_insert['product_id'], 'it_rooster_reporting_imported', 1 );
										update_post_meta( $product_insert['product_id'], 'it_rooster_reporting_db_version', IT_RST_REPORTING_MODULE_DB_VERSION );
									}
									
									$products_insert = array();
									$import_data_update = array();
									
									if ( isset( $import_data['imported_products'] ) ) {
										$import_data_update['imported_products']   = $new_products + $import_data['imported_products'];
									}
									else{
										$import_data_update['imported_products']   = $new_products;
									}
									if ( !isset( $import_data['imported_orders'] ) ) {
										$import_data_update['imported_orders'] = 0;
									}
									else {
										$import_data_update['imported_orders'] = $import_data['imported_orders'];
									}
																		
									$import_data_update['total_products'] 	 = $total_products;
									$import_data_update['total_orders'] 	 = $total_orders;
									$import_data_update['progress']		  	 = ( ( $import_data_update['imported_orders'] + $import_data_update['imported_products'] ) / ( $total_orders + $total_products ) ) * 100;
									update_option( 'it_rooster_reporting_import_status', $import_data_update );
								}								
							}
							// Failed to import product
							else {
								$failure = get_post_meta( $v, 'it_rooster_reporting_import_failure', true );
								$failure++;
								update_post_meta( $v, 'it_rooster_reporting_import_failure', $failure );
							}
						}
						
						if ( !empty( $products_insert ) ) {
							$this->wp_insert_rows( $products_insert, IT_RST_REPORTING_PRODUCTS_DATABASE, 'products' );
							
							foreach ( $products_insert as $product_insert ) {
								update_post_meta( $product_insert['product_id'], 'it_rooster_reporting_imported', 1 );
								update_post_meta( $product_insert['product_id'], 'it_rooster_reporting_db_version', IT_RST_REPORTING_MODULE_DB_VERSION );
							}
						}
					}
					else {
						// Nothing to import
						$end = true;
					}
				}
				
				// Create/Update import data option
				if ( empty( $import_data ) ) {
					$import_data 					  = array();
					$import_data['imported_products'] = $new_products;
					$import_data['imported_orders']   = $new_orders;
					$import_data['total_products'] 	  = $total_products;
					$import_data['total_orders'] 	  = $total_orders;
					$import_data['progress']		  = ( ( $new_orders + $new_products ) / ( $total_orders + $total_products ) ) * 100;
				}
				else {
					$import_data['imported_products'] += $new_products;
					$import_data['imported_orders']   += $new_orders;
					$import_data['progress']		  = ( ( $import_data['imported_orders'] + $import_data['imported_products'] ) / ( $import_data['total_orders'] + $import_data['total_products'] ) ) * 100;
				}
				
				// If there are no items to import set progress to 100%
				if ( $end ) {
					$import_data['progress'] = 100;
					
					// Send notification email in case it was not sent already
					if ( !isset( $import_data['notification'] ) || !$import_data['notification'] ) {
						$this->sendNotificationEmail();
						$import_data['notification'] = 1;
					}
				}
				
				else if ( $import_data['progress'] > 100 ) {
					$import_data['progress'] = 100;
					
					// Send notification email in case it was not sent already
					if ( !isset( $import_data['notification'] ) || !$import_data['notification'] ) {
						$this->sendNotificationEmail();
						$import_data['notification'] = 1;
					}
				}
				
				update_option( 'it_rooster_reporting_import_status', $import_data );
				update_option( 'it_rooster_reporting_is_importing', 0 );
				
				if ( $end ) {
					
					// Check if db upgrade is needed, if so run it
					$db_upgrade 	= new RST_Reporting_Database_Upgrade();
					$upgrade_result = $db_upgrade->run_upgrade();
										
					if ( 'done' == $upgrade_result ) {
						if( $next_timestamp = wp_next_scheduled ( 'it_rooster_import_cron' ) ){
							wp_unschedule_event( $next_timestamp, 'it_rooster_import_cron', array() );
						}
						$ithemes_api 		= IT_iThemes_API::get_instance();
					    return $ithemes_api->requestPing( '', 0 );
						return true;
					}
				}
				
				$queries_cache = new RST_Reporting_Queries_Cache();
			    $queries_cache->delete_orders_cache();
				
				return true;
			} catch ( Exception $e ) {
		    	update_option( 'it_rooster_reporting_is_importing', 0 );
	    	}
										
		}
		else {
			return false;
		}
    }
    
    // Sends import completed notification email
    public function sendNotificationEmail() {
	    
	    add_filter( 'wp_mail_content_type', array( $this, 'setEmailToHTML' ) );
	    $admin_email = get_option('admin_email');
	    $subject 	 = 'iThemes Sales Accelerator Dashboard is Ready!';	    
	    $admin_email = get_option( 'admin_email' );
	    $user 		 = get_user_by( 'email', $admin_email );
		
		if ( defined( 'IT_RST_PLUGIN_PREMIUM_ACTIVE' ) ) { 
			$message = file_get_contents( dirname( __FILE__ ) . '/welcome-pro.htm', FILE_USE_INCLUDE_PATH );
		}
		else {
			$message = file_get_contents( dirname( __FILE__ ) . '/welcome-free.htm', FILE_USE_INCLUDE_PATH );
		}
		
		if ( isset( $user->display_name ) ) {
			$message = str_replace( '[username]', $user->display_name, $message );
		}
		else {
			$message = str_replace( '[username]', 'there', $message );
		}
		
		$dashboard 	     = ( get_option('rst_dashboard') ) ? '?rst_dashboard=1' : '';
	    $dashboard_url   = get_dashboard_url() . $dashboard;
	    $message 	     = str_replace( '[dashboard_url]', $dashboard_url, $message );
	    $unsubscribe_url = admin_url() . 'admin.php?page=ithemes-sales-acc-plugin-settings';
	    $message 		 = str_replace( '[unsubscribe_url]', $unsubscribe_url, $message );
	    $status 		 = wc_mail( $admin_email, $subject, $message, array() );
	    
	    remove_filter( 'wp_mail_content_type', array( $this, 'setEmailToHTML' ) );
    }
    
    public function setEmailToHTML() {
	    
	    return 'text/html';
    }
    
    
	public function wp_insert_rows( $row_arrays = array(), $wp_table_name, $source = '', $update = false, $primary_key = null ) {
		
		global $wpdb;
		$wp_table_name = $wpdb->remove_placeholder_escape( esc_sql( $wp_table_name ) );
		
		// Setup arrays for Actual Values, and Placeholders
		$values        = array();
		$place_holders = array();
		$query         = '';
		$query_columns = '';
		
		$query .= "INSERT INTO `{$wp_table_name}` (";
		foreach ( $row_arrays as $count => $row_array ) {
			foreach ( $row_array as $key => $value ) {
				if ($count == 0) {
					if ( $query_columns ) {
						$query_columns .= ", " . $key . "";
					} else {
						$query_columns .= "" . $key . "";
					}
				}
				
				$values[] = $value;
				
				$symbol = '%s';
				if ( $source == 'refunds' && ( $key == 'meta2' ) ) {
					$symbol = '%f';
				}
				else if ( $source == 'items' && ( $key == 'price' || $key == 'total_refunded' ) ) {
					$symbol = '%f';
				}
				else if ( $source == 'orders' && ( $key == 'total_gross' || $key == 'total_net' || $key == 'total_shipping' || $key == 'total_refunded' || $key == 'total_fees' || $key == 'total_taxes' || $key == 'value_discount' || $key == 'avg_product' ) ) {
					$symbol = '%f';
				}
				else if ( is_numeric( $value ) ) {
					if ( is_float( $value ) ) {
						$symbol = '%f';
					} else {
						$symbol = '%d';
					}
				}
				if ( isset( $place_holders[ $count ] ) ) {
					$place_holders[$count] .= ", '$symbol'";
				} else {
					$place_holders[$count] = "( '$symbol'";
				}
			}
			// mind closing the GAP
			$place_holders[$count] .= ')';
		}
				
		$query .= " $query_columns ) VALUES ";
		$query .= implode(', ', $place_holders);
		
		if ( $update ) {
			$update = " ON DUPLICATE KEY UPDATE $primary_key=VALUES( $primary_key ),";
			$cnt    = 0;
			foreach ( $row_arrays[0] as $key => $value ) {
				if ( $cnt == 0 ) {
					$update .= "$key=VALUES($key)";
					$cnt = 1;
				} else {
					$update .= ", $key=VALUES($key)";
				}
			}
			$query .= $update;
		}
								
		$sql = $wpdb->prepare( $query, $values );
		if ( $wpdb->query( $sql ) ) {
			return true;
		} else {
			return false;
		}
	}
    
    /*
	    Parses user agent data into array
    */
    public function getBrowser( $u_agent ) {
	    
	  $bname 	= 'N/A';
	  $platform = 'Unknown';
	  $version	= '';
	  $ub       = '';
	  $type     = '';
	  
	  // First get the platform?
	  if ( preg_match( '/android/i', $u_agent ) ) {
		$platform = 'android';
		$type     = 'mobile';
	  } elseif ( preg_match( '/iphone|cpu iphone os/i', $u_agent ) ) {
	    $platform = 'iOS';
	    $type     = 'mobile';
	  } elseif ( preg_match( '/Windows Phone/i', $u_agent ) ) {
	    $platform = 'Windows Phone';
	    $type     = 'mobile';
	  } elseif ( preg_match( '/linux/i', $u_agent ) ) {
	    $platform = 'linux';
	    $type     = 'desktop';
	  } elseif ( preg_match( '/macintosh|mac os x/i', $u_agent ) ) {
	    $platform = 'mac';
	    $type     = 'desktop';
	  } elseif ( preg_match( '/windows|win32/i', $u_agent ) ) {
	    $platform = 'windows';
	    $type     = 'desktop';
	  }
	  
	  // Next get the name of the useragent yes seperately and for good reason
	  if ( preg_match( '/MSIE/i',$u_agent ) && !preg_match( '/Opera/i',$u_agent ) ) {
	    $bname = 'Internet Explorer';
	    $ub = 'MSIE';
	  } elseif ( preg_match( '/Firefox/i',$u_agent ) ) {
	    $bname = 'Mozilla Firefox';
	    $ub = 'Firefox';
	  } elseif ( preg_match( '/Chrome/i',$u_agent ) ) {
	    $bname = 'Google Chrome';
	    $ub = 'Chrome';
	  } elseif ( preg_match( '/Safari/i',$u_agent ) ) {
	    $bname = 'Apple Safari';
	    $ub = 'Safari';
	  } elseif ( preg_match( '/Opera/i',$u_agent ) ) {
	    $bname = 'Opera';
	    $ub = 'Opera';
	  } elseif ( preg_match( '/Netscape/i',$u_agent ) ) {
	    $bname = 'Netscape';
	    $ub = 'Netscape';
	  }
	  
	  // finally get the correct version number
	  $known = array( 'Version', $ub, 'other' );
	  $pattern = '#(?<browser>' . join( '|', $known ) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	  if ( !preg_match_all( $pattern, $u_agent, $matches ) ) {
	    // we have no matching number just continue
	  }
	  
	  // see how many we have
	  $i = count( $matches['browser'] );
	  
	  // check if we have a number
	  if ( $version == null || $version == "" ) { 
		  $version = '?'; 
	  }
      return array(
 	    'userAgent' => $u_agent,
	    'name'      => $bname,
	    'version'   => $version,
	    'platform'  => $platform,
	    'pattern'   => $pattern,
	    'type'      => $type,
	  );
	}
}