<?php
	
if (! defined('ABSPATH')) {
    exit();
}

/**
* This class handles Order Product database queries
**/

class RST_Reporting_Order_Products_Database extends IT_Rooster_Database {

    public function __construct() {
	    
        $this->tableName = IT_RST_REPORTING_ORDER_PRODUCTS_DATABASE;
    }
}