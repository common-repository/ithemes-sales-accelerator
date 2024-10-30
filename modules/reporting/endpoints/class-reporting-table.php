<?php
	
if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

/**
* This class handles free module tables
**/

class IT_RST_RP_Table {
    
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
    
    // Best Customers
    public function table_bestCustomers( $mode, $dateStart, $dateEnd, $body, $params ) {

		$content = RST_Reporting_Queries::get_instance()->bestCustomers( $dateStart, $dateEnd, $params );
		$values   = isset( $content['values'] )   ? $content['values']    : array();
		$total    = isset( $content['total'] )    ? $content['total']     : 0;
		$order    = isset( $content['order'] )    ? $content['order']     : 4;
		$order_by = isset( $content['order_by'] ) ? $content['order_by']  : 'desc';
		
		if ( $mode == 1 ) {
			$html = '<table id="" class="it_rst_dataTable" cellspacing="0" width="100%">
	            		<thead>
	            			<tr class="it_rst_dataTable_head">
	            				<th class="sm-show"><span style="padding-left: 25px;"></span>' . __( 'Name', 'ithemes-sales-accelerator' ) .'</th>
	            				<th class="sm-hide s-hide">' . __( 'Country', 'ithemes-sales-accelerator' ) .'</th>
	            				<th class="s-hide sm-show">' . __( 'Email', 'ithemes-sales-accelerator' ) .'</th>
								<th class="sm-hide s-hide">' . __( 'Orders', 'ithemes-sales-accelerator' ) .'</th>
								<th class="sm-show">' 		 . __( 'Spend', 'ithemes-sales-accelerator' ) .'</th>
							</tr>
	            		</thead>
	            		<tfoot>
				            <tr>
				                <th class="sm-show">' 		 . __( 'Name', 'ithemes-sales-accelerator' ) .'</th>
				                <th class="sm-hide s-hide">' . __( 'Country', 'ithemes-sales-accelerator' ) .'</th>
				                <th class="s-hide sm-show">' . __( 'Email', 'ithemes-sales-accelerator' ) .'</th>
				                <th class="sm-hide s-hide">' . __( 'Orders', 'ithemes-sales-accelerator' ) .'</th>
				                <th class="sm-show">' 		 . __( 'Spend', 'ithemes-sales-accelerator' ) .'</th>
				            </tr>
				        </tfoot>
	            		<tbody>';
	        $i = 1;
	        if ( $body ) {
		        foreach ( $content as $item ) {
	
			        $html .='	<tr>
		            				<td class="sm-show"><a href="' . get_edit_user_link( $item['customer_id'] ) . '"><b>' . $item['name'] . '</b></a></td>
		            				<td class="sm-hide s-hide">'   . $item['country'] . '</td>
		            				<td class="s-hide sm-show">'   . $item['email'] . '</td>
		            				<td class="sm-hide s-hide">'   . $item['orders'] . '</td>
		            				<td class="sm-show">' 		   . $item['spend'] . '</td>
		            			</tr>';
		            $i++;
		            if ( $i == 40 ) {
		            	break;
					}
		        }
	        }
            $html.= '</tbody></table>';
		
			return array( 'html' => $html, 'order' => $order, 'order_type' => $order_by );
		}
		else if ( $mode == 2 ) {
			$data = array();
			foreach ( $values as $item ) { 
				$data[] = array( '<a href="' . get_edit_user_link( $item['customer_id'] ) . '"><b>' . $item['name'] . '</b></a>', $item['country'], $item['email'], $item['orders'], $item['spend'] );
			}
			
			return array( 'data' => $data, 'total' => $total );
		}
		else {
			return array( 'columns' => array( 'name' => 'Name', 'country' => 'Country', 'email' => 'Email', 'orders' => 'Orders', 'spend' => 'Spend' ), 'values' => $content, 'order' => $order, 'order_type' => $order_by );
		}
    }    
      
