<?php
	
if (! defined('ABSPATH')) {
    exit();
}

/**
* This class handles Orders database queries
**/

class RST_Reporting_Orders_Database extends IT_Rooster_Database {

    public function __construct() {
	    
        $this->tableName = IT_RST_REPORTING_ORDERS_DATABASE;
    }
}