<?php
	
if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

/**
* This class handles free module fastfacts and overviews
**/

class IT_RST_RP_Box {
    
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
    
    // Calculate difference between Dates
    public function getDateDiff( $date ) {
	    
	    $date1   = $date; 
		$date2   = current_time( 'mysql' );
		$diff    = abs( strtotime( $date2 ) - strtotime( $date1 ) );		
		$seconds = $diff;
		$minutes = floor( $seconds / 60 );
		$hours   = floor( $minutes / 60 );
		$days    = floor( $hours   / 24 );
		$weeks   = floor( $days    / 7  );
		$months  = floor( $days    / 30 );
	
		if ( $months > 0 ) {
			if( $months > 1 ) {
				return $months . ' ' . __( 'months ago', 'ithemes-sales-accelerator' );
			}
			else{
				return $months . ' ' . __( 'month ago', 'ithemes-sales-accelerator' );
			}
		}
		else if ( $weeks > 0 ) {
			if( $weeks > 1 ) {
				return $weeks . ' ' . __( 'weeks ago', 'ithemes-sales-accelerator' );
			}
			else{
				return $weeks . ' ' . __( 'week ago', 'ithemes-sales-accelerator' );
			}
		}
		else if ( $days > 0 ) {
			if( $days > 1 ) {
				return $days . ' ' . __( 'days ago', 'ithemes-sales-accelerator' );
			}
			else{
				return $days . ' ' . __( 'day ago', 'ithemes-sales-accelerator' );
			}
		}
		else if ( $hours > 0 ) {
			if( $hours > 1 ) {
				return $hours . ' ' . __( 'hours ago', 'ithemes-sales-accelerator' );
			}
			else{
				return $hours . ' ' . __( 'hour ago', 'ithemes-sales-accelerator' );
			}
		}
		else if ( $minutes > 0 ) {
			if( $minutes > 1 ) {
				return $minutes . ' ' . __( 'minutes ago', 'ithemes-sales-accelerator' );
			}
			else{
				return $minutes . ' ' . __( 'minute ago', 'ithemes-sales-accelerator' );
			}
		}
		else { 
			return 'just now';
		}
    }

    // Recent Orders
    public function box_recentOrders( $renderHtml = '' ) {
		
		$content = RST_Reporting_Queries::get_instance()->recentOrders();
		
		if ( $renderHtml == '1' ) {
			$html = '';
			foreach ( $content as $item ) {
				
					$html .= "
						<table class='table-row it-rst-table-recent-orders'>
							<tr>
								<th rowspan='4' width='35%' align='center'><span class='order-status " . $item['status'] . "'>" . $item['status'] . "</span></th>
							    <th  width='65%' class='ro-number'><a href='" . get_edit_post_link( $item['order'] ) . "'># " . $item['order'] . "</a></th>
							</tr>
							<tr>
							    <td class='ro-total'>" . $item['products'] . " products for " . $item['total'] . "</td>
							</tr>
							<tr>
							    <td class='ro-date'>" . $this->getDateDiff( $item['date'] ) . "</td>
							</tr>
						</table>";
			}
			
			return array( 'html' => $html );
		}
		else {
			return $content;
		}
    }
    
    // Products Available
    public function box_productsAvailable( $renderHtml = '' ) {
		
		$content = RST_Reporting_Queries::get_instance()->productsAvailable();
		
		if ( $renderHtml == '1' ) {
			$html = '<h6 class="pa_txt">' . __( 'Products Available', 'ithemes-sales-accelerator' ) .'</h6>
					<span class="number_box_big">' . $content . '</span>
					';
			
			return array( 'html' => $html );
		}
		else {
			return array( 'total' => $content );
		}
	}
		
	// Total Customers
    public function box_totalCustomers( $renderHtml ) {
		
		$content = RST_Reporting_Queries::get_instance()->totalCustomers();
		
		if ( $renderHtml == '1' ) {
			$html = '<h6 class="pa_txt">' . __( 'Total Customers', 'ithemes-sales-accelerator' ) .'</h6>
					<span class="number_box_big">' . $content . '</span>
					';
			
			return array( 'html' => $html );
		}
		else {
			return array( 'total' => $content );
		}
	}
	
