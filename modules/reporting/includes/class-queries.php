<?php
	
if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

/**
* This class handles free module queries
**/

class RST_Reporting_Queries {
	
	// Singleton design pattern
    protected static $instance = NULL;
    
    // Method to return the singleton instance
    public static function get_instance() {
	    
        if ( null == self::$instance ) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
	
	public function __construct() {}
	
	// Calculates overview variation %
	public function variation_calc( $data ) {
		
		$variation 	 = array();
		$i 			 = 0;
								
		foreach ( $data as $value ) {
													
			array_push( $variation, $value['value'] );
		}
				
		if ( $variation ) {
			$lastTwo 	  = array_slice( $variation, -2, 2, true );
			$allButTwo 	  = array_slice( $variation, 0, count( $variation ) - 2, true );
			$lastTwoAvg   = array_sum( $lastTwo ) / 2;
			$variationAvg = ( array_sum( $allButTwo ) / count( $allButTwo ) );
			
			if ( $variationAvg ) {
				$avg 	   = $lastTwoAvg / $variationAvg;
				$avgResult = $avg - 1;
				$avgResult *= 100; 				
			}
			else {
				$avgResult = 0;
			}
		}
		else {
			$avgResult = 0;
		}
		
		return $avgResult;
	}
	/*
	 * Total Net or Total Gross
	*/
	public function totalType() {
		
		global $it_rst_reporting_settings;

		if ( $it_rst_reporting_settings['total_sales_net'] == '1' ) {
			$condition = explode( ',', $it_rst_reporting_settings['total_net'] );
			$result = '';
			if ( count($condition) == 1 ) {
				return '(total_gross-' . $condition[0] . ')';
			}
			else if ( count($condition) == 2 ) {
				return '(total_gross-' . $condition[0] . '-' . $condition[1] . ')';
			}
			else if ( count($condition) == 3 ) {
				return '(total_gross-' . $condition[0] . '-' . $condition[1] . '-' . $condition[2] . ')';
			}
			else if ( count($condition) == 4 ) {
				return 'total_net';
			}
		}
		else {
			return 'total_gross';
		}
	}
	
	/*
	 * Order Status to include in queries
	*/
	public function orderStatus() {
		
		global $it_rst_reporting_settings;
		return "'" . str_replace( ',',"','", $it_rst_reporting_settings['order_status'] ) . "'";
	}

	/*
	 * Validate the Date
	*/
	public function validateDate($date) {
		
	     return ( date( 'Y-m-d', strtotime( $date ) ) == $date );
	}
	
	/*
	 * Array Column Function for PHP < 5.2
	*/
	public function array_column( array $input, $columnKey, $indexKey = null ) {
       
       $array = array();
       foreach ( $input as $value ) {
           if ( !array_key_exists( $columnKey, $value ) ) {
               return false;
           }
           if ( is_null( $indexKey ) ) {
               $array[] = $value[ $columnKey ];
           }
           else {
               if ( !array_key_exists( $indexKey, $value ) ) {
                   return false;
               }
               if ( ! is_scalar( $value[ $indexKey ] ) ) {
                   return false;
               }
               $array[ $value[ $indexKey ] ] = $value[ $columnKey ];
           }
       }
       
       return $array;
   	}
   	
   	/*
	 * Return the difference in days between dates 
	*/
   	public function diffDays( $startDate,$endDate ) {
	   	
	   	$diff = abs( strtotime( $endDate ) - strtotime( $startDate ) );
		$days = abs( $diff / 60 / 60 / 24 );
		
		return $days;
   	}
   	
   	/*
	 * Return Array ordered and with dates with no values (=0)
	*/
	public function orderArray( $startDate,$endDate,$data ) {

		$days = $this->diffDays( $startDate,$endDate );
		
		if ($days == 0) {
			$cicle = 23 - ( 23 - date( 'H' ) );
		}
		else {
			$cicle = $days;
		} 			
				
		for ( $i = 0; $i < $cicle; $i++ ) {
			
			if ( $days == 0 ) {
				$search_for = '' . $i + 1;
				$result = !( array_search( $search_for, array_values( $this->array_column( $data,'hour' ) ) ) !== false );
			}
			else {
				$search_for = '' . date( 'Y-m-d', strtotime( $startDate . ' +' .$i .'  day' ) );
				$result = !( array_search( $search_for, array_values( $this->array_column( $data, 'date' ) ) ) !== false );
			}
			
			if ( $result ){
				if ( $days == 0 ) {
					array_push( $data, array( 'value' => 0, 'date' => $startDate, 'hour' => $i+1 ) );
				}
				else {
					array_push( $data, array( 'value' => 0, 'date' => date( 'Y-m-d', strtotime( $startDate . ' +' .$i .'  day' ) ) ) );
				}
			}
		}

		$dates = array(); 
		foreach ( $data as $value ) {  
			if ( $days == 0 )
				$dates[] = $value['hour'];
			else  
				$dates[] = $value['date'];
		}
		
		return array( 'dates' => $dates, 'allValues' => $data );
	}
	
	/*
	 * List all Recent Orders
	*/
	public function recentOrders() {
		
		global $wpdb, $it_rst_reporting_settings;
		$totalType 	 = $this->totalType();
		$orderStatus = $this->orderStatus();

		if ( $it_rst_reporting_settings['recent_orders_creation'] == '1' ) {
			$orders = $wpdb->get_results( "select order_id, date, $totalType as total, num_products, status from " . IT_RST_REPORTING_ORDERS_DATABASE . " where status != 'trash' order by date desc limit 100" );
		}
		else {
			$orders = $wpdb->get_results( "select order_id, post_modified as date, $totalType as total, num_products, status from " . $wpdb->prefix . "posts p inner join " . IT_RST_REPORTING_ORDERS_DATABASE . " o on p.id = o.order_id where status != 'trash' order by post_modified desc limit 100" );
		}
		
		$ordersA = array();
		
		foreach ( $orders as $order ) {
			array_push( $ordersA, array( 'order' => $order->order_id, 'status' => $order->status, 'date' => $order->date, 'products' => floatval( $order->num_products ), 'total' =>  strip_tags( wc_price( $order->total ) ) ) );
		}
		
		return $ordersA;
	}
	
	/*
	 * Products Avaliable
	*/
	public function productsAvailable() {
		
		global $it_rst_reporting_settings, $wpdb;
		$cache 	  = $it_rst_reporting_settings['cache_enabled'];
		if ( $cache ) {
			$products = get_transient( 'it_rst_reporting_productsAvailable' );
			if ( ! $products ) {
				$products = $wpdb->get_var( "select count(*) from " . $wpdb->posts . " p inner join " . $wpdb->postmeta . " pm on p.ID = pm.post_id where meta_key = '_stock_status' and meta_value = 'instock' and post_status = 'publish'" );
				
				set_transient( 'it_rst_reporting_productsAvailable', $products, 60 * 60 * 24 );
			}
		}
		else {
			$products = $wpdb->get_var( "select count(*) from " . $wpdb->posts . " p inner join " . $wpdb->postmeta . " pm on p.ID = pm.post_id where meta_key = '_stock_status' and meta_value = 'instock' and post_status = 'publish'" );
		}
					
		return floatval( $products );
	}
	
	/*
	 * Available Quantities
	*/
	public function availableQtt() {
	
		global $wpdb;
		$qtt = $wpdb->get_var( "select sum(meta_value) from " . $wpdb->postmeta . " where post_id in (select p.ID from " . $wpdb->posts . " p inner join " . $wpdb->postmeta . "  pm on p.ID = pm.post_id where meta_key = '_stock_status' and meta_value = 'instock' and post_status = 'publish') and meta_key = '_stock'" );	
		
		return floatval( $qtt );
	}
	
	
	/*
	 * Out Stock
	*/
	public function outStock() {
		
		global $it_rst_reporting_settings, $wpdb;
		$cache 	  = $it_rst_reporting_settings['cache_enabled'];
		
		if ( $cache ) {
			$out = get_transient( 'it_rst_reporting_outStock' );
			if ( ! $out ) {
				$out = $wpdb->get_var( "select count(*) from " . $wpdb->postmeta . " where post_id in (select p.ID from " . $wpdb->posts . " p inner join " . $wpdb->postmeta . " pm on p.ID = pm.post_id where meta_key = '_manage_stock' and meta_value = 'yes' and post_status = 'publish') and meta_key = '_stock_status' and meta_value = 'outofstock'" );	
				set_transient( 'it_rst_reporting_outStock', $out, 60 * 60 * 24 );
			}
		}
		else {
			$out = $wpdb->get_var( "select count(*) from " . $wpdb->postmeta . " where post_id in (select p.ID from " . $wpdb->posts . " p inner join " . $wpdb->postmeta . " pm on p.ID = pm.post_id where meta_key = '_manage_stock' and meta_value = 'yes' and post_status = 'publish') and meta_key = '_stock_status' and meta_value = 'outofstock'" );
		}
		return floatval( $out );
	}
				
	/*
	 * Total Customers
	*/
	public function totalCustomers() {
		
		global $it_rst_reporting_settings;
		$cache 	  = $it_rst_reporting_settings['cache_enabled'];
		
		if ( $cache ) {
			$customers = get_transient( 'it_rst_reporting_totalCustomers' );
			if ( ! $customers ) {
				$customers = count_users();
				set_transient( 'it_rst_reporting_totalCustomers', $customers, 60 * 60 * 24 );
			}
		}
		else {
			$customers = count_users();
		}
		return floatval( $customers['avail_roles']['customer'] );
	}
	
	/*
	 * Total Sales - Fast Fact
	*/
	public function totalSalesFF() {
		
		global $it_rst_reporting_settings, $wpdb;
		$cache 	  = $it_rst_reporting_settings['cache_enabled'];
		if ( $cache ) {
			$totalSales = get_transient( 'it_rst_reporting_totalSalesFF' );
			if ( ! $totalSales ) {
				$totalType 	 = $this->totalType();
				$orderStatus = $this->orderStatus();
				
				$totalSales = $wpdb->get_var( "select sum($totalType)-sum(total_refunded) from " . IT_RST_REPORTING_ORDERS_DATABASE . " where status in (" . $orderStatus  . ",'refunded') ");
				set_transient( 'it_rst_reporting_totalSalesFF', $totalSales, 60 * 60 * 24 );
			}
		}
		else {
			$totalType 	 = $this->totalType();
			$orderStatus = $this->orderStatus();
			$totalSales = $wpdb->get_var( "select sum($totalType)-sum(total_refunded) from " . IT_RST_REPORTING_ORDERS_DATABASE . " where status in (" . $orderStatus  . ",'refunded') ");
		}
					
		return  strip_tags( wc_price( $totalSales ) );
	}
	
	/*
	 * Best Customers
	*/
	public function bestCustomers( $startDate, $endDate, $params ) {
		
		if ( !( $this->validateDate( $startDate ) && $this->validateDate( $endDate ) ) ) {
			return array();
		}

		$records = isset( $params['length'] ) ? $params['length'] : 30;
		$start   = isset( $params['start'] )  ? $params['start']  : 0;

		if ( !$records ) {
			$records  = ' limit 30';
		}
		else {
			$records  = " limit $records";
		}
		
		$offset    = ( $start ) ? " offset $start" : '';
		$columns   = array( 'name', 'country', 'email', 'orders+0', 'spend' );
		$order     = ( isset( $params['order']['column'] ) && $columns[$params['order']['column']] ) ? $columns[$params['order']['column']] : 'spend';
		$order_by  = ( isset( $params['order']['dir'] ) && $params['order']['dir'] ) ? $params['order']['dir'] : 'desc';
		
		$filter    = ( isset( $params['search']['value'] ) && $params['search']['value'] ) ? " WHERE country LIKE '%%" . $params['search']['value'] . "%%' OR name LIKE '%%" . $params['search']['value'] . "%%' OR email LIKE '%%" . $params['search']['value'] . "%%' " : '';

		global $wpdb;		
		$totalType 	 = $this->totalType();
		$orderStatus = $this->orderStatus();
		
		$default_country = __( 'Unknown' , 'ithemes-sales-accelerator' );
		$query 		 = $wpdb->prepare( "select SQL_CALC_FOUND_ROWS * from ( select customer, orders, spend, email, name, if(country IS NULL, '$default_country', country) as country from ( select customer, sum(spend) as spend, count(*) as orders, (select meta_value from " . $wpdb->usermeta . " as umeta WHERE meta_key = 'billing_country' AND user_id=customer) as country, (select user_email from " . $wpdb->users . " as usertable2 WHERE usertable2.ID=customer) as email, (select display_name from " . $wpdb->users . " as usertable WHERE usertable.ID=customer) as name from (select order_id, customer, total_gross as spend from " . IT_RST_REPORTING_ORDERS_DATABASE . " WHERE (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and status in ( " . $orderStatus  . ", 'refunded') and customer <> 0 union all select e.order_id, customer, -meta2 from " . IT_RST_REPORTING_EVENTS_DATABASE . " e inner join " . IT_RST_REPORTING_ORDERS_DATABASE . " o on e.order_id = o.order_id WHERE (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and status in ( " . $orderStatus  . ", 'refunded') and customer <> 0 ) as z group by customer ) as y ) as x $filter order by $order $order_by $records $offset", $startDate, $endDate, $startDate, $endDate  );
						
		$customers 	 = $wpdb->get_results( $query );
		$customersA  = array();
		
		$total_records  = $wpdb->get_row( 'select FOUND_ROWS() as rows' );		
		$records_nbr    = ( isset( $total_records->rows ) ) ? $total_records->rows : 0;
		
		foreach ( $customers as $customer ) {
			if ( $customer->customer ) {
				array_push( $customersA, array( 'customer_id' => $customer->customer, 'name' => $customer->name, 'email' => $customer->email, 'orders' => $customer->orders, 'spend' =>  strip_tags( wc_price( $customer->spend ) ), 'country' => $customer->country ) );
			}
		}
		
		$order_key = ( array_search( $order, $columns ) !== false ) ? array_search( $order, $columns ) : 0;
		return array( 'values' => $customersA, 'total' => $records_nbr, 'order' => $order_key, 'order_by' => $order_by );
	}
	
	/*
	 * Top Countries - Table
	*/
	public function topCountries_Table( $startDate, $endDate, $params ) {

		if ( !( $this->validateDate( $startDate ) && $this->validateDate( $endDate ) ) ) {
			return array();
		}
		
		$records = isset( $params['length'] ) ? $params['length'] : 30;
		$start   = isset( $params['start'] )  ? $params['start']  : 0;

		if ( !$records ) {
			$records  = ' limit 30';
		}
		else {
			$records  = " limit $records";
		}
		
		$offset    = ( $start ) ? " offset $start" : '';
		$columns   = array( 'country', 'total', 'num_orders', 'num_products', 'coupons' );
		$order     = ( isset( $params['order']['column'] ) && $columns[$params['order']['column']] ) ? $columns[$params['order']['column']] : 'total';
		$order_by  = ( isset( $params['order']['dir'] ) && $params['order']['dir'] ) ? $params['order']['dir'] : 'desc';
		
		$filter    = ( isset( $params['search']['value'] ) && $params['search']['value'] ) ? " WHERE country LIKE '%%" . $params['search']['value'] . "%%' " : '';
				
		global $wpdb;		
		$orderStatus = $this->orderStatus();
		
		$query = $wpdb->prepare( "select SQL_CALC_FOUND_ROWS country, sum(total) as total, count(order_id) as num_orders, sum(num_products) as num_products, sum(value_discount) as coupons from (select country, total_gross as total, order_id, num_products, value_discount from " . IT_RST_REPORTING_ORDERS_DATABASE . " p WHERE (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s')  and status in ( " . $orderStatus  . ", 'refunded' )
union all 
select country, ifnull((select -sum(meta2) from " . IT_RST_REPORTING_EVENTS_DATABASE . " where DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s' and type = '2' and order_status in ( " . $orderStatus  . ", 'refunded' ) and order_id=a.order_id),0) as total_refunded,order_id, '','' from " . IT_RST_REPORTING_ORDERS_DATABASE . " a WHERE (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s')  and status in ( " . $orderStatus  . ", 'refunded' )) x $filter group by country order by $order $order_by $records $offset",$startDate, $endDate, $startDate, $endDate, $startDate, $endDate );
		
		$countries  = $wpdb->get_results( $query );			
		
		$total_records  = $wpdb->get_row( 'select FOUND_ROWS() as rows' );		
		$records_nbr    = ( isset( $total_records->rows ) ) ? $total_records->rows : 0;
		
		$countriesA = array();
		
		foreach ( $countries as $country ) {				
			array_push( $countriesA, array( 'country' => $country->country, 'total' =>  strip_tags( wc_price( $country->total ) ), 'num_orders' => $country->num_orders, 'num_products' => $country->num_products, 'coupons' =>  strip_tags( wc_price( $country->coupons ) ) ) );
		}
		
		$order_key = ( array_search( $order, $columns ) !== false ) ? array_search( $order, $columns ) : 0;
		return array( 'values' => $countriesA, 'total' => $records_nbr, 'order' => $order_key, 'order_by' => $order_by );
	}
	
	/*
	 * Total Sales By Customer
	*/
	public function totalSalesByCustomer( $startDate, $endDate, $customer ) {
		
		global $wpdb;	
		$totalType 	 = $this->totalType();
		
		if ( !( $this->validateDate( $startDate ) && $this->validateDate( $endDate ) ) ) {
			return array();
		}

		$days 		 = $this->diffDays( $startDate,$endDate );
		$orderStatus = $this->orderStatus();
					
		// What query to execute
		if ( $days == 0 ) {
			$select = "select sum(total) as total, date, hour(date) as hour from (select sum($totalType) as total, date from " . IT_RST_REPORTING_ORDERS_DATABASE . " WHERE status in (" . $orderStatus  . ",'refunded') and (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and customer = $customer group by hour(date) union all select -sum(meta2), datetime as date from " . IT_RST_REPORTING_EVENTS_DATABASE . " e INNER JOIN " . IT_RST_REPORTING_ORDERS_DATABASE . " o2 WHERE e.order_status in (" . $orderStatus  . ",'refunded') and (DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and o2.customer = $customer and type = 2 group by hour(date)) x group by hour(date) order by date asc";
		}
		else {
			$select = "select sum(total) as total, date from (select sum($totalType) as total, DATE_FORMAT(date,'%%Y-%%m-%%d') as date from " . IT_RST_REPORTING_ORDERS_DATABASE . " WHERE status in (" . $orderStatus  . ",'refunded') and (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and customer = $customer group by day(date), month(date), year(date) union all select -sum(meta2), DATE_FORMAT(datetime,'%%Y-%%m-%%d') as date from " . IT_RST_REPORTING_EVENTS_DATABASE . " e INNER JOIN " . IT_RST_REPORTING_ORDERS_DATABASE . " o2 ON e.order_id = o2.order_id WHERE e.order_status in (" . $orderStatus  . ",'refunded') and (DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and o2.customer = $customer and type = 2 group by day(date), month(date), year(date)) x group by day(date), month(date), year(date) order by date asc";
		}
			
		$query 		 = $wpdb->prepare( $select, $startDate, $endDate, $startDate, $endDate );	
		$totalSales  = $wpdb->get_results( $query );
						
		$totalSalesA = array();
		$total 	 	 = 0;
		
		foreach ( $totalSales as $sales ) {
							
			$totalDay = $sales->total;
			
			if ( $days == 0 ) {
				array_push( $totalSalesA, array( "value" => floatval($totalDay), "date" => $sales->date, "hour" => $sales->hour ) );
			}
			else {
				array_push( $totalSalesA, array( "value" => floatval($totalDay), "date" => $sales->date ) );
			}
			
			$total = $total + $totalDay;					
		}
		
		$newData = $this->orderArray( $startDate, $endDate, $totalSalesA );
							
		array_multisort( $newData['dates'], SORT_ASC, $newData['allValues'] );
		$variationAvg = $this->variation_calc( $newData['allValues'] );
		
		return array( 'variation' => round( $variationAvg, 2 ), 'total' =>  strip_tags( wc_price( $total ) ), 'lines' =>  $newData['allValues'] );
	}
	   	
	/*
	 * Total Sales
	*/
	public function totalSales( $startDate, $endDate ) {
		
		global $wpdb;	
		$totalType 	 = $this->totalType();
		
		if ( !( $this->validateDate( $startDate ) && $this->validateDate( $endDate ) ) ) {
			return array();
		}

		$days 		 = $this->diffDays( $startDate,$endDate );
		$orderStatus = $this->orderStatus();
					
		// What query to execute
		if ( $days == 0 ) {
			$select = "select sum(total) as total, date, hour(date) as hour from (select sum($totalType) as total, date from " . IT_RST_REPORTING_ORDERS_DATABASE . " WHERE status in (" . $orderStatus  . ",'refunded') and  (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') group by hour(date) union all select -sum(meta2), datetime as date from " . IT_RST_REPORTING_EVENTS_DATABASE . " e WHERE e.order_status in (" . $orderStatus  . ",'refunded') and (DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and type = 2 group by hour(date)) x group by hour(date) order by date asc";
		}
		else {
			$select = "select sum(total) as total, date from (select sum($totalType) as total, DATE_FORMAT(date,'%%Y-%%m-%%d') as date from " . IT_RST_REPORTING_ORDERS_DATABASE . " WHERE status in (" . $orderStatus  . ",'refunded') and  (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') group by day(date), month(date), year(date) union all select -sum(meta2), DATE_FORMAT(datetime,'%%Y-%%m-%%d') as date from " . IT_RST_REPORTING_EVENTS_DATABASE . " e WHERE e.order_status in (" . $orderStatus  . ",'refunded') and (DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and type = 2 group by day(date), month(date), year(date)) x group by day(date), month(date), year(date) order by date asc";
		}
			
		$query 		 = $wpdb->prepare( $select, $startDate, $endDate, $startDate, $endDate );	
		$totalSales  = $wpdb->get_results( $query );
				
		$totalSalesA = array();
		$total 	 	 = 0;
		
		foreach ( $totalSales as $sales ) {
							
			$totalDay = $sales->total;
			
			if ( $days == 0 ) {
				array_push( $totalSalesA, array( "value" => floatval($totalDay), "date" => $sales->date, "hour" => $sales->hour ) );
			}
			else {
				array_push( $totalSalesA, array( "value" => floatval($totalDay), "date" => $sales->date ) );
			}
			
			$total = $total + $totalDay;			
		}
		
		$newData = $this->orderArray( $startDate, $endDate, $totalSalesA );
										
		array_multisort( $newData['dates'], SORT_ASC, $newData['allValues'] );
		$variationAvg = $this->variation_calc( $newData['allValues'] );
		
		return array( 'variation' => round( $variationAvg, 2 ), 'total' =>  strip_tags( wc_price( $total ) ), 'lines' =>  $newData['allValues'] );
	}
	
	/*
	 * Total Spend with Coupons
	*/
	public function totalCoupons( $startDate, $endDate ) {
		
		global $wpdb;	
		
		if ( !( $this->validateDate( $startDate ) && $this->validateDate( $endDate ) ) ) {
			return array();
		}
			
		$days 		 = $this->diffDays( $startDate,$endDate );
		$orderStatus = $this->orderStatus();
		
		// What query to execute
		if ( $days == 0 ) {
			$select = "select sum(value_discount) as value_discount, DATE_FORMAT(date,'%%Y-%%m-%%d') as date, hour(date) as hour from " . IT_RST_REPORTING_ORDERS_DATABASE . " WHERE (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and coupon <> '' and status in (" . $orderStatus  . ") group by hour(date) order by hour(date) asc";
		}
		else {
			$select = "select sum(value_discount) as value_discount, DATE_FORMAT(date,'%%Y-%%m-%%d') as date from " . IT_RST_REPORTING_ORDERS_DATABASE . " WHERE (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and coupon <> '' and status in (" . $orderStatus  . ") group by day(date), month(date), year(date) order by date asc";
		}

		$query 		   = $wpdb->prepare( $select, $startDate, $endDate );							
		$totalCoupons  = $wpdb->get_results( $query );
		$totalCouponsA = array();
		$total 		   = 0;

		foreach ( $totalCoupons as $coupon ) {
			if ( $days == 0 ) {
				array_push( $totalCouponsA, array( 'value' => floatval( $coupon->value_discount ), 'date' => $coupon->date, 'hour' => $coupon->hour ) );
			}
			else {
				array_push( $totalCouponsA, array( 'value' => floatval( $coupon->value_discount ), 'date' => $coupon->date ) );
			}

			$total = $total + $coupon->value_discount;
		}
		
		$newData = $this->orderArray( $startDate, $endDate, $totalCouponsA );
			
		array_multisort( $newData['dates'], SORT_ASC, $newData['allValues'] );
		$variationAvg = $this->variation_calc( $newData['allValues'] );
		
		return array( 'variation' => round($variationAvg,2), 'total' =>  strip_tags( wc_price( $total ) ), 'lines' =>  $newData['allValues'] );
	}
							
	/*
	 * Best Category
	*/
	public function bestCategory( $startDate, $endDate ) {
		
		global $wpdb;	
		$totalType = $this->totalType();

		if ( !( $this->validateDate( $startDate ) && $this->validateDate( $endDate ) ) ) {
			return array();
		}
			
		$days 		  = $this->diffDays( $startDate,$endDate );
		$orderStatus  = $this->orderStatus();
								
		$best_category = "select (total - ifnull(refunded,0)) as total, category from (select sum(price) as total, product, category, (select sum(meta2) as total from " . IT_RST_REPORTING_EVENTS_DATABASE . " d where d.meta1=a.product and d.type=2 and d.order_status in ( " . $orderStatus  . ", 'refunded') and (DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s')  group by d.meta1 
) as refunded from " . IT_RST_REPORTING_ORDER_PRODUCTS_DATABASE . " a where status in ( " . $orderStatus  . ", 'refunded') and (DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') group by category, day(datetime), month(datetime), year(datetime) order by total desc) x order by total desc limit 1";

		$query 		  = $wpdb->prepare( $best_category, $startDate, $endDate, $startDate, $endDate );			
		$bestCategory = $wpdb->get_row( $query );
		
		if ( !empty( $bestCategory ) ) {
			
			// What query to execute
			if ( $days == 0 ) {
				$select = "select category, date, hour(date) as hour, sum(total) as total from (select category, x.date, sum(total) as total from (select category, datetime as date, sum(price) as total from " . IT_RST_REPORTING_ORDER_PRODUCTS_DATABASE . " WHERE (DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and product in (select distinct product from " . IT_RST_REPORTING_ORDER_PRODUCTS_DATABASE . " where category = '" . $bestCategory->category . "') and status in (" . $orderStatus  . ", 'refunded') group by product, hour(datetime) union all select '" . $bestCategory->category . "', datetime, -sum(meta2) from " . IT_RST_REPORTING_EVENTS_DATABASE . " where type = 2 and meta1 in (select distinct product from " . IT_RST_REPORTING_ORDER_PRODUCTS_DATABASE . " where category = '" . $bestCategory->category . "') and (DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and order_id in (select order_id from " . IT_RST_REPORTING_ORDERS_DATABASE . " where status in (" . $orderStatus  . ",'refunded') and (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s')) and meta1 <> 0 group by meta1, hour(datetime)) x group by category, hour(date)) x group by category, hour(date)";
			}
			else {
				$select = "select category, date, sum(total) as total from (select category, x.date, sum(total) as total from (select category, datetime as date, sum(price) as total from " . IT_RST_REPORTING_ORDER_PRODUCTS_DATABASE . " WHERE (DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and product in (select distinct product from " . IT_RST_REPORTING_ORDER_PRODUCTS_DATABASE . " where category = '" . $bestCategory->category . "') and status in (" . $orderStatus  . ", 'refunded') group by product, day(datetime), month(datetime), year(datetime) union all select '" . $bestCategory->category . "', datetime, -sum(meta2) from " . IT_RST_REPORTING_EVENTS_DATABASE . " where type = 2 and meta1 in (select distinct product from " . IT_RST_REPORTING_ORDER_PRODUCTS_DATABASE . " where category = '" . $bestCategory->category . "') and (DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and order_id in (select order_id from " . IT_RST_REPORTING_ORDERS_DATABASE . " where status in (" . $orderStatus  . ",'refunded') and (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s')) and meta1 <> 0 group by meta1, day(datetime), month(datetime), year(datetime)) x group by category, day(date), month(date), year(date)) x group by category, day(date), month(date), year(date)";
			}
										
			$query = $wpdb->prepare( $select, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate );	
			$bestC = $wpdb->get_results( $query );
		
		}
		else {
			$bestC = array();
		}
	
		$bestCA    = array();
		$total 	   = 0;

		foreach ( $bestC as $category ) {
			if ( $days == 0 ) {
				array_push( $bestCA, array( 'value' => floatval( $category->total ), 'date' => $category->date, 'hour' => $category->hour ) );
			}
			else {
				array_push( $bestCA, array( 'value' => floatval( $category->total ), 'date' => $category->date ) );
			}

			$total = $total + $category->total;			
		}
		
		$newData = $this->orderArray( $startDate, $endDate, $bestCA );
					
		$name = ( isset( $bestC[0]->category ) && $bestC[0]->category ) ? $bestC[0]->category : 'Uncategorized';
		array_multisort( $newData['dates'], SORT_ASC, $newData['allValues'] );
		$variationAvg = $this->variation_calc( $newData['allValues'] );
		
		return array( 'name' => $name, 'variation' => round( $variationAvg, 2 ), 'total' =>  strip_tags( wc_price( $total ) ), 'lines' =>  $newData['allValues'] );
	}
	
	public function bestCountryByProduct( $startDate, $endDate, $productID ) {
		
		global $wpdb;	
		$totalType = $this->totalType();
		
		if ( !( $this->validateDate( $startDate ) && $this->validateDate( $endDate ) ) ) {
			return array();
		}
	
		$days 		 = $this->diffDays( $startDate,$endDate );
		$orderStatus = $this->orderStatus();
					
		$best_country = "select country, sum(total) as total from (select country, price as total from " . IT_RST_REPORTING_ORDER_PRODUCTS_DATABASE . " p INNER JOIN " . IT_RST_REPORTING_ORDERS_DATABASE . " o on p.order_id = o.order_id WHERE (DATE_FORMAT(datetime, '%%Y-%%m-%%d') BETWEEN '%s' AND '%s')  and p.status in ( " . $orderStatus  . ", 'refunded' ) and product in ( $productID ) 
union all
select country, ifnull((select -sum(meta2) from " . IT_RST_REPORTING_EVENTS_DATABASE . " where DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s' and type = '2' and order_status in ( " . $orderStatus  . ", 'refunded' ) and meta1=a.product),0) as total_refunded from " . IT_RST_REPORTING_ORDER_PRODUCTS_DATABASE . " a INNER JOIN " . IT_RST_REPORTING_ORDERS_DATABASE . " o2 on a.order_id = o2.order_id WHERE (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and a.status in ( " . $orderStatus  . ", 'refunded' ) and a.product in ( $productID ) ) as x group by country order by total desc limit 1";

		$query 		 = $wpdb->prepare( $best_country, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate );
		
		$bestCountry = $wpdb->get_row( $query );
														
		if ( !empty( $bestCountry ) ) {
		
			// What query to execute
			if ( $days == 0 ) {
				
				$select = "select sum(total) as total, date, hour(date) as hour from (select price as total, datetime as date from " . IT_RST_REPORTING_ORDER_PRODUCTS_DATABASE . " p INNER JOIN " . IT_RST_REPORTING_ORDERS_DATABASE . " o on p.order_id = o.order_id WHERE (DATE_FORMAT(datetime, '%%Y-%%m-%%d') BETWEEN '%s' AND '%s')  and p.status in ( " . $orderStatus  . ", 'refunded' ) and product in( $productID ) and country = '{$bestCountry->country}'
				union all
				select ifnull((select -sum(meta2) from " . IT_RST_REPORTING_EVENTS_DATABASE . " where DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s' and type = '2' and order_status in ( " . $orderStatus  . ", 'refunded' ) and meta1=a.product),0) as total_refunded, a.datetime from " . IT_RST_REPORTING_ORDER_PRODUCTS_DATABASE . " a INNER JOIN " . IT_RST_REPORTING_ORDERS_DATABASE . " o2 on a.order_id = o2.order_id WHERE (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and a.status in ( " . $orderStatus  . ", 'refunded' ) and a.product in( $productID ) and o2.country = '{$bestCountry->country}') as x group by hour(date)";
		    }
			else {					
				$select = "select sum(total) as total, date from (select price as total, datetime as date from " . IT_RST_REPORTING_ORDER_PRODUCTS_DATABASE . " p INNER JOIN " . IT_RST_REPORTING_ORDERS_DATABASE . " o on p.order_id = o.order_id WHERE (DATE_FORMAT(datetime, '%%Y-%%m-%%d') BETWEEN '%s' AND '%s')  and p.status in ( " . $orderStatus  . ", 'refunded' ) and product in( $productID ) and country = '{$bestCountry->country}'
union all
select ifnull((select -sum(meta2) from " . IT_RST_REPORTING_EVENTS_DATABASE . " where DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s' and type = '2' and order_status in ( " . $orderStatus  . ", 'refunded' ) and meta1=a.product),0) as total_refunded, a.datetime from " . IT_RST_REPORTING_ORDER_PRODUCTS_DATABASE . " a INNER JOIN " . IT_RST_REPORTING_ORDERS_DATABASE . " o2 on a.order_id = o2.order_id WHERE (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and a.status in ( " . $orderStatus  . ", 'refunded' ) and a.product in( $productID ) and o2.country = '{$bestCountry->country}') as x group by day(date), month(date), year(date)";
			}
			
			$query  = $wpdb->prepare( $select, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate );
			$bestCO = $wpdb->get_results( $query );
			
		}
		else {
			$bestCO = array();
		}
							
		$bestCOA 	= array();
		$total 		= 0;

		foreach ( $bestCO as $country ) {
			if ( $days == 0 ) {
				array_push( $bestCOA, array( 'value' => floatval( $country->total ), 'date' => $country->date, 'hour' => $country->hour ) );
			}
			else {
				array_push( $bestCOA, array( 'value' => floatval( $country->total ), 'date' => $country->date ) );
			}

			$total = $total + $country->total;			
		}
		
		$newData = $this->orderArray( $startDate, $endDate, $bestCOA );
					
		$name = isset( $bestCountry->country ) ? $bestCountry->country : '';
		array_multisort( $newData['dates'], SORT_ASC, $newData['allValues'] );
		$variationAvg = $this->variation_calc( $newData['allValues'] );
		
		return array( 'name' => $name, 'variation' => round( $variationAvg, 2 ), 'total' =>  strip_tags( wc_price( $total ) ), 'lines' =>  $newData['allValues'] );
	}
	
	/*
	 * Best Country
	*/
	public function bestCountry( $startDate, $endDate ) {
		
		global $wpdb;	
		$totalType = $this->totalType();
		
		if ( !( $this->validateDate( $startDate ) && $this->validateDate( $endDate ) ) ) {
			return array();
		}
	
		$days 		 = $this->diffDays( $startDate,$endDate );
		$orderStatus = $this->orderStatus();
					
		$best_country = "select country, sum(total) as total from (select country, total_gross as total from " . IT_RST_REPORTING_ORDERS_DATABASE . " p WHERE (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s')  and status in ( " . $orderStatus  . ", 'refunded' )
union all 
select country, ifnull((select -sum(meta2) from " . IT_RST_REPORTING_EVENTS_DATABASE . " where DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s' and type = '2' and order_status in ( " . $orderStatus  . ", 'refunded' ) and order_id=a.order_id),0) as total_refunded from "  .IT_RST_REPORTING_ORDERS_DATABASE . " a WHERE (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s')  and status in ( " . $orderStatus  . ", 'refunded' )) x group by country order by total desc limit 1";

		$query 		 = $wpdb->prepare( $best_country, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate );
		
		$bestCountry = $wpdb->get_row( $query );
								
		if ( !empty( $bestCountry ) ) {
		
			// What query to execute
			if ( $days == 0 ) {
				$select = "select sum(total) as total, date, hour(date) as hour from (select total_gross as total, date from " . IT_RST_REPORTING_ORDERS_DATABASE . " p WHERE (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s')  and status in ( " . $orderStatus  . ", 'refunded' ) and country = '{$bestCountry->country}'
union all 
select ifnull((select -sum(meta2) from " . IT_RST_REPORTING_EVENTS_DATABASE . " where DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s' and type = '2' and order_status in ( " . $orderStatus  . ", 'refunded' ) and order_id=a.order_id),0) as total_refunded, a.date from " . IT_RST_REPORTING_ORDERS_DATABASE . " a WHERE (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s')  and status in ( " . $orderStatus  . ", 'refunded' ) and country = '{$bestCountry->country}') x group by hour(date)";
		    }
			else {					
				$select = "select sum(total) as total, date from (select total_gross as total, date from " . IT_RST_REPORTING_ORDERS_DATABASE . " p WHERE (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s')  and status in ( " . $orderStatus  . ", 'refunded' ) and country = '{$bestCountry->country}'
union all 
select ifnull((select -sum(meta2) from " . IT_RST_REPORTING_EVENTS_DATABASE . " where DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s' and type = '2' and order_status in ( " . $orderStatus  . ", 'refunded' ) and order_id=a.order_id),0) as total_refunded, a.date from " . IT_RST_REPORTING_ORDERS_DATABASE . " a WHERE (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s')  and status in ( " . $orderStatus  . ", 'refunded' ) and country = '{$bestCountry->country}') x group by day(date), month(date), year(date)";
			}
			
			$query  = $wpdb->prepare( $select, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate );
			$bestCO = $wpdb->get_results( $query );
			
		}
		else {
			$bestCO = array();
		}
							
		$bestCOA 	= array();
		$total 		= 0;

		foreach ( $bestCO as $country ) {
			if ( $days == 0 ) {
				array_push( $bestCOA, array( 'value' => floatval( $country->total ), 'date' => $country->date, 'hour' => $country->hour ) );
			}
			else {
				array_push( $bestCOA, array( 'value' => floatval( $country->total ), 'date' => $country->date ) );
			}

			$total = $total + $country->total;			
		}
		
		$newData = $this->orderArray( $startDate, $endDate, $bestCOA );
					
		$name = isset( $bestCountry->country ) ? $bestCountry->country : '';
		array_multisort( $newData['dates'], SORT_ASC, $newData['allValues'] );
		$variationAvg = $this->variation_calc( $newData['allValues'] );
		
		return array( 'name' => $name, 'variation' => round( $variationAvg, 2 ), 'total' =>  strip_tags( wc_price( $total ) ), 'lines' =>  $newData['allValues'] );
	}
	
	/*
	 * Best Shipping Method
	*/
	public function bestShipping( $startDate, $endDate ) {
		
		global $wpdb;	
		$totalType 	 = $this->totalType();
		
		if ( !( $this->validateDate( $startDate ) && $this->validateDate( $endDate ) ) ) {
			return array();
		}
			
		$days 		 = $this->diffDays( $startDate,$endDate );
		$orderStatus = $this->orderStatus();
		
		$select = "select shipping, sum(total) as total from (select shipping, sum(total_gross) as total from " . IT_RST_REPORTING_ORDERS_DATABASE . " WHERE (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and status in ( " . $orderStatus  . ", 'refunded' ) group by shipping 
UNION ALL
select (select shipping from " . IT_RST_REPORTING_ORDERS_DATABASE . " where order_id=a.order_id) as shipping_method, -sum(meta2) as total_refunded from " . IT_RST_REPORTING_EVENTS_DATABASE . " a WHERE (DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and order_status in ( " . $orderStatus  . ", 'refunded' )  group by shipping_method) x group by shipping order by total desc limit 1";
		
		$query 		  = $wpdb->prepare( $select, $startDate, $endDate, $startDate, $endDate );
		$bestShipping = $wpdb->get_row( $query );
		
		if ( !empty( $bestShipping ) ) {
			// What query to execute
			if ( $days == 0 ) {
				$selectSM = "select sum(total) as total, hour_date, shipping from (select shipping, sum(total_gross) as total, hour(date) as hour_date from " . IT_RST_REPORTING_ORDERS_DATABASE . " WHERE (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and status in ( " . $orderStatus  . ", 'refunded' ) and shipping='{$bestShipping->shipping}' group by hour(date)
UNION ALL
select '{$bestShipping->shipping}' as shipping_method, -sum(meta2) as total_refunded, hour(datetime) as hour_date from " . IT_RST_REPORTING_EVENTS_DATABASE . " a WHERE (DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and order_status in ( " . $orderStatus  . ", 'refunded' )  and order_id in (select order_id from " . IT_RST_REPORTING_ORDERS_DATABASE . " where shipping='{$bestShipping->shipping}')  group by hour(datetime))x group by hour_date order by hour_date asc";
			}
			else {
				$selectSM = "select sum(total) as total, date, shipping from (select shipping, sum(total_gross) as total, DATE_FORMAT(date,'%%Y-%%m-%%d') as date from " . IT_RST_REPORTING_ORDERS_DATABASE . " WHERE (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and status in ( " . $orderStatus  . ", 'refunded' ) and shipping='{$bestShipping->shipping}' group by day(date), month(date), year(date)
UNION ALL
select '{$bestShipping->shipping}' as shipping_method, -sum(meta2) as total_refunded, DATE_FORMAT(datetime,'%%Y-%%m-%%d') from " . IT_RST_REPORTING_EVENTS_DATABASE . " a WHERE (DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and order_status in ( " . $orderStatus  . ", 'refunded' ) and order_id in (select order_id from " . IT_RST_REPORTING_ORDERS_DATABASE . " where shipping='{$bestShipping->shipping}') group by day(datetime), month(datetime), year(datetime))x group by date order by date asc";
			}
			
			$querySM  = $wpdb->prepare( $selectSM, $startDate, $endDate, $startDate, $endDate );
			$bestSM = $wpdb->get_results( $querySM );
		}
		else {
			$bestSM = array();
		}
	
		$bestSMA 	= array();
		$total 		= 0;

		foreach ( $bestSM as $shipping ) {
			if ( $days == 0 ) {
				array_push( $bestSMA, array( 'value' => floatval( $shipping->total ), 'date' => $shipping->date, 'hour' => $shipping->hour ) );
			}
			else {
				array_push( $bestSMA, array( 'value' => floatval( $shipping->total ), 'date' => $shipping->date ) );
			}

			$total = $total + $shipping->total;
		}
		
		$newData   = $this->orderArray( $startDate, $endDate, $bestSMA );
				
		$name 	   = isset( $bestSM[0]->shipping ) ? $bestSM[0]->shipping : '';
		array_multisort( $newData['dates'], SORT_ASC, $newData['allValues'] );
		$variationAvg = $this->variation_calc( $newData['allValues'] );
		
		return array( 'name' => $name, 'variation' => round( $variationAvg, 2 ), 'total' =>  strip_tags( wc_price( $total ) ), 'lines' =>  $newData['allValues'] );
	}
	
	/*
	 * Total Refunds by Customer
	*/
	public function totalRefundsByCustomer( $startDate, $endDate, $customerID ) {
		
		global $wpdb;	
		
		if ( !( $this->validateDate( $startDate ) && $this->validateDate( $endDate ) ) ) {
			return array();
		}
			
		$days 		 = $this->diffDays( $startDate, $endDate );
		$orderStatus = $this->orderStatus();
		
		// What query to execute
		if ( $days == 0 ) {
			$select = "select sum(meta2) as total, datetime as date, hour(datetime) as hour from " . IT_RST_REPORTING_EVENTS_DATABASE . " e inner join " . IT_RST_REPORTING_ORDERS_DATABASE . " o on e.order_id = o.order_id where type = 2 and (DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and status in (" . $orderStatus  . ",'refunded') and o.customer = $customerID group by hour(datetime) order by datetime asc";
		}
		else {
			$select = "select sum(meta2) as total, DATE_FORMAT(datetime,'%%Y-%%m-%%d') as date from " . IT_RST_REPORTING_EVENTS_DATABASE . " e inner join " . IT_RST_REPORTING_ORDERS_DATABASE . " o on e.order_id = o.order_id where type = 2 and (DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and status in (" . $orderStatus  . ",'refunded') and o.customer = $customerID group by day(datetime), month(datetime), year(datetime) order by datetime asc";
		}

		$query 		   = $wpdb->prepare( $select, $startDate, $endDate );	
		$totalRefunds  = $wpdb->get_results( $query );
		
		$totalRefundsA = array();
		$total 		   = 0;

		foreach ( $totalRefunds as $refund ) {
			if ( $days == 0 ) {
				array_push( $totalRefundsA, array( 'value' => floatval( $refund->total ), 'date' => $refund->date, 'hour' => $refund->hour ) );
			}
			else {
				array_push( $totalRefundsA, array( 'value' => floatval( $refund->total ), 'date' => $refund->date ) );
			}

			$total = $total + $refund->total;			
		}
		
		$newData = $this->orderArray( $startDate, $endDate, $totalRefundsA );
					
		array_multisort( $newData['dates'], SORT_ASC, $newData['allValues'] );
		$variationAvg = $this->variation_calc( $newData['allValues'] );
		
		return array( 'variation' => round($variationAvg,2), 'total' =>  strip_tags( wc_price( $total ) ), 'lines' =>  $newData['allValues'] );
	}
	
	/*
	 * Total Refunds by Product
	*/
	public function totalRefundsByProduct( $startDate, $endDate, $productID ) {
		
		global $wpdb;	
		
		if ( !( $this->validateDate( $startDate ) && $this->validateDate( $endDate ) ) ) {
			return array();
		}
			
		$days 		 = $this->diffDays( $startDate, $endDate );
		$orderStatus = $this->orderStatus();
		
		// What query to execute
		if ( $days == 0 ) {
			$select = "select sum(meta2) as total, datetime as date, hour(datetime) as hour from " . IT_RST_REPORTING_EVENTS_DATABASE . " e inner join " . IT_RST_REPORTING_ORDERS_DATABASE . " o on e.order_id = o.order_id where type = 2 and (DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and status in (" . $orderStatus  . ",'refunded') and meta1 in ($productID) group by hour(datetime) order by datetime asc";
		}
		else {
			$select = "select sum(meta2) as total, DATE_FORMAT(datetime,'%%Y-%%m-%%d') as date from " . IT_RST_REPORTING_EVENTS_DATABASE . " e inner join " . IT_RST_REPORTING_ORDERS_DATABASE . " o on e.order_id = o.order_id where type = 2 and (DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and status in (" . $orderStatus  . ",'refunded') and meta1 in ($productID) group by day(datetime), month(datetime), year(datetime) order by datetime asc";
		}

		$query 		   = $wpdb->prepare( $select, $startDate, $endDate );	
		$totalRefunds  = $wpdb->get_results( $query );
		
		$totalRefundsA = array();
		$total 		   = 0;

		foreach ( $totalRefunds as $refund ) {
			if ( $days == 0 ) {
				array_push( $totalRefundsA, array( 'value' => floatval( $refund->total ), 'date' => $refund->date, 'hour' => $refund->hour ) );
			}
			else {
				array_push( $totalRefundsA, array( 'value' => floatval( $refund->total ), 'date' => $refund->date ) );
			}

			$total = $total + $refund->total;
		}
		
		$newData = $this->orderArray( $startDate, $endDate, $totalRefundsA );
					
		array_multisort( $newData['dates'], SORT_ASC, $newData['allValues'] );
		$variationAvg = $this->variation_calc( $newData['allValues'] );
		
		return array( 'variation' => round($variationAvg,2), 'total' =>  strip_tags( wc_price( $total ) ), 'lines' =>  $newData['allValues'] );
	}
	
	/*
	 * Total Refunds
	*/
	public function totalRefunds( $startDate, $endDate ) {
		
		global $wpdb;	
		
		if ( !( $this->validateDate( $startDate ) && $this->validateDate( $endDate ) ) ) {
			return array();
		}
			
		$days 		 = $this->diffDays( $startDate, $endDate );
		$orderStatus = $this->orderStatus();
		
		// What query to execute
		if ( $days == 0 ) {
			$select = "select sum(meta2) as total, datetime as date, hour(datetime) as hour from " . IT_RST_REPORTING_EVENTS_DATABASE . " e inner join " . IT_RST_REPORTING_ORDERS_DATABASE . " o on e.order_id = o.order_id where type = 2 and (DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and status in (" . $orderStatus  . ",'refunded') group by hour(datetime) order by datetime asc";
		}
		else {
			$select = "select sum(meta2) as total, DATE_FORMAT(datetime,'%%Y-%%m-%%d') as date from " . IT_RST_REPORTING_EVENTS_DATABASE . " e inner join " . IT_RST_REPORTING_ORDERS_DATABASE . " o on e.order_id = o.order_id where type = 2 and (DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and status in (" . $orderStatus  . ",'refunded') group by day(datetime), month(datetime), year(datetime) order by datetime asc";
		}

		$query 		   = $wpdb->prepare( $select, $startDate, $endDate );	
		$totalRefunds  = $wpdb->get_results( $query );
		$totalRefundsA = array();
		$total 		   = 0;

		foreach ( $totalRefunds as $refund ) {
			if ( $days == 0 ) {
				array_push( $totalRefundsA, array( 'value' => floatval( $refund->total ), 'date' => $refund->date, 'hour' => $refund->hour ) );
			}
			else {
				array_push( $totalRefundsA, array( 'value' => floatval( $refund->total ), 'date' => $refund->date ) );
			}

			$total = $total + $refund->total;			
		}
		
		$newData = $this->orderArray( $startDate, $endDate, $totalRefundsA );
					
		array_multisort( $newData['dates'], SORT_ASC, $newData['allValues'] );
		$variationAvg = $this->variation_calc( $newData['allValues'] );
		
		return array( 'variation' => round($variationAvg,2), 'total' =>  strip_tags( wc_price( $total ) ), 'lines' =>  $newData['allValues'] );
	}
	
	/*
	 * Customers vs Guests
	*/
	public function customersvsguest( $startDate, $endDate ) {
		
		global $wpdb;	
		
		if ( !( $this->validateDate( $startDate ) && $this->validateDate( $endDate ) ) ) {
			return array();
		}
			
		$days 	  	 = $this->diffDays( $startDate,$endDate );
		$totalType 	 = $this->totalType();
		$orderStatus = $this->orderStatus();
		
		// What query to execute
		$select = "select (select sum(total) from (select sum(total_gross) as total from " . IT_RST_REPORTING_ORDERS_DATABASE . " where customer <> 0 and status in ( " . $orderStatus  . ", 'refunded' ) and (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') union all select -sum(meta2) from " . IT_RST_REPORTING_EVENTS_DATABASE . " e inner join " . IT_RST_REPORTING_ORDERS_DATABASE . " o on e.order_id = o.order_id where customer <> 0 and order_status in ( " . $orderStatus  . ", 'refunded' ) and (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s')) x) as customers, (select sum(total) from (select sum(total_gross) as total from " . IT_RST_REPORTING_ORDERS_DATABASE . " where customer = 0 and status in ( " . $orderStatus  . ", 'refunded' ) and (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') union all select -sum(meta2) from " . IT_RST_REPORTING_EVENTS_DATABASE . " e inner join " . IT_RST_REPORTING_ORDERS_DATABASE . " o on e.order_id = o.order_id where customer = 0 and order_status in ( " . $orderStatus  . ", 'refunded' ) and (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s')) x) as guests";
		
		$query 		   = $wpdb->prepare( $select, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate );
		$cg 		   = $wpdb->get_results( $query );
		$tot_customers = ( isset( $cg[0]->customers) && $cg[0]->customers > 0 ) ? $cg[0]->customers : 0;
		$tot_guests    = ( isset( $cg[0]->guests )   && $cg[0]->guests > 0 )    ? $cg[0]->guests : 0;
		$total 	  	   = $tot_customers + $tot_guests;
		$customers     = ( $tot_customers && $total ) ? round( ( $cg[0]->customers / $total ) * 100,0 ) : 0;
		$guests    	   = ( $tot_guests    && $total ) ? round( ( $cg[0]->guests / $total ) * 100,0 ) : 0;
		
		return array( 'customers_per' => floatval( $customers ), 'guests_per' => floatval( $guests ), 'customers_val' =>  strip_tags( wc_price( $cg[0]->customers ) ), 'guests_val' =>  strip_tags( wc_price( $cg[0]->guests ) ) );
	}
	
	/*
	 * Shipping Method
	*/
	public function shippingMethod( $startDate, $endDate ) {
		
		global $wpdb;	
		
		if ( !( $this->validateDate( $startDate ) && $this->validateDate( $endDate ) ) ) {
			return array();
		}
			
		$totalType 	 = $this->totalType();
		$orderStatus = $this->orderStatus();
		
		$select = "select shipping, sum(total) as total from (select shipping, sum(total_gross) as total from " . IT_RST_REPORTING_ORDERS_DATABASE . " WHERE (DATE_FORMAT(date,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and status in ( " . $orderStatus  . ", 'refunded' ) group by shipping 
UNION ALL
select (select shipping from " . IT_RST_REPORTING_ORDERS_DATABASE . " where order_id=a.order_id) as shipping_method, -sum(meta2) as total_refunded from " . IT_RST_REPORTING_EVENTS_DATABASE . " a WHERE (DATE_FORMAT(datetime,'%%Y-%%m-%%d') BETWEEN '%s' AND '%s') and order_status in ( " . $orderStatus  . ", 'refunded' )  group by shipping_method) x group by shipping order by total desc limit 15";
		
		$query 		= $wpdb->prepare( $select, $startDate, $endDate, $startDate, $endDate );			
		$shippings 	= $wpdb->get_results( $query );
		$shippingsA = array();
		
		foreach ( $shippings as $item ) {	
			$method = ( $item->shipping ? $item->shipping : 'Other' );
			array_push( $shippingsA, array( 'name' => $method, 'total_app' =>  strip_tags( wc_price( $item->total ) ), 'total' => round( floatval( $item->total ), 2 ) ) );
		}
		
		return $shippingsA;
	}
}
