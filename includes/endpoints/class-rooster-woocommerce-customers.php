<?php

if ( !defined( 'ABSPATH' ) ) {
    exit();
}

/**
* This class provides endpoints to access customer data
**/

class IT_RST_WooCommerce_Customers {
    
    // Singleton design pattern
    protected static $instance = NULL;
    private $server;
    
    // Method to return the singleton instance
    public static function get_instance() {
	    
        if ( null == self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
	    
	    $this->server = new WP_REST_Server();
    }    
    
    // Get all customers/Search customers by parameters
    public function get_customers( $request ) {
	    
	    $params = $request->get_params();
	    return $this->search_customers( $request );	    
	}
	
	// Get customer by id
	public function get_customer( $request ) {	
			
	    $params = $request->get_params();		
		return $this->search_customers( $request );
	}	

	// Search customers by parameters
	public function search_customers( $request ) {
		
		// Get data from post
		$filter = $request->get_params();
		
		// Get single customer
		if ( isset( $filter['id'] ) ) {
			$customers = current( $this->get_customer_data( $filter['id'] ) );
		}
		else {		
			
			// Search customers
			$query = $this->query_customers( $filter );
			$customers = array();
			foreach ( $query->get_results() as $user_id ) {
				$customers[] = current( $this->get_customer_data( $user_id ) );
			}
		}

		ob_clean();
		return $customers;
	}
	
	/**
	 * Helper method to get customer user objects
	 *
	 * Note that WP_User_Query does not have built-in pagination so limit & offset are used to provide limited
	 * pagination support
	 *
	 * The filter for role can only be a single role in a string.
	 *
	 * @since 2.3
	 * @param array $args request arguments for filtering query
	 * @return WP_User_Query
	 */

	private function query_customers( $args = array() ) {

		// default users per page
		$users_per_page = get_option( 'posts_per_page' );

		// Set base query arguments
		$query_args = array(
			'fields'  => 'ID',
			'role'    => 'customer',
			'orderby' => 'registered',
			'number'  => $users_per_page,
		);

		// Custom Role
		if ( !empty( $args['role'] ) ) {
			$query_args['role'] = $args['role'];
		}

		// Search
		if ( !empty( $args['search'] ) ) {
			if ( strpos( $args['search'], ' ' ) !== false ) {
				$search_array 	   = explode( ' ', $args['search'] );
				$first_name_search = $search_array[0];
				$last_name_search  = $search_array[1];

				$query_args['search'] 	  = '*' . esc_attr( $args['search'] ) . '*';
				$query_args['meta_query'] = array(
				        'relation' => 'AND',
				        array(
				            'key'     => 'first_name',
				            'value'   => $first_name_search,
				            'compare' => 'LIKE',
				        ),
				        array(
				            'key'     => 'last_name',
				            'value'   => $last_name_search,
				            'compare' => 'LIKE',
				        ),
				);
			}
			else if ( strpos( $args['search'], '@' ) !== false ) {
				$query_args['search'] 		  = '*' . esc_attr( $args['search'] ) . '*';
				$query_args['search_columns'] = array( 'user_email' );
			}
			else {
				$query_args['search'] 	  = '*' . esc_attr( $args['search'] ) . '*';
				$query_args['meta_query'] = array(
				        'relation' => 'OR',
				        array(
				            'key'     => 'first_name',
				            'value'   => $args['search'],
				            'compare' => 'LIKE',
				        ),
				        array(
				            'key'     => 'last_name',
				            'value'   => $args['search'],
				            'compare' => 'LIKE',
				        ),
				        array(
				            'key' => 'description',
				            'value' => $args['search'] ,
				            'compare' => 'LIKE',
				        ),
				);
			}
		}

		// Limit number of users returned
		if ( ! empty( $args['limit'] ) ) {
			if ( $args['limit'] == -1 ) {
				unset( $query_args['number'] );
			} else {
				$query_args['number'] = absint( $args['limit'] );
				$users_per_page       = absint( $args['limit'] );
			}
		} else {
			$args['limit'] = $query_args['number'];
		}

		// Page
		$page = ( isset( $args['page'] ) ) ? absint( $args['page'] ) : 1;

		// Offset
		if ( ! empty( $args['offset'] ) ) {
			$query_args['offset'] = absint( $args['offset'] );
		} else {
			$query_args['offset'] = $users_per_page * ( $page - 1 );
		}

		// Created date
		if ( ! empty( $args['created_at_min'] ) ) {
			$this->created_at_min = $this->server->parse_datetime( $args['created_at_min'] );
		}

		if ( ! empty( $args['created_at_max'] ) ) {
			$this->created_at_max = $this->server->parse_datetime( $args['created_at_max'] );
		}

		// Order (ASC or DESC, ASC by default)
		if ( ! empty( $args['order'] ) ) {
			$query_args['order'] = $args['order'];
		}

		// Orderby
		if ( ! empty( $args['orderby'] ) ) {
			$query_args['orderby'] = $args['orderby'];

			// Allow sorting by meta value
			if ( ! empty( $args['orderby_meta_key'] ) ) {
				$query_args['meta_key'] = $args['orderby_meta_key'];
			}
		}

		$query = new WP_User_Query( $query_args );

		// Helper members for pagination headers
		$query->total_pages = ( $args['limit'] == -1 ) ? 1 : ceil( $query->get_total() / $users_per_page );
		$query->page = $page;

		return $query;
	}
	
	// Get customer data by id
	public function get_customer_data( $id, $fields = null ) {
		
		global $wpdb;
		$customer    = new WC_Customer( $id );
		$statuses = array_map( 'esc_sql', wc_get_order_statuses() );
		$statuses = array_map( array( $wpdb, 'remove_placeholder_escape' ), $statuses );
		
		if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
			$_data       = $customer->get_data();
			$order_count = $customer->get_order_count();
			$total_spent = $customer->get_total_spent();
			$avatar_url  = $customer->get_avatar_url();
		}
		else {
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
				'id' 		    => $id, 
				'date_created'  => $user->user_registered, 
				'date_modified' => $user->user_registered, 
				'email' 		=> $user->user_email, 
				'first_name' 	=> $first_name, 
				'last_name' 	=> $last_name, 
				'username' 		=> $user->user_login, 
				'billing' 		=> $billing, 
				'shipping' 		=> $shipping, 
			);
			
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
		if ( $last_order instanceof WC_Order ) {
			$last_order  = new IT_WC_Order( $last_order );
		}

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
	
	// Prepare date for rest response
	public function wc_rest_prepare_date_response( $timestamp, $convert_to_utc = false ) {
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

		return $date->format( 'Y-m-d\TH:i:s\Z' );
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
}