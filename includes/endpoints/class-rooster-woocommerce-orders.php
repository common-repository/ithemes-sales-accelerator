<?php

if ( !defined( 'ABSPATH' ) ) {
    exit();
}

/**
* This class provides endpoints to access order data
**/

class IT_RST_WooCommerce_Orders extends WP_REST_Controller {
    
    // Singleton design pattern
    protected static $instance = NULL;
    private $server;
    private $post_type = 'shop_order';
    
    // Method to return the singleton instance
    public static function get_instance() {
	    
        if ( null == self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
	    
	    $this->server = new WP_REST_Server();
	    $this->rest_base = 'orders';
	    $this->namespace = 'wc/v1';
    }    
    
    // Get all orders/Get orders by parameters
    public function get_orders( $request ) {
	    
	    ob_clean();
		return $this->search_orders( $request );
	}
	
	// Prepare links for rest response
	protected function prepare_links( $order ) {
		
		$links = array(
			'self' => array(
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $order->get_id() ) ),
			),
			'collection' => array(
				'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ),
			),
		);

		if ( 0 !== (int) @$order->get_order()->post->post_parent ) {
			$links['up'] = array(
				'href' => rest_url( sprintf( '/%s/orders/%d', $this->namespace, @$order->get_order()->post->post_parent ) ),
			);
		}

