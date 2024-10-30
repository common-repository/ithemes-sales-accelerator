<?php
	
if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

/**
* This class handles product creation/updates
**/

class RST_Reporting_Orders {
	
	public function __construct() {
		
		// Run only if there's no initial importing running at the same time
		if ( !get_option( 'it_rooster_reporting_is_importing' ) ) {
			add_action( 'woocommerce_new_order_item',            array( $this, 'sync_new_item'), 10, 3 );
			add_action( 'woocommerce_process_shop_order_meta', 	 array( $this, 'sync_order_to_db' ), 10, 1 );
			add_action( 'woocommerce_checkout_order_processed',  array( $this, 'sync_order_to_db' ), 10, 1 );
			add_action( 'woocommerce_api_create_order',     	 array( $this, 'api_sync_order' ), 10, 3 );
		}
		
		add_action( 'woocommerce_order_status_changed',		 	 array( $this, 'sync_order_status_change' ), 10, 3 );
		add_action( 'save_post',			 				 	 array( $this, 'sync_order_to_db' ), 10, 1 );
		add_action( 'woocommerce_order_refunded',  			 	 array( $this, 'sync_order_refunded'), 10, 2 );
		add_action( 'woocommerce_refund_deleted',  			 	 array( $this, 'sync_del_refund_to_db'), 10, 2 );
		
		if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {   
			add_action( 'woocommerce_update_order_item',         array( $this, 'sync_updated_item'), 10, 2 );
			add_action( 'woocommerce_before_delete_order_item',  array( $this, 'sync_deleted_item'), 10, 2 );
		}
		
		else {
			add_action( 'woocommerce_before_delete_order_item',  array( $this, 'sync_deleted_item_2_6'), 10, 1 );	
			add_action( 'woocommerce_update_order_item',         array( $this, 'sync_updated_item'), 10, 2 );
			add_action( 'woocommerce_saved_order_items',         array( $this, 'sync_updated_items'), 10, 2 );
			add_action( 'woocommerce_order_status_refunded',     array( $this, 'sync_order_refunded_2_6'), 10, 2 );
		}
		
		// Deletes order from database before being deleted from WooCommerce
		add_action( 'before_delete_post', 					 	 array( $this, 'delete_order_from_db' ), 10, 1 );
	}
	
	public function api_sync_order( $order_id, $data, $order ) {
		$this->sync_order_to_db( $order_id );
	}
		