    // Top Countries
    public function table_topCountries( $mode, $dateStart, $dateEnd, $body, $params ) {
	    
		$content = RST_Reporting_Queries::get_instance()->topCountries_Table( $dateStart, $dateEnd, $params );
		$values   = isset( $content['values'] )   ? $content['values']    : array();
		$total    = isset( $content['total'] )    ? $content['total']     : 0;
		$order    = isset( $content['order'] )    ? $content['order']     : 1;
		$order_by = isset( $content['order_by'] ) ? $content['order_by']  : 'desc';

		if ( $mode == 1 ) {
			$html = '<table id="" class="it_rst_dataTable" cellspacing="0" width="100%">
	            		<thead>
	            			<tr class="it_rst_dataTable_head">
	            				<th class="sm-show"><span style="padding-left: 25px;"></span>' . __( 'Country', 'ithemes-sales-accelerator' ) .'</th>
	            				<th class="sm-show">' 		 . __( 'Total', 'ithemes-sales-accelerator' ) .'</th>
	            				<th class="s-hide sm-show">' . __( 'Num. Orders', 'ithemes-sales-accelerator' ) .'</th>
								<th class="sm-hide s-hide">' . __( 'Num. Products', 'ithemes-sales-accelerator' ) .'</th>
								<th class="sm-hide s-hide">' . __( 'Total Coupons', 'ithemes-sales-accelerator' ) .'</th>
							</tr>
	            		</thead>
	            		<tfoot>
				            <tr>
				                <th class="sm-show"><span style="padding-left: 25px;"></span>' . __( 'Country', 'ithemes-sales-accelerator' ) .'</th>
	            				<th class="sm-show">' 		 . __( 'Total', 'ithemes-sales-accelerator' ) .'</th>
	            				<th class="s-hide sm-show">' . __( 'Num. Orders', 'ithemes-sales-accelerator' ) .'</th>
								<th class="sm-hide s-hide">' . __( 'Num. Products', 'ithemes-sales-accelerator' ) .'</th>
								<th class="sm-hide s-hide">' . __( 'Total Coupons', 'ithemes-sales-accelerator' ) .'</th>
				            </tr>
				        </tfoot>
	            		<tbody>';
	        $i = 1;
	        if ( $body ) {
		        foreach ( $values as $item ) {
	
			        $html .='	<tr>
		            				<td class="sm-show"><b>' 	 . $item['country'] . '</b></td>
		            				<td class="sm-hide s-hide">' . $item['total'] . '</td>
		            				<td class="s-hide sm-show">' . $item['num_orders'] . '</td>
		            				<td class="sm-hide s-hide">' . $item['num_products'] . '</td>
		            				<td class="sm-show">' 		 . $item['coupons'] . '</td>
		            			</tr>';
		            $i++;
		            if ( $i == 40 ) {
		            	break;
		            }
		        }
	        }
	        
            $html .= '</tbody></table>';
			
			return array( 'html' => $html, 'order' => $order, 'order_type' => $order_by );
		}
		else if ( $mode == 2 ) {
			$data = array();
			foreach ( $values as $item ) { 
				$data[] = array( $item['country'], $item['total'], $item['num_orders'], $item['num_products'], $item['coupons'] );
			}
			
			return array( 'data' => $data, 'total' => $total );
		}
		else {
			return array( 'columns' => array( 'country' => 'Country', 'total' => 'Total', 'num_orders' => 'Num. Orders', 'num_products' => 'Num. Products', 'coupons' => 'Total Coupons' ), 'values' => $values, 'order' => $order, 'order_type' => $order_by );
		}
    }
        
    // Send result by JSON Endpoint
    public function contentTable_json_endpoint( $request ) {
	    
	    $params = $request->get_params();
	    
	    // Get params
	    $renderHtml	= isset( $params['renderHtml'] ) ? $params['renderHtml'] : '';
	    $tableName 	= isset( $params['slug'] ) 		 ? $params['slug'] 		 : '';
	    $dateStart 	= isset( $params['dateStart'] )  ? $params['dateStart']  : date( 'Y-m-d', ( strtotime ( '-30 days' ) ) );
	    $dateEnd 	= isset( $params['dateEnd'] ) 	 ? $params['dateEnd'] 	 : date( 'Y-m-d' );
	    $draw 		= isset( $params['draw'] ) 	 	 ? $params['draw'] 		 : 0;
	    $body 	    = isset( $params['body'] ) 	     ? $params['body'] 	     : 1;
	    $data 		= array();
		
		switch ( $tableName ) {
		    // Best Customers
		    case 'best_customers':
		    	if ( $draw ) {
			    	$data = $this->table_bestCustomers( 2, $dateStart, $dateEnd, $body, $params );
		    	}
		    	else if ( $renderHtml == 1 ) {
		    		$data = $this->table_bestCustomers( 1, $dateStart, $dateEnd, $body, $params );
		    	}
		    	else {
					$data = $this->table_bestCustomers( '', $dateStart, $dateEnd, $body, $params );
				}
			break;
						
			// Top Countries
		    case 'top_countries':
			    if ( $draw ) {
			    	$data = $this->table_topCountries( 2, $dateStart, $dateEnd, $body, $params );
		    	}
		    	else if ( $renderHtml == 1 ) {
		    		$data = $this->table_topCountries( 1, $dateStart, $dateEnd, $body, $params );
		    	}
		    	else {
					$data = $this->table_topCountries( '', $dateStart, $dateEnd, $body, $params );
				}
			break;			
		}
		if ( $draw ) {
		    $return = array( 'draw' => $draw, 'recordsTotal' => $data['total'], 'recordsFiltered' => $data['total'], 'data' => $data['data'] );
		    echo json_encode( $return );
		    die();
		}
		else {
	    	return new IT_REST_Response( array( 'result' => true, 'data' => $data )  , 200 );
	    }
    }
}