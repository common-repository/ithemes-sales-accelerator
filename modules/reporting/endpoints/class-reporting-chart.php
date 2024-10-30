<?php
	
if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

/**
* This class handles free module charts
**/

class IT_RST_RP_Chart {
    
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
    
    // Customers vs Guests
    public function chart_cg( $renderHtml, $dateStart, $dateEnd ) {

		$content = RST_Reporting_Queries::get_instance()->customersvsguest( $dateStart, $dateEnd );

		if ( $renderHtml == '1' ) {
			$value = ( $content['customers_per'] > $content['guests_per'] ? $content['customers_per'] : $content['guests_per'] );
			$html = '<div class="inline pt0"><h1>' . __( 'Customers vs Guests', 'ithemes-sales-accelerator' ) .'</h1><span class="selected_date">30 Days</span></div>
		            	<div id="chartFixedHeightContainer_Axis"><canvas id="customers_guests"></canvas><span class="valueDonut">' . $value . '%</span></div>
		            	<div style="margin-left: -20px;margin-right: -20px;">
		            		<div class="legend1"><span class="legend_axis green"></span> <span class="txt">' . __( 'Customers Spend', 'ithemes-sales-accelerator' ) .'</span> <span class="value">' . $content['customers_val'] . ' (' . $content['customers_per'] . '%)</span></div>
		            		<div class="legend2"><span class="legend_axis blue">&nbsp;</span> <span class="txt">' . __( 'Guests Spend', 'ithemes-sales-accelerator' ) .'</span> <span class="value">' . $content['guests_val'] . ' (' . $content['guests_per'] . '%)</span></div>
					</div>';
			return array( 'html' => $html, 'values' => array( 'label1' => 'Customers', 'value1' => $content['customers_per'], 'label2' => 'Guests', 'value2' => $content['guests_per'] ) );
		}
		else {
			$reporting_features = RST_Reporting_Features::get_instance();
			$type_chart 		= $reporting_features->is_lines_chart( 'customers_guests' );
			$app_content = array();
			
			foreach ( $content as $k => $v ) {
				$app_content[] = array( 'name' => $k, 'total' => $v, 'total_app' => $v );
			}
			
			return array( 'columns' => array( 'customers_val' => 'Customers Spend', 'guests_val' => 'Guests Spend', 'customers_per' => 'Customers (%)', 'guests_per' => 'Guests (%)' ), 'type_chart' => $type_chart, 'values' => $app_content );
		}
    }
    
    // Shipping Method
    public function chart_shippingMethod( $renderHtml, $dateStart, $dateEnd ) {

		$content = RST_Reporting_Queries::get_instance()->shippingMethod( $dateStart, $dateEnd );

		if ( $renderHtml == '1' ) {
			$html = '<div class="inline pt0"><h1>' . __( 'Shipping Method', 'ithemes-sales-accelerator' ) . '</h1><span class="selected_date">' . __( 'Last 30 Days', 'ithemes-sales-accelerator' ) . '</span></div>
		            	<div id="chartFixedHeightContainer_Axis"><canvas id="shipping_method"></canvas></div>
					</div>';
			return array( 'html' => $html, 'values' => array( $content ) );
		}
		else {
			$reporting_features = RST_Reporting_Features::get_instance();
			$type_chart 		= $reporting_features->is_lines_chart( 'shipping_method' );
			return array( 'columns' => array( 'name' => 'Shipping Method', 'total_app' => 'Total Sales' ), 'type_chart' => $type_chart, 'values' => $content );
		}	
    }
       
    // Send result by JSON Endpoint
    public function contentChart_json_endpoint( $request ) {
	    
	    $params = $request->get_params();
	    // Get params
	    $renderHtml = isset( $params['renderHtml'] ) ? $params['renderHtml'] : '';
	    $chartName 	= isset( $params['slug'] ) 		 ? $params['slug'] 		 : '';
	    $dateStart 	= isset( $params['dateStart'] )  ? $params['dateStart']  : date( 'Y-m-d', (strtotime ( '-30 days' ) ) );
	    $dateEnd 	= isset( $params['dateEnd'] )    ? $params['dateEnd'] 	 : date( 'Y-m-d' );
		
		switch( $chartName ){
			
			// Customers vs Guests
		    case 'customers_guests':
		    	if ( $renderHtml == '1' ) {
		    		$data = $this->chart_cg( $renderHtml, $dateStart, $dateEnd );
		    	}
		    	else {
					$data = $this->chart_cg( '', $dateStart, $dateEnd );
				}
			break;
						
			// Shipping Method		    
			case 'shipping_method':
		    	if ( $renderHtml == '1' ) {
		    		$data = $this->chart_shippingMethod( $renderHtml, $dateStart, $dateEnd );
		    	}
		    	else {
					$data = $this->chart_shippingMethod( '', $dateStart, $dateEnd );
				}
			break;
			
		    // Still neds to implement free charts
		    default :
		    	return new IT_REST_Response( array( 'result' => false, 'data' => array() )  , 200 );
			break;
		}
			    
	    return new IT_REST_Response( array( 'result' => true, 'data' => $data )  , 200 );
    }   
}