	public function sync_order_to_db( $order_id ) {
		try {
		    $order 	   = new IT_WC_Order( $order_id );
		    $order_obj = $order->get_order();
		    
		    if ( is_object( $order_obj ) && $order_obj instanceof WC_Order && $order_obj->get_status() !== 'auto-draft' && $order_obj->get_status() !== 'draft' ) {
			    
			    update_post_meta( $order_id, 'it_rooster_reporting_imported', TRUE );
			    update_post_meta( $order_id, 'it_rooster_reporting_db_version', IT_RST_REPORTING_MODULE_DB_VERSION );
			    
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
			    			    
			    if ( $country && isset( WC()->countries->countries[ $country] ) && WC()->countries->countries[ $country ] ) {
				    $country = WC()->countries->countries[ $country ]; 
			    }
			    
			    $fees_total 	= 0;
			    foreach ( $fees as $k => $fee ) {
				    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
				    	$fees_total += $fee->get_total();
				    }
				    else if ( isset( $fee['line_total'] ) ) {
					    $fees_total += $fee['line_total'];
				    }
			    }
			    
			    if ( $net < 0  && $refunded == $gross ) {
				    $net = 0;
			    }
			    				    
			    $payment		= $order->get_payment_method();
			    $coupons        = ( $order->get_used_coupons() ) ? $order->get_used_coupons() : array();
			    $coupons	    = implode( ',', $coupons );
			    $discount       = ( $order->get_discount_total() ) ? $order->get_discount_total() : 0.0000;
			    $net		   -= $discount;
			    $total_products = count( $order->get_items() );
			    			    
			    $user_agent 	= get_post_meta( $order->get_id(),'_customer_user_agent',true );
			    $item_qtt 		= 0;
				$item_total     = 0.00;  
				$avg_product    = 0.00;
				$items_nbr      = 0;
			    		    
			    foreach ( $order->get_items() as $k=>$v ) {
				    
				    // Inserts item meta data
				    if ( $this->syncItemMeta( $v ,$order, $k ) ) {}
				    else {
					    return false;
				    }
				    
				    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) { 
					    if ( $v->get_quantity() ) {  
						    $item_qtt      	+= $v->get_quantity(); 
						    $item_total     += number_format( (float) abs( $v->get_total() ) / $v->get_quantity(), 4 );
						    $items_nbr++;
					    }
				    }
				    else {
					    if ( $v['qty'] ) { 
						    $item_qtt      	+= $v['qty']; 
						    $item_total     += number_format( (float) abs( $v['line_total'] ) / $v['qty'], 4 );
							$items_nbr++;
						}
				    }
			    }
			    
			    if ( $items_nbr ) {
			    	$avg_product = number_format( (float) abs( $item_total ) / $items_nbr, 4 );
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
				    $browser 	= isset( $ua_obj['name'] ) 	   ? $ua_obj['name'] 	 : '';
				    $platform 	= isset( $ua_obj['type'] ) 	   ? $ua_obj['type'] 	 : '';
				    $os 		= isset( $ua_obj['platform'] ) ? $ua_obj['platform'] : '';
			    }
			    
			    // Save order data into custom database
			    $order_data    = array( 'order_id' 	  	   => $order_id,
			    						'date' 	      	   => $date,
			    						'total_gross' 	   => $gross,
			    						'total_net'   	   => $net,
			    						'total_fees'       => $fees_total,
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
			    						'city' 		   	   => $city,
			    						'payment' 		   => $payment,
			    						'operating_system' => $os,
			    						'platform' 		   => $platform,
			    						'browser' 		   => $browser,
			    						'coupon' 		   => $coupons,
			    						'value_discount'   => $discount,
			    						'source'		   => $source
			    						
			    				 );
			    
			    $orders_database = new RST_Reporting_Orders_Database();
			    
			    $order_db = $orders_database->get_by( array( 'order_id' => $order_id ) );
			    
			    // If order already exists update
			    if ( !empty( $order_db ) && isset( $order_db[0] ) ) {
			    	$orders_database->update( $order_data, array( 'order_id' => $order_id )  );
			    }
			    // Otherwise create a new entry
			    else {
			    	$orders_database->insert( $order_data );
			    }
			    
			    // Change status on refunds table
			    $events_database = new RST_Reporting_Events_Database();
			    $events_database->update( array( 'order_status' => $status ), array( 'order_id' => $order_id, 'type' => 2 )  );
			    
			    $queries_cache = new RST_Reporting_Queries_Cache();
			    $queries_cache->delete_orders_cache();
			    
			    return true;
		    }
	    } catch ( Exception $e ) {
		    return false;
	    }
	}
	
	// Updates order and event custom tables on status change
	public function sync_order_status_change ( $order_id, $from, $to ) {
		
		$this->sync_order_to_db( $order_id );
		$events_database = new RST_Reporting_Events_Database();
		
		// Save event data into custom database
		$events_data     = array( 'order_id' => $order_id, 'meta1' => $from, 'meta2' => $to, 'meta3' => '', 'type' => 1, 'datetime' => current_time( 'mysql' ) );
		$events_database->insert( $events_data );
	}
	
	// Updates event custom tables on full refund for WC 2.6
	public function sync_order_refunded_2_6( $order_id ) {
		
		$this->sync_order_to_db( $order_id );
		$order   = new IT_WC_Order( $order_id );
		$status  = $order->get_status();
		$refunds = $order->get_refunds();
		
		if ( isset( $refunds[0] ) ){
			
			$events_database = new RST_Reporting_Events_Database();
			$amount    = $refunds[0]->get_refund_amount();
			$amount    = number_format( (float) abs( $amount ), 4 );
			$refund_id = $refunds[0]->id;
			
			if ( !$events_database->get_by( array( 'order_id' => $order_id, 'notes' => "refund #$refund_id" ) ) ){
			
				$refunds_data     = array( 'order_id' => $order_id, 'meta1' => 0, 'meta2' => $amount, 'meta3' => '', 'type' => 2, 'notes' => "refund #$refund_id", 'datetime' => current_time( 'mysql' ), 'order_status' =>  $status );
				$events_database->insert( $refunds_data );
			}
		}
	}
	
	// Updates order and event custom tables on new refund
	public function sync_order_refunded( $order_id, $refund_id ) {
		
		$this->sync_order_to_db( $order_id );
		$refund = new IT_WC_Order( $refund_id );
		$order  = new IT_WC_Order( $order_id );
		$status = $order->get_status();
		$events_database = new RST_Reporting_Events_Database();
		$refund_items = $refund->get_items();
		
		if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
			$refund_amount = $refund->get_amount();
		}
		else {
			$refund_amount = $refund->get_refund_amount();
		}
		
		$total_qty    = 0;
		$total_items  = 0;
				
		if ( !empty( $refund_items ) ) {
			foreach ( $refund_items as $item ) {
				
				if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
					$product_id   = ( $item->get_variation_id() ) ? $item->get_variation_id() : $item->get_product_id();
					$quantity     = abs( $item->get_quantity() );
					$total        = number_format( (float) abs( $item->get_total() ) + abs( $item->get_total_tax() ), 4 );
					$total_items += $total;
				}
				else {
					$product_id   = ( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'];
					$quantity     = abs( $item['qty'] );
					$total        = number_format( (float) abs( $item['line_total'] ) + abs( $item['line_tax'] ), 4 );
					$total_items += $total;
				}
				
				$categories = '';
			    $terms = get_the_terms( $product_id, 'product_cat' );
			    if ( $terms ) {
					foreach ( $terms as $term ) {
						if ( $categories ){
					    	$categories .= ',' . $term->name;
					    }
					    else {
						    $categories = $term->name;
					    }
					}
				}
				
				if ( $quantity > 0 ) {						
					$refunds_data     = array( 'order_id' => $order_id, 'meta1' => $product_id, 'meta2' => $total, 'meta3' => $categories, 'type' => 2, 'notes' => "refund #$refund_id", 'datetime' => current_time( 'mysql' ), 'order_status' =>  $status );
					$events_database->insert( $refunds_data );
				}
				
				if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) ) {
					$total_qty += $quantity;	
				}
			}		
			if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) ) {
				if ( $total_qty == 0 ) {
					$refund_amount      = number_format( (float) abs( $refund_amount ), 4 );
					$refunds_data     = array( 'order_id' => $order_id, 'meta1' => 0, 'meta2' => $refund_amount, 'meta3' => '', 'type' => 2, 'notes' => "refund #$refund_id", 'datetime' => current_time( 'mysql' ), 'order_status' =>  $status );
					$events_database->insert( $refunds_data );
				}
			}
			if ( $total_items < $refund_amount ) {
				$difference = number_format( (float) abs( $refund_amount - $total_items ), 4 );
				if ( $difference ) {
					$refunds_data     = array( 'order_id' => $order_id, 'meta1' => 0, 'meta2' => $difference, 'meta3' => '', 'type' => 2, 'notes' => "refund #$refund_id", 'datetime' => current_time( 'mysql' ), 'order_status' =>  $status );
					$events_database->insert( $refunds_data );
				}
			}
		}
		else {
			$refund_amount    = number_format( ( float ) abs( $refund_amount ), 4 );
			$refunds_data     = array( 'order_id' => $order_id, 'meta1' => 0, 'meta2' => $refund_amount, 'meta3' => '', 'type' => 2, 'notes' => "refund #$refund_id", 'datetime' => current_time( 'mysql' ), 'order_status' =>  $status );
			$events_database->insert( $refunds_data );
		}
	}
	
	// Updates order from custom database on refunds
	public function sync_del_refund_to_db( $refund_id, $order_id ) {
		
		$events_database = new RST_Reporting_Events_Database();
		$events_database->delete( array( 'order_id' => $order_id, 'notes' => "refund #$refund_id" ) );
		$this->sync_order_to_db( $order_id );
	}
	
	// Adds new order item from custom database
	public function sync_new_item( $item_id, $item, $order_id ) {
		
		$order 	   = new IT_WC_Order( $order_id );
		$order_obj = $order->get_order();
		if ( $order_obj instanceof WC_Order && $order->get_status() !== 'auto-draft' && $order->get_status() !== 'draft' ) {
			if ( $item instanceof WC_Order_Item_Product ) {
				$this->syncItemMeta( $item, $order, $item_id );
			}
			else {
				$this->sync_order_to_db( $order_id );
			}
		}
	}
	
	// Updates order item from custom database
	public function sync_updated_item( $item_id, $item ) {
		
		$order_id = $item->get_order_id();
		$order 	  = $item->get_order();
		if ( $order instanceof WC_Order && $order->get_status() !== 'auto-draft' && $order->get_status() !== 'draft' ) {
			if ( $item instanceof WC_Order_Item_Product ) {
				$this->syncItemMeta( $item, $order, $item_id );
			}
			else {
				$this->sync_order_to_db( $order_id );
			}
		}
	}
	
	// Called on calculate totals on WC 2.6
	public function sync_updated_items( $order_id, $items ) {
		
		$this->sync_order_to_db( $order_id );
	}
	
	// Deletes order item from custom database (WC > 3.0)
	public function sync_deleted_item( $item_id ) {
		
		$data_store = WC_Data_Store::load( 'order-item' );
		$type_item = $data_store->get_order_item_type( $item_id );

		if ( $type_item == 'line_item' ) {
			$item 	  = new WC_Order_Item_Product( $item_id );
			$order_id = $item->get_order_id();
			$this->deleteItemMeta( $item, $order_id );
		}
	}
	
	// Deletes order item from custom database (WC 2.6.x)
	public function sync_deleted_item_2_6( $item_id ) {
		
		$order_item 	 = new IT_WC_Order_Item( $item_id );
		$order_id 		 = $order_item->get_order_id();
		$order_item_type = $order_item->get_order_item_type();
		
		if ( $order_item_type == 'line_item' && $order_id ) {
			$product 		= $order_item->get_product_id();
		    if ( $order_item->get_variation_id() ) {
		    	$product 	= $order_item->get_variation_id();
		    }
		    
		    if ( $product ) {
			    $order_product_database = new RST_Reporting_Order_Products_Database();
			    $order_product_database->delete( array( 'order_id' => $order_id, 'product' => $product ) );
		    }
		}
	}
	
	// Deletes item meta from custiom database
	public function deleteItemMeta( $item, $order_id ) {
		
		$order_product_database = new RST_Reporting_Order_Products_Database();    
	    $product 				= $item->get_product_id();
	    
	    if( $item->get_variation_id() ){
	    	$product 			= $item->get_variation_id();
	    }
	    
	    $order_product_database->delete( array( 'order_id' => $order_id, 'product' => $product ) );
	}
	
	// Deletes order from custom database
	public function delete_order_from_db( $post_id ) {
		
		$post = get_post( $post_id );
		if ( $post->post_type !== 'shop_order' ) {
	        return;
	    }
	    
	    $order = new IT_WC_Order( $post_id );
	
	    if ( !$order ) {
	        return;
	    }    
	    
	    $orders_database	    = new RST_Reporting_Orders_Database();
	    $order_product_database = new RST_Reporting_Order_Products_Database();
	    
	    $orders_database->delete( array( 'order_id' => $post_id ) );
	    $order_product_database->delete( array( 'order_id' => $post_id ) );
	}
	
	/* 
    Saves order item meta into custom database
	*/
	public function syncItemMeta ( $item, $order, $key ) {
		
	    try {
		    $order_product_database = new RST_Reporting_Order_Products_Database();
		    $order_id 			    = $order->get_id();
		    
		    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {   
			    $product 		= $item->get_product_id();
			    if( $item->get_variation_id() ){
			    	$product 	= $item->get_variation_id();
			    }
			    
			    $price      	= $item->get_total();
			    $quantity      	= $item->get_quantity(); 
		    }
		    else {
			    if ( isset( $item['product_id'] ) ) {
				    $product 		= $item['product_id'];
				    if( $item['variation_id'] ){
				    	$product 	= $item['variation_id'];
				    }
				    
				    $price      	= $item['line_total'];
				    $quantity      	= $item['qty']; 
			    }
		    }
		    if ( isset( $product ) && isset( $price ) && isset( $quantity ) ) {
		    
			    $product_obj	= new IT_WC_Product( $product );
			    
			    if ( !$order instanceof IT_WC_Order ) {
				    $order 		= new IT_WC_Order( $order );
			    }
			    
			    $refunded_qty   = abs( $order->get_qty_refunded_for_item( $key ) );
			    $refunded_tot   = abs( $order->it_get_total_refunded_for_item( $key ) );
			    $type  			= $product_obj->get_type();
			    $categories	    = '';
			    $categories 	= '';
			    if ( $type == 'variation' ) {
				    $main_product = $product_obj->get_parent_id();
			    } 
			    else {
				    $main_product = $product;
			    }
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
			    
			    $date 	   = ( $order->get_date_created() ) ? $order->get_date_created() : current_time( 'mysql' );
			    $status    = $order->get_status();
			    
			    if ( $date instanceof WC_DateTime ) {
				    $date  = $date->date_i18n( 'Y-m-d H:i:s' );
			    }
			    
			    $item_data = array( 'order_id' => $order_id, 'product' => $product, 'category' => $categories, 'price' => $price, 'quantity' => $quantity, 'product_type' => $type, 'quantity_refunded' => $refunded_qty, 'total_refunded' => $refunded_tot, 'status' => $status, 'datetime' => $date );
			    
			    $product_db = $order_product_database->get_by( array( 'order_id' => $order_id, 'product' => $product) );
			    if ( !empty( $product_db ) && isset( $product_db[0] ) ) {
			    	$order_product_database->update( $item_data, array( 'order_id' => $order_id, 'product' => $product )  );
			    }
			    else {
			    	$order_product_database->insert( $item_data );
			    }
			    
			    return true;
		    }
		    
		    return false;
	    }
	    catch ( Exception $e ) {
		    return false;
	    }
	    
	    return false;
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
	  if( preg_match( '/MSIE/i', $u_agent ) && !preg_match( '/Opera/i', $u_agent ) ) {
	    $bname    = 'Internet Explorer';
	    $ub 	  = 'MSIE';
	  } elseif( preg_match( '/Firefox/i', $u_agent ) ) {
	    $bname    = 'Mozilla Firefox';
	    $ub 	  = 'Firefox';
	  } elseif( preg_match( '/Chrome/i', $u_agent ) ) {
	    $bname    = 'Google Chrome';
	    $ub 	  = 'Chrome';
	  } elseif( preg_match( '/Safari/i', $u_agent ) ) {
	    $bname 	  = 'Apple Safari';
	    $ub 	  = 'Safari';
	  } elseif( preg_match( '/Opera/i', $u_agent ) ) {
	    $bname 	  = 'Opera';
	    $ub 	  = 'Opera';
	  } elseif( preg_match( '/Netscape/i', $u_agent ) ) {
	    $bname 	  = 'Netscape';
	    $ub 	  = 'Netscape';
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
	  if ( $version == null || $version == '' ) {
	  	$version = '?'; 
	  }
	  return array(
	    'userAgent' => $u_agent,
	    'name'      => $bname,
	    'version'   => $version,
	    'platform'  => $platform,
	    'pattern'   => $pattern,
	    'type'      => $type
	  );
	}
}