	// Total Sales - Fast Fact
    public function box_totalSalesFF( $renderHtml ) {
		
		$content = RST_Reporting_Queries::get_instance()->totalSalesFF();
		
		if ( $renderHtml == '1' ) {
			$html = '<h6 class="pa_txt">' . __( 'Total Sales', 'ithemes-sales-accelerator' ) .'</h6>
					<span class="number_box_big">' . $content . '</span>
					';
			
			return array( 'html' => $html );
		}
		else {
			return array( 'total' => $content );
		}
	}
			
	// Out Stock - Fast Fact
    public function box_outStock( $renderHtml ) {
		
		$content = RST_Reporting_Queries::get_instance()->outStock();
		
		if ( $renderHtml == '1' ) {
			$html = '<h6 class="pa_txt">' . __( 'Out Stock Products', 'ithemes-sales-accelerator' ) .'</h6>
					<span class="number_box_big">' . $content . '</span>
					';
			
			return array( 'html' => $html );
		}
		else {
			return array( 'total' => $content );
		}
	}
		
	// Total Sales
    public function box_totalSales( $renderHtml = '', $dateStart, $dateEnd ) {
	    
	    $customer_id = ( isset( $_GET['customer_id'] ) ) ? $_GET['customer_id'] : 0;
	    if ( $customer_id ) {
		    $content = RST_Reporting_Queries::get_instance()->totalSalesByCustomer( $dateStart, $dateEnd, $customer_id );
		    $title   = __( 'Total Purchased', 'ithemes-sales-accelerator' );
	    } else {
		    $content = RST_Reporting_Queries::get_instance()->totalSales( $dateStart, $dateEnd );
		    $title   = __( 'Total Sales', 'ithemes-sales-accelerator' );
	    }
	    				
		if ( $renderHtml == '1' ) {
			$arrow = ( $content['variation'] >= 0 ? 'rst_arrow_up' : 'rst_arrow_down' );
			$html = '
	            	<div class="row" style="padding-top:5px;">
	            		<div class="col s12"><h6 class="pa_txt">' . $title .'</h6></div>
	            	</div>
	            	<div class="row">
						<div class="col s12"><div id="chartFixedHeightContainer"><canvas id="total_sales"></canvas></div></div>
	            	</div>
	            	<div class="row" style="padding-top:10px;">
						<div class="col s12 number_box">' . $content['total'];
	        if ( $content['variation'] != 0 ) {
		        $html .= do_shortcode( "[$arrow]" ) . ' <div class="tooltip"><span class="' . $arrow . '"> ' . $content['variation'] . ' %<span class="tooltiptext tooltip-top">' . __( 'Tooltip text', 'ithemes-sales-accelerator' ) .'</span></span></div>
		            		</div>
		            	</div>';
	        }
			
			return array( 'html' => $html, 'values' => $content['lines'] );
		}
		else {
			return $content;
		}
    }
	            
    // Total Coupons
    public function box_totalCoupons( $renderHtml, $dateStart, $dateEnd ) {

		$content = RST_Reporting_Queries::get_instance()->totalCoupons( $dateStart, $dateEnd );

		if ( $renderHtml == '1' ) {
			$arrow = ( $content['variation'] >= 0 ? 'rst_arrow_up' : 'rst_arrow_down' );
			$html = '
	            	<div class="row" style="padding-top:5px;">
	            		<div class="col s12"><h6 class="pa_txt">' . __( 'Total Coupons', 'ithemes-sales-accelerator' ) .'</h6></div>
	            	</div>
	            	<div class="row">
						<div class="col s12"><div id="chartFixedHeightContainer"><canvas id="total_coupons"></canvas></div></div>
	            	</div>
	            	<div class="row" style="padding-top:3px;">
						<div class="col s12 number_box">' . $content['total'];
	        if ( $content["variation"] != 0 ) {
		        $html .= do_shortcode( "[$arrow]" ) . ' <div class="tooltip"><span class="' . $arrow . '"> ' . $content['variation'] . ' %<span class="tooltiptext tooltip-top">' . __( 'Tooltip text', 'ithemes-sales-accelerator' ) .'</span></span></div>
		            		</div>
		            	</div>';
	        }
			
			return array( 'html' => $html, 'values' => $content['lines'] );
		}
		else {
			return $content;
		}
    }
        
