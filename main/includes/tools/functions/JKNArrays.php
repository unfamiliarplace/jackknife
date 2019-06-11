<?php

/**
 * Provides functions for dealing with strings.
 */
final class JKNArrays {

	/**
	 * Given a needle present in a haystack, return the item following it
	 * (by $n steps).
	 *
	 * @param $needle
	 * @param array $haystack
	 * @param int $n
	 * @return mixed|null
	 */
	static function following($needle, array $haystack, int $n=1) {

		// Orient
		reset($haystack);

		// Determine where this volume falls
		for ($i = 0; $i < count($haystack); $i++) {

			// Until we find it, simply advance
			if (current($haystack) != $needle) {
				next($haystack);

				// Once we find it, begin traversing
			} else {
				for ($j = 0; $j < abs($n); $j++) {

					// Advance if counting up, rewind if not
					$candidate = ($n > 0) ? next($haystack) : prev($haystack);

					// Return if we reach the end
					if (empty($candidate)) return null;
				}

				// If we get here, we've found it
				return current($haystack);
			}
		}

		return null;
	}

	/**
	 * Return a flattened version of the given two-dimensional array.
	 * Credit: https://stackoverflow.com/a/15939539
	 *
	 * @param array $arr
	 * @return array
	 */
	static function flatten_2D(array $arr): array {
		if (empty($arr)) return $arr;

		$flat = [];
		array_walk_recursive($arr,
			function($v, string $k) use (&$flat) { $flat[] = $v; }
		);

		return $flat;
	}
}