		return $links;
	}
	
	// Get customer by ID
	public function get_customer( $id, $fields = null ) {
		
		$customer    = new WC_Customer( $id );
		if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
			$_data       = $customer->get_data();
			$order_count = $customer->get_order_count();
			$total_spent = $customer->get_total_spent();
			$avatar_url  = $customer->get_avatar_url();
		}
		else {
			global $wpdb;
			$user = new WP_User( $id );
			
			$first_name = get_user_meta( $id, 'first_name', TRUE );
			$last_name  = get_user_meta( $id, 'last_name', TRUE );
			
			$billing             = array(
				'first_name'     => get_user_meta( $id, 'billing_first_name', TRUE ),
				'last_name'      => get_user_meta( $id, 'billing_last_name', TRUE ),
				'company'        => get_user_meta( $id, 'billing_company', TRUE ),
				'address_1'      => get_user_meta( $id, 'billing_address_1', TRUE ),
				'address_2'      => get_user_meta( $id, 'billing_address_2', TRUE ),
				'city'           => get_user_meta( $id, 'billing_city', TRUE ),
				'state'          => get_user_meta( $id, 'billing_state', TRUE ),
				'postcode'       => get_user_meta( $id, 'billing_postcode', TRUE ),
				'country'        => get_user_meta( $id, 'billing_country', TRUE ),
				'email'          => get_user_meta( $id, 'billing_email', TRUE ),
				'phone'          => get_user_meta( $id, 'billing_phone', TRUE ),
			);
			
			$shipping            = array(
				'first_name'     => get_user_meta( $id, 'shipping_first_name', TRUE ),
				'last_name'      => get_user_meta( $id, 'shipping_last_name', TRUE ),
				'company'        => get_user_meta( $id, 'shipping_company', TRUE ),
				'address_1'      => get_user_meta( $id, 'shipping_address_1', TRUE ),
				'address_2'      => get_user_meta( $id, 'shipping_address_2', TRUE ),
				'city'           => get_user_meta( $id, 'shipping_city', TRUE ),
				'state'          => get_user_meta( $id, 'shipping_state', TRUE ),
				'postcode'       => get_user_meta( $id, 'shipping_postcode', TRUE ),
				'country'        => get_user_meta( $id, 'shipping_country', TRUE ),
				'email'          => get_user_meta( $id, 'shipping_email', TRUE ),
			);

			$_data = array( 
				'id' 			=> $id, 
				'date_created'  => $user->user_registered, 
				'date_modified' => $user->user_registered, 
				'email' 		=> $user->user_email, 
				'first_name'    => $first_name, 
				'last_name'     => $last_name, 
				'username' 	    => $user->user_login, 
				'billing' 	    => $billing, 
				'shipping' 	    => $shipping,
			);
			
			$statuses = array_map( 'esc_sql', wc_get_order_statuses() );
			$statuses = array_map( array( $wpdb, 'remove_placeholder_escape' ), $statuses );
			
			unset( $statuses['wc-pending'] );
			unset( $statuses['wc-on-hold'] );
			unset( $statuses['wc-cancelled'] );
			unset( $statuses['wc-refunded'] );
			unset( $statuses['wc-failed'] );
						
			$count = $wpdb->get_var( "SELECT COUNT(*)
				FROM $wpdb->posts as posts
				LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
				WHERE   meta.meta_key = '_customer_user'
				AND     posts.post_type = 'shop_order'
				AND     posts.post_status IN ( '" . implode( "','", $statuses ) . "' )
				AND     meta_value = '" . $wpdb->remove_placeholder_escape( esc_sql( $id ) ) . "'
			" );
			
			$spent    = $wpdb->get_var( apply_filters( 'woocommerce_customer_get_total_spent_query', "SELECT SUM(meta2.meta_value)
				FROM $wpdb->posts as posts
				LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
				LEFT JOIN {$wpdb->postmeta} AS meta2 ON posts.ID = meta2.post_id
				WHERE   meta.meta_key       = '_customer_user'
				AND     meta.meta_value     = '" . $wpdb->remove_placeholder_escape( esc_sql( $id ) ) . "'
				AND     posts.post_type     = 'shop_order'
				AND     posts.post_status   IN ( 'wc-" . implode( "','wc-", $statuses ) . "' )
				AND     meta2.meta_key      = '_order_total'
			", $customer ) );

			if ( !$spent ) {
				$spent = 0;
			}
						
			$order_count = abs( $count );
			$total_spent = abs( $spent );
			$avatar_url  = get_avatar_url( $id );
		}
		$last_order  = wc_get_customer_last_order( $id );
		$last_order  = new IT_WC_Order( $last_order );
		$format_date = array( 'date_created', 'date_modified' );

		// Format date values.
		foreach ( $format_date as $key ) {
			$_data[ $key ] = $_data[ $key ] ? wc_rest_prepare_date_response( $_data[ $key ] ) : null; // v1 API used UTC.
		}

		$data = array(
			'id'            => $_data['id'],
			'date_created'  => $_data['date_created'],
			'date_modified' => $_data['date_modified'],
			'email'         => $_data['email'],
			'first_name'    => $_data['first_name'],
			'last_name'     => $_data['last_name'],
			'username'      => $_data['username'],
			'last_order'    => array(
				'id'   => is_object( $last_order ) ? $last_order->get_id() : null,
				'date' => is_object( $last_order ) ? wc_rest_prepare_date_response( $last_order->get_date_created() ) : null, // v1 API used UTC.
			),
			'orders_count'  => $order_count,
			'total_spent'   => $total_spent,
			'avatar_url'    => $avatar_url,
			'billing'       => $_data['billing'],
			'shipping'      => $_data['shipping'],
		);

		$context  = ! empty( $request['context'] ) ? $request['context'] : 'view';

		return array( 'customer' => apply_filters( 'woocommerce_api_customer_response', $data, $customer, $fields, $this->server ) );
	} 
	
	/**
	 * Wrapper for @see get_avatar() which doesn't simply return
	 * the URL so we need to pluck it from the HTML img tag
	 *
	 * Kudos to https://github.com/WP-API/WP-API for offering a better solution
	 *
	 * @since 2.1
	 * @param string $email the customer's email
	 * @return string the URL to the customer's avatar
	 */
	private function get_avatar_url( $email ) {
		
		$avatar_html = get_avatar( $email );

		// Get the URL of the avatar from the provided HTML
		preg_match( '/src=["|\'](.+)[\&|"|\']/U', $avatar_html, $matches );

		if ( isset( $matches[1] ) && ! empty( $matches[1] ) ) {
			return esc_url_raw( $matches[1] );
		}

		return null;
	}
	
	// Get order notes
	public function get_order_notes( $request ) {
		
		$params = $request->get_params();
		$id = isset( $params['id'] ) ? $params['id'] : 0;
		$url = site_url() . "/wp-json/wc/v1/orders/$id/notes";
	   		    
	    return IT_RST_Endpoints::get_instance()->make_rest_request( $url );    
	}
	
	// Create order note
	public function create_order_note( $request ) {
		
		$params = $request->get_params();
	    
	    if( isset( $params['id'] ) && $params['id'] && isset( $params['note'] ) && $params['note'] ){
		    $order_id 	 = $params['id'];
		    $note   	 = $params['note'];
		    $note_json 	 = json_encode( array( 'note' => $note ) );
		    
		    $url = site_url() . "/wp-json/wc/v1/orders/$order_id/notes";
		    
		    return IT_RST_Endpoints::get_instance()->make_rest_request( $url, 'POST', $note_json );    
	    }
	    else{
		    return array( 'result' => false, 'data' => array( 'note is a required parameter.' ) );
	    }
	}
	
	// Delete order note by ID
	public function delete_order_note( $request ){
		
		$params = $request->get_params();
	    
	    if( isset( $params['id'] ) && $params['id'] && isset( $params['noteID'] ) && $params['noteID'] ){
		    $order_id 	 = $params['id'];
		    $note   	 = $params['noteID'];
		    
		    $url = site_url() . "/wp-json/wc/v1/orders/$order_id/notes/$note?force=true";
		    
		    return IT_RST_Endpoints::get_instance()->make_rest_request( $url, 'DELETE' ); 
		}
		else{
		    return array( 'result' => false, 'data' => array( 'malformed request.' ) );
	    }
	}
	
	// Get order by ID
	public function get_order( $request ) {
		
		ob_clean();
	    return $this->search_orders( $request );
	}	
	
	// Search orders by customer name
	public function search_orders( $request ) {
		
	    // Get data from post
		$filter = $request->get_params();
		$orders = array();
		
		// Get single product
		if ( isset( $filter['id'] ) ) {
			$order = get_post( $filter['id'] );
			if ( $order->post_type == 'shop_order' ) {
				$orders = $this->prepare_item_for_response( $order, array() );
			}
		}
		else {	
			$query = $this->query_orders( $filter );
	
			foreach ( $query as $order ) {
				$new_orders = $this->prepare_item_for_response( $order, array() );
				$new_orders['customer'] = current( $this->get_customer( $new_orders['customer_id'], array() ) );
				if( $new_orders['customer']['id'] == 0 ){
					$order_obj = new IT_WC_Order( $order );
					$new_orders['customer'] = array(
						'id'               => 0,
						'email'            => $order_obj->get_billing_email(),
						'first_name'       => $order_obj->get_billing_first_name(),
						'last_name'        => $order_obj->get_billing_last_name(),
						'billing_address'  => array(
							'first_name' => $order_obj->get_billing_first_name(),
							'last_name'  => $order_obj->get_billing_last_name(),
							'company'    => $order_obj->get_billing_company(),
							'address_1'  => $order_obj->get_billing_address_1(),
							'address_2'  => $order_obj->get_billing_address_2(),
							'city'       => $order_obj->get_billing_city(),
							'state'      => $order_obj->get_billing_state(),
							'postcode'   => $order_obj->get_billing_postcode(),
							'country'    => $order_obj->get_billing_country(),
							'email'      => $order_obj->get_billing_email(),
							'phone'      => $order_obj->get_billing_phone(),
							),
						'shipping_address' => array(
							'first_name' => $order_obj->get_shipping_first_name(),
							'last_name'  => $order_obj->get_shipping_last_name(),
							'company'    => $order_obj->get_shipping_company(),
							'address_1'  => $order_obj->get_shipping_address_1(),
							'address_2'  => $order_obj->get_shipping_address_2(),
							'city'       => $order_obj->get_shipping_city(),
							'state'      => $order_obj->get_shipping_state(),
							'postcode'   => $order_obj->get_shipping_postcode(),
							'country'    => $order_obj->get_shipping_country(),
						),
					);
				}
				
				$orders[] = $new_orders;
			}
		}
		
		return $orders;   
	}	
	
	// Query orders by customer name
	private function query_orders( $args = array() ) {

		$offset = 0;
		$include = '';

		if( isset( $args['page'] ) ) {
			$offset = ($args['page'] - 1) * 10;
		}
		
		$meta_args = array(
			'posts_per_page'   => 10,
			'offset'           => $offset,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'post_type'  	   => 'shop_order',
			'post_status' 	   => array_keys( wc_get_order_statuses() ),
		);

		if ( !empty( $args['search'] ) ) {
			if ( strpos($args['search'], ' ' ) !== false ) {
				$search_array 	   = explode( ' ', $args['search'] );
				$first_name_search = $search_array[0];
				$last_name_search  = $search_array[1];

				$meta_args['meta_query'] = array(
				        'relation' => 'AND',
				        array(
				            'key'     => '_billing_first_name',
				            'value'   => $first_name_search,
				            'compare' => 'LIKE',
				        ),
				        array(
				            'key'     => '_billing_last_name',
				            'value'   => $last_name_search,
				            'compare' => 'LIKE',
				        ),
				);
			}
			else{
				$meta_args['meta_query'] = array(
				        'relation' => 'OR',
				        array(
				            'key'     => '_billing_first_name',
				            'value'   => $args['search'],
				            'compare' => 'LIKE',
				        ),
				        array(
				            'key'     => '_billing_last_name',
				            'value'   => $args['search'],
				            'compare' => 'LIKE',
				        ),
				);
			}
		}
		
		if (!empty( $args['status'] ) ) {
			$meta_args['post_status'] = $args['status'];
		}
		
		$orders = get_posts( $meta_args );	
		return $orders;
	}
	
	// Prepare order for rest response
	public function prepare_item_for_response( $post, $request ) {
		
		global $wpdb;

		$order = new IT_WC_Order( $post );		
		$dp    = 2;

		$data = array(
			'id'                   => $order->get_id(),
			'parent_id'            => $post->post_parent,
			'status'               => $order->get_status(),
			'order_key'            => $order->get_order_key(),
			'number'               => "{$order->get_id()}",
			'currency'             => $order->get_order_currency(),
			'version'              => $order->get_version(),
			'prices_include_tax'   => $order->get_prices_include_tax(),
			'date_created'         => $this->wc_rest_prepare_date_response( $post->post_date_gmt ),
			'date_modified'        => $this->wc_rest_prepare_date_response( $post->post_modified_gmt ),
			'customer_id'          => $order->get_user_id(),
			'total_discount'       => wc_format_decimal( $order->get_total_discount(), $dp ),
			'discount_tax'         => wc_format_decimal( $order->get_discount_tax(), $dp ),
			'total_shipping'       => wc_format_decimal( $order->get_total_shipping(), $dp ),
			'shipping_tax'         => wc_format_decimal( $order->get_shipping_tax(), $dp ),
			'cart_tax'             => wc_format_decimal( $order->get_cart_tax(), $dp ),
			'total'                => wc_format_decimal( $order->get_total(), $dp ),
			'subtotal'             => wc_format_decimal( $order->get_subtotal(), $dp ),
			'total_tax'            => wc_format_decimal( $order->get_total_tax(), $dp ),
			'billing'              => array(),
			'shipping'             => array(),
			'payment_method'       => $order->get_payment_method(),
			'payment_method_title' => $order->get_payment_method_title(),
			'transaction_id'       => $order->get_transaction_id(),
			'customer_ip_address'  => $order->get_customer_ip_address(),
			'customer_user_agent'  => $order->get_customer_user_agent(),
			'created_via'          => $order->get_created_via(),
			'customer_note'        => $order->get_customer_note(),
			'date_completed'       => $this->wc_rest_prepare_date_response( $order->get_date_completed() ),
			'date_paid'            => $order->get_date_paid(),
			'cart_hash'            => $order->get_cart_hash(),
			'line_items'           => array(),
			'tax_lines'            => array(),
			'shipping_lines'       => array(),
			'fee_lines'            => array(),
			'coupon_lines'         => array(),
			'refunds'              => array(),
		);

		// Add addresses.
		$data['billing']  = $order->get_address( 'billing' );
		$data['shipping'] = $order->get_address( 'shipping' );

		// Add line items.
		foreach ( $order->get_items() as $item_id => $item ) {
			$product      = $order->get_product_from_item( $item );
			$product_id   = 0;
			$variation_id = 0;
			$product_sku  = null;
			
			// Check if the product exists.
			if ( is_object( $product ) ) {
				$product_id   = $product->get_id();
				$variation_id = (int) $product->is_type( 'variation' ) ? $product->get_variation_id() : 0;
				$product_sku  = $product->get_sku();
			}

			$meta = new WC_Order_Item_Meta( $item, $product );
			$item_meta = array();
			$hideprefix = '_';

			foreach ( $meta->get_formatted( $hideprefix ) as $meta_key => $formatted_meta ) {
				$item_meta[] = array(
					'key'   => $formatted_meta['key'],
					'label' => $formatted_meta['label'],
					'value' => $formatted_meta['value'],
				);
			}

			$line_item = array(
				'id'           => $item_id,
				'name'         => $item['name'],
				'sku'          => $product_sku,
				'product_id'   => (int) $product_id,
				'variation_id' => (int) $variation_id,
				'quantity'     => wc_stock_amount( $item['qty'] ),
				'tax_class'    => ! empty( $item['tax_class'] ) ? $item['tax_class'] : '',
				'price'        => wc_format_decimal( $order->get_item_total( $item, false, false ), $dp ),
				'subtotal'     => wc_format_decimal( $order->get_line_subtotal( $item, false, false ), $dp ),
				'subtotal_tax' => wc_format_decimal( $item['line_subtotal_tax'], $dp ),
				'total'        => wc_format_decimal( $order->get_line_total( $item, false, false ), $dp ),
				'total_tax'    => wc_format_decimal( $item['line_tax'], $dp ),
				'taxes'        => array(),
				'meta'         => $item_meta,
			);

			$item_line_taxes = maybe_unserialize( $item['line_tax_data'] );
			if ( isset( $item_line_taxes['total'] ) ) {
				$line_tax = array();

				foreach ( $item_line_taxes['total'] as $tax_rate_id => $tax ) {
					$line_tax[ $tax_rate_id ] = array(
						'id'       => $tax_rate_id,
						'total'    => $tax,
						'subtotal' => '',
					);
				}

				foreach ( $item_line_taxes['subtotal'] as $tax_rate_id => $tax ) {
					$line_tax[ $tax_rate_id ]['subtotal'] = $tax;
				}

				$line_item['taxes'] = array_values( $line_tax );
			}

			$data['line_items'][] = $line_item;
		}
		
		if( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
			
			// Add taxes.
			foreach ( $order->get_taxes() as $key => $tax ) {
				
				$dp = 2;
								
				$tax_line = array(
					'id'                 => $key,
					'rate_code'          => $tax->get_rate_code(),
					'rate_id'            => $tax->get_rate_id(),
					'label'              => $tax->get_label(),
					'compound'           => $tax->get_compound(),
					'tax_total'          => wc_format_decimal( $tax->get_tax_total(), $dp ),
					'shipping_tax_total' => wc_format_decimal( $tax->get_shipping_tax_total(), $dp ),
				);
	
				$data['tax_lines'][] = $tax_line;
			}
		}
		else {
			
			// Add taxes.
			foreach ( $order->get_items( 'tax' ) as $key => $tax ) {
				
				$dp = 2;
				if ( !isset( $tax['rate_id'] ) ) {
					$rate_id = $key;
				}
				else {
					$rate_id = $tax['rate_id'];
				}
				
				$compound   = isset( $tax['compound'] )   		   ? (bool) $tax['compound']   			: false;
				$tax_amount = isset( $tax['tax_amount'] ) 		   ? (bool) $tax['tax_amount'] 			: 0.00;
				$ship_tax   = isset( $tax['shipping_tax_amount'] ) ? (bool) $tax['shipping_tax_amount'] : 0.00;
				
				$tax_line = array(
					'id'                 => $key,
					'rate_code'          => $tax['name'],
					'rate_id'            => $rate_id,
					'label'              => isset( $tax['label'] ) ? $tax['label'] : $tax['name'],
					'compound'           => $compound,
					'tax_total'          => wc_format_decimal( $tax_amount, $dp ),
					'shipping_tax_total' => wc_format_decimal( $ship_tax, $dp ),
				);
	
				$data['tax_lines'][] = $tax_line;
			}
		}

		// Add shipping.
		foreach ( $order->get_shipping_methods() as $shipping_item_id => $shipping_item ) {
						
			if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
				$tax_amount = $shipping_item->get_total_tax();
			}
			else {
				$tax_amount = isset( $tax['tax_amount'] ) ? $tax['tax_amount'] : 0.00;
			}
						
			$shipping_line = array(
				'id'           => $shipping_item_id,
				'method_title' => $shipping_item['name'],
				'method_id'    => $shipping_item['method_id'],
				'total'        => wc_format_decimal( $shipping_item['cost'], $dp ),
				'total_tax'    => $tax_amount,
				'taxes'        => array(),
			);

			$shipping_taxes = maybe_unserialize( $shipping_item['taxes'] );

			if ( ! empty( $shipping_taxes ) ) {

				foreach ( $shipping_taxes['total'] as $tax_rate_id => $tax ) {
					$shipping_line['taxes'][] = array(
						'id'       => $tax_rate_id,
						'total'    => $tax,
					);
				}
			}

			$data['shipping_lines'][] = $shipping_line;
		}

		// Add fees.
		foreach ( $order->get_fees() as $fee_item_id => $fee_item ) {
			$fee_line = array(
				'id'         => $fee_item_id,
				'name'       => $fee_item['name'],
				'tax_class'  => ! empty( $fee_item['tax_class'] ) ? $fee_item['tax_class'] : '',
				'tax_status' => 'taxable',
				'total'      => wc_format_decimal( $order->get_line_total( $fee_item ), $dp ),
				'total_tax'  => wc_format_decimal( $order->get_line_tax( $fee_item ), $dp ),
				'taxes'      => array(),
			);

			$fee_line_taxes = maybe_unserialize( $fee_item['line_tax_data'] );
			if ( isset( $fee_line_taxes['total'] ) ) {
				$fee_tax = array();

				foreach ( $fee_line_taxes['total'] as $tax_rate_id => $tax ) {
					$fee_tax[ $tax_rate_id ] = array(
						'id'       => $tax_rate_id,
						'total'    => $tax,
						'subtotal' => '',
					);
				}

				foreach ( $fee_line_taxes['subtotal'] as $tax_rate_id => $tax ) {
					$fee_tax[ $tax_rate_id ]['subtotal'] = $tax;
				}

				$fee_line['taxes'] = array_values( $fee_tax );
			}
			$data['fee_lines'][] = $fee_line;
		}
		
		if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
			
			// Add coupons.
			foreach ( $order->get_used_coupons() as $coupon_item_id => $coupon_item ) {
				
				$discount 	  	 = isset( $coupon_item['discount_amount'] ) 	? $coupon_item['discount_amount'] : 0.00;
				$discount_tax 	 = isset( $coupon_item['discount_amount_tax'] ) ? $coupon_item['discount_amount_tax'] : 0.00;
				$coupon_name  	 = is_array( $coupon_item )                     ? $coupon_item['name'] : $coupon_item;
				$coupon_post_obj = get_page_by_title( $coupon_name, OBJECT, 'shop_coupon' );
			    $coupon_id 		 = isset( $coupon_post_obj->ID ) ? $coupon_post_obj->ID : 0;
			    
			    if( $coupon_id ){
			    	// Save an instance of WC_Coupon object in an array(necesary to use WC_Coupon methods)
					
					$discount 	  = $order->get_discount_total();
				    $discount_tax = $order->get_discount_tax();				
				}
				
				$coupon_line  = array(
					'id'           => $coupon_id,
					'code'         => $coupon_name,
					'discount'     => wc_format_decimal( $discount, $dp ),
					'discount_tax' => wc_format_decimal( $discount_tax, $dp ),
				);
	
				$data['coupon_lines'][] = $coupon_line;
			}
		}
		else {

			// Add coupons.
			foreach ( $order->get_items( 'coupon' ) as $coupon_item_id => $coupon_item ) {
				$discount 	  = isset( $coupon_item['discount_amount'] ) 	 ? $coupon_item['discount_amount'] : 0.00;
				$discount_tax = isset( $coupon_item['discount_amount_tax'] ) ? $coupon_item['discount_amount_tax'] : 0.00;
				$coupon_line  = array(
					'id'           => $coupon_item_id,
					'code'         => $coupon_item['name'],
					'discount'     => wc_format_decimal( $discount, $dp ),
					'discount_tax' => wc_format_decimal( $discount_tax, $dp ),
				);
	
				$data['coupon_lines'][] = $coupon_line;
			}
		}

		// Add refunds.
		foreach ( $order->get_refunds() as $refund ) {
			if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
				$id_refund = $refund->get_id();
			} else {
				$id_refund = $refund->id;
			}
			$data['refunds'][] = array(
				'id'     => $id_refund,
				'refund' => $refund->get_refund_reason() ? $refund->get_refund_reason() : '',
				'total'  => '-' . wc_format_decimal( $refund->get_refund_amount(), $dp ),
			);
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $order ) );

		return apply_filters( "woocommerce_rest_prepare_{$this->post_type}", $response->data, $post, $request );
	}
	
	// Prepare date for rest response
	protected function wc_rest_prepare_date_response( $timestamp, $convert_to_utc = false ) {
		
		if ( $convert_to_utc ) {
			$timezone = new DateTimeZone( wc_timezone_string() );
		} else {
			$timezone = new DateTimeZone( 'UTC' );
		}

		try {
			if ( is_numeric( $timestamp ) ) {
				$date = new DateTime( "@{$timestamp}" );
			} else {
				$date = new DateTime( $timestamp, $timezone );
			}

			// convert to UTC by adjusting the time based on the offset of the site's timezone
			if ( $convert_to_utc ) {
				$date->modify( -1 * $date->getOffset() . ' seconds' );
			}

		} catch ( Exception $e ) {
			$date = new DateTime( '@0' );
		}

		return $date->format( 'Y-m-d\TH:i:s' );
	}
	
	// Change order status
	public function set_order_status( $request ) {
		
	    $params = $request->get_params();
	    if ( isset( $params['id'] ) && $params['id'] && isset( $params['status'] ) && $params['status'] ) {
		    $order_id 	 = $params['id'];
		    $status  	 = $params['status'];
		    $status_json = json_encode( array( 'status' => $status ) );
		    
		    $url = site_url() . "/wp-json/wc/v1/orders/$order_id";
		    
		    return IT_RST_Endpoints::get_instance()->make_rest_request( $url, 'PUT', $status_json );    
	    }
	    else {
		    return array( 'result' => false, 'data' => array( 'status is a required parameter.' ) );
	    }
	}
}