    // Total Refunds
    public function box_totalRefunds( $renderHtml = '', $dateStart, $dateEnd ) {
	    
	    $product_id  = ( isset( $_GET['product_id'] ) )  ? $_GET['product_id']  : 0;
	    $customer_id = ( isset( $_GET['customer_id'] ) ) ? $_GET['customer_id'] : 0;
	    if ( $product_id ) {
		    $product_obj = new IT_WC_Product( $product_id );
		    if ( $product_obj->get_product() instanceof WC_Product_Variable ) {
				$product_id = $product_obj->get_children();
				$product_id = implode( ',', $product_id );
		    }
		    $content = RST_Reporting_Queries::get_instance()->totalRefundsByProduct( $dateStart, $dateEnd, $product_id );
		    $title 	 = __( 'Total Refunds', 'ithemes-sales-accelerator' );
	    } else if ( $customer_id ) {
		    $content = RST_Reporting_Queries::get_instance()->totalRefundsByCustomer( $dateStart, $dateEnd, $customer_id );
		    $title 	 = __( 'Total Refunded', 'ithemes-sales-accelerator' );
		} else {
		    $content = RST_Reporting_Queries::get_instance()->totalRefunds( $dateStart, $dateEnd );
		    $title 	 = __( 'Total Refunds', 'ithemes-sales-accelerator' );
	    }
	    
		if ( $renderHtml == '1' ) { 
			$arrow = ( $content['variation'] >= 0 ? 'rst_arrow_up' : 'rst_arrow_down' );
			$html = '<div class="row" style="padding-top:3px;">
	            		<div class="col s12"><h6 class="pa_txt">' . $title .'</h6></div>
						<div class="col s12"><div id="chartFixedHeightContainer"><canvas id="total_refunds"></canvas></div></div>
	            	</div>
	            	<div class="row" style="padding-top:3px;">
	            		<div class="col s12 number_box rst_arrow_down">' . $content['total'];
	        if ( $content["variation"] != 0 ) {
		        $html .= do_shortcode( "[$arrow]" ) . ' <div class="tooltip"><span class="' . $arrow . '"> ' . $content['variation'] . ' %<span class="tooltiptext tooltip-top">' . __( 'Tooltip text', 'ithemes-sales-accelerator' ) .'</span></span></div>
		            		</div>
		            	</div>';
	        }
			
			return array( 'html' => $html, 'values' => $content['lines'] );
		}
		else {
			return $content;
		}
    }
        
    // Best Category
    public function box_bestCategory( $renderHtml, $dateStart, $dateEnd ) {

		$content  = RST_Reporting_Queries::get_instance()->bestCategory( $dateStart, $dateEnd );
		$category = $content['name'];
		$len 	  = strlen( $category );
		
		if  ( $len >= 20 ) {
			$name = substr( $category, 0, 18 ) . '..';
		}
		else { 
			$name = $category;
		}
			

		if ( $renderHtml == '1' ) {
			$arrow = ( $content['variation'] >= 0 ? 'rst_arrow_up' : 'rst_arrow_down' );
			$html = '<div class="row" style="padding-top:3px;">
	            		<div class="col s12"><h6 class="pa_txt">' . __( 'Best Category', 'ithemes-sales-accelerator' ) .'</h6></div>
						<div class="col s12"><div id="chartFixedHeightContainer"><canvas id="best_category"></canvas></div></div>
	            	</div>
	            	<div class="row" style="padding-top:3px;">
	            		<div class="col s6 number_box">' . $name . '</div>
	            		<div class="col s6 variation_value">' . $content['total'];
	            	if ( $content['variation'] != 0 ) {
						$html .= '<div class="variation_data">' . do_shortcode( "[$arrow]" ) . ' <div class="tooltip"><span class="' . $arrow . '"> ' . $content['variation'] . ' %<span class="tooltiptext tooltip-top">' . __( 'Tooltip text', 'ithemes-sales-accelerator' ) .'</span></span></div></div></div>';
					}
					else { 
						$html .= '</div>';
					}	
	        $html .= '</div>';			
			return array( "html" => $html, "values" => $content["lines"] );
		}
		else {
			return $content;
		}
    }
    
