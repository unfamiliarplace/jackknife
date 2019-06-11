<?php

/**
 * Provides functions for formatting miscellaneous objects.
 */
final class JKNFormatting {

	/**
	 * Return a string formatting the given number of bytes in a human-readable
	 * file size.
	 *
	 * @param int $bytes The filesize.
	 * @param int $precision How many decimals to cut to.
	 * @return string
	 */
	static function filesize(int $bytes, int $precision=2): string {
		$base = log($bytes, 1024);
		$suffixes = ['', 'kb', 'mb', 'gb', 'tb'];
		return sprintf('%s %s',
			round(pow(1024, $base - floor($base)), $precision),
			$suffixes[(int) floor($base)]);
	}
}
