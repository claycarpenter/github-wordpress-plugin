<?php

/*
 * Simple error/debug logging utility.
 */
class Logger {
	public static $is_enabled = FALSE;
	private static $log_file_location = 'debug.log';

	public static function log_debug( $message ) {
		if ( self::$is_enabled ) {
			error_log( self::get_timestamp( ) . " - " . $message . "\n", 3, self::$log_file_location );
		}
	}

	private static function get_timestamp( ) {
		return date( DateTime::RFC822 );
	}

}
?>