    // Best Country
    public function box_bestCountry( $renderHtml, $dateStart, $dateEnd ) {
	    
	    $product_id = ( isset( $_GET['product_id'] ) ) ? $_GET['product_id'] : 0;
	    if ( $product_id ) {
		    $product_obj = new IT_WC_Product( $product_id );
		    if ( $product_obj->get_product() instanceof WC_Product_Variable ) {
				$product_id = $product_obj->get_children();
				$product_id = implode( ',', $product_id );
		    }
		    $content = RST_Reporting_Queries::get_instance()->bestCountryByProduct( $dateStart, $dateEnd, $product_id );
	    } else {
		    $content = RST_Reporting_Queries::get_instance()->bestCountry( $dateStart, $dateEnd );
	    }
		
		$len = strlen( $content['name'] );
		
		if ( $len >= 20 ) {
			$name = substr( $content['name'], 0, 18 ) . '..';
		}
		else { 
			$name = $content['name'];
		}

		if ( $renderHtml == '1' ) {
			$arrow = ( $content['variation'] >= 0 ? 'rst_arrow_up' : 'rst_arrow_down' );
			$html = '<div class="row" style="padding-top:3px;">
	            		<div class="col s12"><h6 class="pa_txt">' . __( 'Best Country', 'ithemes-sales-accelerator' ) .'</h6></div>
						<div class="col s12"><div id="chartFixedHeightContainer"><canvas id="best_country"></canvas></div></div>
	            	</div>
	            	<div class="row" style="padding-top:3px;">
	            		<div class="col s6 number_box">' . $name . '</div>
	            		<div class="col s6 variation_value">' . $content['total'];
	            	if ( $content['variation'] != 0 ) { 
						$html .= '<div class="variation_data">' . do_shortcode( "[$arrow]" ) . ' <div class="tooltip"><span class="' . $arrow . '"> ' . $content['variation'] . ' %<span class="tooltiptext tooltip-top">' . __( 'Tooltip text', 'ithemes-sales-accelerator' ) .'</span></span></div></div></div>';
					}
					else { 
						$html .= '</div>';
					}	
	        $html .= '</div>';			
			return array( 'html' => $html, 'values' => $content['lines'] );
		}
		else {
			return $content;
		}
    }
    
    // Best Shipping
    public function box_bestShipping( $renderHtml, $dateStart, $dateEnd ) {

		$content = RST_Reporting_Queries::get_instance()->bestShipping( $dateStart, $dateEnd );
		$len 	 = strlen( $content['name'] );
		
		if ( $len >= 20 ) {
			$name = substr( $content['name'], 0, 18 ) . '..';
		}
		else {
			$name = $content['name'];
		}

		if ( $renderHtml == '1' ) {
			$arrow = ( $content['variation'] >= 0 ? 'rst_arrow_up' : 'rst_arrow_down' );
			$html = '<div class="row" style="padding-top:3px;">
	            		<div class="col s12"><h6 class="pa_txt">' . __( 'Best Shipping', 'ithemes-sales-accelerator' ) .'</h6></div>
						<div class="col s12"><div id="chartFixedHeightContainer"><canvas id="shipping_method"></canvas></div></div>
	            	</div>
	            	<div class="row" style="padding-top:3px;">
	            		<div class="col s6 number_box">' . $name . '</div>
	            		<div class="col s6 variation_value">' . $content['total'];
	            	if ( $content['variation'] != 0 ) {
						$html .= '<div class="variation_data">' . do_shortcode( "[$arrow]" ) . ' <div class="tooltip"><span class="' . $arrow . '"> ' . $content['variation'] . ' %<span class="tooltiptext tooltip-top">' . __( 'Tooltip text', 'ithemes-sales-accelerator' ) .'</span></span></div></div></div>';
					}
					else { 
						$html .= '</div>';
					}	
	        $html .= '</div>';			
			return array( "html" => $html, "values" => $content["lines"] );
		}
		else {
			return $content;
		}
    }
	
