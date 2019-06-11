<?php

/**
 * Provides functions for dealing with strings.
 */
final class JKNDebugging {

	/**
	 * Die, pretty-printing the given object.
	 *
	 * @param mixed|null $object The object to pretty-print.
	 */
	static function autopsy($object=null): void {
		wp_die(sprintf('<pre>%s</pre>', print_r($object, true)));
	}

	static function set($flag=E_ALL, bool $log=true, bool $display=true,
			string $logfile=''): void {

		if ($log && empty($logfile)) $logfile = content_url('debug.log');

		ini_set('display_errors',   $display);
		ini_set('error_reporting',  $flag);
		ini_set('log_errors',       $log);
		ini_set('error_log',        $logfile);
	}

	/**
	 * Turn on all errors.
	 *
	 * @param int $flag
	 * @param bool $log
	 * @param bool $display
	 * @param string $logfile
	 */
	static function on($flag=E_ALL, bool $log=true, bool $display=true,
			string $logfile=''): void {

		self::set($flag, $log, $display, $logfile);

	}

	/**
	 * Turn off all errors.
	 *
	 * @param null $flag
	 * @param bool $log
	 * @param bool $display
	 * @param string|null $logfile
	 */
	static function off($flag=null, bool $log=false, bool $display=false,
			string $logfile=null): void {

		self::set($flag, $log, $display, $logfile);
	}

}
