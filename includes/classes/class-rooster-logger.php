<?php

class IT_RST_Logger{

	public $file;
	public $file_prev;
	public $strdate;
	public $level = array( 'debug' => 7, 'info' => 6, 'notice' => 5, 'warn' => 4, 'critical' => 3, 'error' => 2 );

	public $timer_start      = array();
	public $accumulated_time = array();

	public function __construct($file = false){

		if ( ! defined('IT_RST_DEBUG_MODE') ) return;

		// Build logfile path
		$uploads   = wp_upload_dir();
		$uploaddir = $uploads['basedir'];
		$logdir    = $uploaddir . '/rooster-logs';
		$logfile   = $logdir . '/rooster.log';
		$oldfile   = $logdir . '/rooster-old.log';

		if ( IT_RST_DEBUG_MODE == '' ) {
			
			// Remove logfile when logging is disabled
			if ( file_exists( $logfile ) ) unlink( $logfile );
			if ( file_exists( $oldfile ) ) unlink( $oldfile );

		} else {
			// Rotate logfile if greater than 10mb
			if ( file_exists( $logfile ) && filesize( $logfile ) > 10 * 1024 * 1024 ) {
				if ( file_exists( $oldfile ) ) unlink( $oldfile );
				rename( $logfile, $oldfile );
			}

			if ( $file ) {
				$this->file = $file;
			} else {
				if ( !is_dir( $logdir ) ) mkdir( $logdir );
				if ( !file_exists( $logfile ) ) touch( $logfile );
				$this->file      = $logfile;
				$this->file_prev = $oldfile;
			}
			$this->strdate = 'Y/m/d H:i:s';
			
			$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
			if ( $action == 'heartbeat' ) return;
			if ( $action == 'wpla_tail_log' ) return;
			if ( $action == 'wplister_tail_log' ) return;

			// Only log request for admin pages or post requests
			if ( $_SERVER['REQUEST_METHOD'] == 'POST' || is_admin() ) {
				$this->info( 
					$_SERVER['REQUEST_METHOD'] . ': ' . 
					$_SERVER['QUERY_STRING'] . ' - ' . 
					( isset( $_POST['action'] ) ? $_POST['action'] : '' ) .' - '. 
					( isset( $_POST['do'] ) ? $_POST['do'] : '' )  
				);
			}
		}
	}

	public function log( $level = debug, $msg = false ) {
		
		//If debug is not on, then don't log
		if ( defined( 'IT_RST_DEBUG_MODE' ) ) {
			if ( IT_RST_DEBUG_MODE >= $this->level[ $level ] ) {
				return error_log( '['.gmdate( $this->strdate ).'] '.strtoupper( $level ) . ' ' . $msg . "\n", 3, $this->file );
			}
		}
	}

	public function debug( $msg = false ){
		
		return $this->log( 'debug', $msg );
	}

	public function info( $msg = false ) {
		
		return $this->log( 'info', $msg );
	}

	public function notice( $msg = false ) {
		
		return $this->log( 'notice', $msg );
	}

	public function warn( $msg = false ) {
		
		return $this->log( 'warn', $msg );
	}

	public function critical( $msg = false ) {
		
		return $this->log( 'critical', $msg );
	}

	public function error( $msg = false ) {
		
		return $this->log( 'error', $msg );
	}

	public function start( $key ) {
		
		$this->timer_start[ $key ] = microtime( true ) * 1000;
	}

	public function logTime( $key ) {
		
		$now  = microtime( true ) * 1000;
		$msec = round( $now - $this->timer_start[ $key ], 3 );
		$this->debug( "*** It took $msec ms to process '$key'" );
	}

	public function startTimer( $key ) {
		
		$this->timer_start[ $key ] = microtime( true ) * 1000;
	}
	public function endTimer( $key ) {
		
		$now  = microtime( true ) * 1000;
		$msec = $now - $this->timer_start[$key];
		if ( ! isset( $this->accumulated_time[ $key ] ) ) $this->accumulated_time[ $key ] = 0;
		$this->accumulated_time[ $key ] += $msec;
	}
	public function logSpentTime( $key ) {
		
		if ( ! isset( $this->accumulated_time[ $key ] ) ) return;
		$msec = round( $this->accumulated_time[ $key ], 3 );
		$this->debug( "*** I spent $msec ms in total to '$key'" );
	}

    public function callStack( $stacktrace ) {
	    
        $this->info( str_repeat("=", 50) );
        $i = 1;
        foreach ( $stacktrace as $node ) {
            $this->info( "$i. ".basename( $node['file'] ) .":" .$node['function'] ."(" .$node['line'].")" );
            $i++;
        }
        $this->info( str_repeat( "=", 50 ) );
    }
}