    // Send result by JSON Endpoint
    public function contentBox_json_endpoint( $request ) {
	    
	    $params = $request->get_params();
	    // Get params
	    $renderHtml = isset( $params['renderHtml'] ) ? $params['renderHtml'] : '';
	    $boxName 	= isset( $params['slug'] ) 		 ? $params['slug'] 		 : '';
	    $dateStart 	= isset( $params['dateStart'] )  ? $params['dateStart']  : date( 'Y-m-d', (strtotime ( '-30 days' ) ) );
	    $dateEnd 	= isset( $params['dateEnd'] ) 	 ? $params['dateEnd'] 	 : date( 'Y-m-d' );

		$data = array( 'html' => '' );
		
		switch ( $boxName ) {
			
		    // Recent Orders
		    case 'recent_orders':
		    	if ( $renderHtml == '1' ) {
		    		$data = $this->box_recentOrders( $renderHtml );
		    	}
		    	else {
					$data = $this->box_recentOrders( '' );
				}
			break;
			
			// Products Available
		    case 'products_available':
		    	if ( $renderHtml == '1' ) {
		    		$data = $this->box_productsAvailable( $renderHtml );
		    	}
		    	else {
					$data = $this->box_productsAvailable( '' );
				}
			break;
						
			// Total Customers
		    case 'total_customers':
		    	if ( $renderHtml == '1' ) {
		    		$data = $this->box_totalCustomers( $renderHtml );
		    	}
		    	else {
					$data = $this->box_totalCustomers( '' );
				}
			break;
															
			// Total Sales
		    case 'total_sales':
		    	if ( $renderHtml == '1' ) {
		    		$data = $this->box_totalSales( $renderHtml, $dateStart, $dateEnd );
		    	}
		    	else {
					$data = $this->box_totalSales( '', $dateStart, $dateEnd );
				}
			break;
			
			// Total Sales
		    case 'total_coupons':
		    	if ( $renderHtml == '1' ) {
		    		$data = $this->box_totalCoupons( $renderHtml, $dateStart, $dateEnd );
		    	}
		    	else {
					$data = $this->box_totalCoupons( '', $dateStart, $dateEnd );
				}
			break ;
						
			// Total Refunds
		    case 'total_refunds':
		    	if ( $renderHtml == '1' ) {
		    		$data = $this->box_totalRefunds( $renderHtml, $dateStart, $dateEnd );
		    	}
		    	else {
					$data = $this->box_totalRefunds( '', $dateStart, $dateEnd );
				}
			break ;
			
			// Best Category
		    case 'best_category':
		    	if ( $renderHtml == '1' ) {
		    		$data = $this->box_bestCategory( $renderHtml, $dateStart, $dateEnd );
		    	}
		    	else {
					$data = $this->box_bestCategory('', $dateStart, $dateEnd );
				}
			break ;
			
			// Best Country
		    case 'best_country':
		    	if ( $renderHtml == '1' ) {
		    		$data = $this->box_bestCountry( $renderHtml, $dateStart, $dateEnd );
		    	}
		    	else {
					$data = $this->box_bestCountry( '', $dateStart ,$dateEnd );
				}
			break ;
			
			// Best Shipping
		    case 'shipping_method':
		    	if ( $renderHtml == '1' ) {
		    		$data = $this->box_bestShipping( $renderHtml, $dateStart, $dateEnd );
		    	}
		    	else {
					$data = $this->box_bestShipping( '', $dateStart, $dateEnd );
				}
			break ;
			
			// Total Sales FF
		    case 'total_sales_ff':
		    	if ( $renderHtml == '1' ) {
		    		$data = $this->box_totalSalesFF( $renderHtml );
		    	}
		    	else {
					$data = $this->box_totalSalesFF( '' );
				}
			break ;
									
			// Out of Stock FF
		    case  'out_of_stock':
		    	if ( $renderHtml == '1' ) {
		    		$data = $this->box_outStock( $renderHtml );
		    	}
		    	else {
					$data = $this->box_outStock( '' );
				}
			break ;
			
			default :
		    	return new IT_REST_Response( array( 'result' => false, 'data' => array() )  , 200 );
			break;
		}
	    
	    return new IT_REST_Response( array( 'result' => true, 'data' => $data )  , 200 );
    }
}
