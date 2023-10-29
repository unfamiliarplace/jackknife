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

	/**
	 * Return a script to console-log the given text.
	 *
	 * @param string $text
	 * @return string
	 */
	static function get_console_text($text=''): string {
		$text = addslashes($text);
		return "<script>console.log('$text')</script>";
	}

	/**
	 * Set the PHP error reporting settings.
	 *
	 * @param int $flag The flags to set for error_reporting.
	 * @param bool $log Whether to log using PHP's log setting.
	 * @param bool $display Whether to display errors.
	 * @param string $logfile The path to the logfile.
	 */
	static function set($flag=E_ALL, bool $log=true, bool $display=true,
			string $logfile=''): void {

		if ($log && empty($logfile)) $logfile = content_url('debug.log');

		ini_set('display_errors',   $display);
		ini_set('error_reporting',  $flag);
		ini_set('log_errors',       $log);
		ini_set('error_log',        $logfile);
	}

	/**
	 * Log an error using WP's debug log.
	 *
	 * @param object data
	 */
	static function log($data='Logged') {
		if (is_array($data) || is_object($data)) {
			error_log(print_r($data, true));
		} else {
			error_log($data);
		}
	}

    /**
     * Clear the debug log.
     */
    static function clear_log() {
        $path = WP_CONTENT_DIR . '/debug.log';
        if (defined('WP_DEBUG_LOG') && (gettype(WP_DEBUG_LOG) === 'string')) {
            $path = WP_DEBUG_LOG;
        }

        file_put_contents($path, "");
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
