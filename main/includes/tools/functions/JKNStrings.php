<?php

/**
 * Provides functions for dealing with strings.
 */
final class JKNStrings {

	/*
	 * =========================================================================
	 * Replacement
	 * =========================================================================
	 */

	/**
	 * Return the given haystack with all instances of needle replaced by
	 * replacement (like str_replace but all of them).
	 *
	 * @param string $needle
	 * @param string $replacement
	 * @param string $haystack
	 * @return string
	 */
	static function replace_all(string $needle, string $replacement,
		string $haystack): string {

		while (!empty(strpos($haystack, $needle))) {
			$haystack = str_replace($needle, $replacement, $haystack);
		}

		return $haystack;
	}

	/**
	 * Replace the last occurrence of the given substring in the given string
	 * using the given replacement.
	 * Useful for e.g. the last comma in an imploded list.
	 *
	 * Credit: https://stackoverflow.com/questions/3835636
	 *
	 * @param string $needle
	 * @param string $replacement
	 * @param string $haystack
	 * @return string
	 */
	static function replace_last(string $needle, string $replacement,
			string $haystack): string {

		$explode = explode(strrev($needle), strrev($haystack), 2);
		$implode = implode(strrev($replacement), $explode);
		return strrev($implode);
	}

	/**
	 * Return the given haystack with all double occurrences of the given
	 * needle removed.
	 *
	 * @param string $haystack
	 * @param string $needle
	 * @return string
	 */
	static function remove_doubles(string $haystack, string $needle): string {
		return self::replace_all($needle . $needle, $needle, $haystack);
	}


	/*
	 * =========================================================================
	 * Identification
	 * =========================================================================
	 */

	/**
	 * Return true iff the given string starts with the given substring.
	 * Credit: stackoverflow.com/questions/834303
	 *
	 * @param string $haystack
	 * @param string $needle
	 * @return bool
	 */
	static function starts_with(string $haystack, string $needle): bool {
		$length = strlen($needle);
		if ($length == 0) return true;
		return (substr($haystack, 0, $length) === $needle);
	}

	/**
	 * Return true iff the given string ends with the given substring.
	 * Credit: stackoverflow.com/questions/834303
	 *
	 * @param string $haystack
	 * @param string $needle
	 * @return bool
	 */
	static function ends_with(string $haystack, string $needle): bool {
		$length = strlen($needle);
		if ($length == 0) return true;
		return (substr($haystack, -$length) === $needle);
	}


	/*
	 * =========================================================================
	 * Sanitization
	 * =========================================================================
	 */

	/**
	 * Return the name, sanitized. (alphabet, digits, underscore, hyphen)
	 *
	 * @param string $name
	 * @param bool $lower Whether to lowercase the result.
	 * @param string $replacer A sanitizable replacer.
	 * @return string
	 */
	static function sanitize(string $name, bool $lower=true,
		string $replacer='_'): string {

		// Define our pattern
		$pattern = '#[^a-zA-Z0-9_-]#';

		// See if the replacer is okay; if not use underscore
		$sanitized_replacer = preg_replace($pattern, '', $replacer);
		if (empty($sanitized_replacer)) $replacer = '_';

		// Sanitize the name and optionally lowercase it
		$sanitized_name = preg_replace($pattern, $replacer, $name);
		return ($lower) ? strtolower($sanitized_name) : $sanitized_name;
	}


	/*
	 * =========================================================================
	 * Capitalization
	 * =========================================================================
	 */

	/**
	 * Return the string with the first letter capitalized, the rest untouched.
	 *
	 * @param string $s
	 * @return string
	 */
	static function capitalize(string $s): string {
		return strtoupper(substr($s, 0, 1)) . substr($s, 1);
	}

	/**
	 * Return the string in sentence case (only first letter capitalized).
	 * Extremely simple; does not detect proper names, etc.
	 *
	 * @param string $s
	 * @return string
	 */
	static function sentence_case(string $s): string {
		return strtoupper(substr($s, 0, 1)) . strtolower(substr($s, 1));
	}

	/**
	 * Return the string in title case (first letter of each word capitalized).
	 * Extremely simple; does not detect proper names, etc.
	 *
	 * @param string $s
	 * @return string
	 */
	static function title_case(string $s): string {
		$words = explode(' ', $s);
		return implode(' ', array_map([__CLASS__, 'sentence_case'], $words));
	}
}
