<?php

/**
 * Provides functions for working with classes.
 */
final class JKNClasses {

	/**
	 * Provided an associative array [classname => filepath], set them
	 * to autoload.
	 *
	 * @param array $name_to_file The array. Paths can be relative or absolute.
	 */
	static function autoload(array $name_to_file): void {
		$dir = JKNAPI::dir(null, debug_backtrace());

		$cb = function (string $class_name) use ($name_to_file, $dir): void {

			foreach($name_to_file as $name => $file) {
				if (!JKNStrings::starts_with($file, '/')) {
					$file = sprintf('%s/%s', $dir, $file);
				}

				if ($class_name == $name) {
					require_once $file;
					return;
				}
			}
		};

		spl_autoload_register($cb);
	}
}
