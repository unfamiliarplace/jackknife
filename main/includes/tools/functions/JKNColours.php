<?php

/**
 * Functions for working with colours.
 */
class JKNColours {

	/*
	 * =========================================================================
	 * Constants
	 * =========================================================================
	 */

	const good  = '#cdf4da';
	const meh   = '#efedb1';
	const bad   = '#f4c1c1';


	/*
	 * =========================================================================
	 * Defaults
	 * =========================================================================
	 */

	/**
	 * Return a colour signifying 'good'. Optionally supply alpha channel.
	 *
	 * @param float|null $opacity 0.0 - 1.0
	 * @return string
	 */
	static function good(float $opacity=null): string {
		return self::hex_to_rgba(self::good, $opacity);
	}

	/**
	 * Return a colour signifying 'meh'. Optionally supply alpha channel.
	 *
	 * @param float|null $opacity 0.0 - 1.0
	 * @return string
	 */
	static function meh(float $opacity=null): string {
		return self::hex_to_rgba(self::meh, $opacity);
	}

	/**
	 * Return a colour signifying 'bad'. Optionally supply alpha channel.
	 *
	 * @param float|null $opacity 0.0 - 1.0
	 * @return string
	 */
	static function bad(float $opacity=null): string {
		return self::hex_to_rgba(self::bad, $opacity);
	}


	/*
	 * =========================================================================
	 * Conversions
	 * =========================================================================
	 */

	/**
	 * Return the given hex colour string as an RGBA colour string.
	 * Optionally supply an alpha channel, 0.0 - 1.0.
	 *
	 * Credit: Bojan Petrovic
	 * mekshq.com/how-to-convert-hexadecimal-color-code-to-rgb-or-rgba-using-php
	 *
	 * @param string $hex
	 * @param float|null $opacity
	 * @return string
	 */
	static function hex_to_rgba(string $hex, float $opacity=null): string {
		$default = 'rgb(0, 0, 0)';

		// Return default if no hex provided
		if (empty($hex)) return $default;

		// Sanitize hex if "#" is provided
		if ($hex[0] == '#') $hex = substr($hex, 1);

		// Check if colour has 6 or 3 characters and get values
		if (strlen($hex) == 6) {
			$colours = [
				$hex[0] . $hex[1],
				$hex[2] . $hex[3],
				$hex[4] . $hex[5]
			];

		} elseif (strlen($hex) == 3) {
			$colours = [
				$hex[0] . $hex[0],
				$hex[1] . $hex[1],
				$hex[2] . $hex[2]
			];

		} else {
			return $default;
		}

		// Convert hexadec to rgb
		$rgb = array_map('hexdec', $colours);

		// Check if opacity is set(rgba or rgb)
		if ($opacity){
			if (abs($opacity) > 1) $opacity = 1.0;
			$output = sprintf('rgba(%s, %s)', implode(', ', $rgb), $opacity);
		} else {
			$output = sprintf('rgb(%s)', implode(', ', $rgb));
		}

		// Return rgb(a) colour string
		return $output;
	}

	/**
	 * Return the given rgb(a) colour as a hex. (The alpha channel is ignored.)
	 *
	 * Credit: two answers from stackoverflow.com/questions/32962624
	 *
	 * @param string|array $rgb Either an array of values or a string of them.
	 * @return string
	 */
	static function rgb_to_hex($rgb): string {

		// If it's a string, convert it to an array
		if (gettype($rgb) == 'string') {

			// If it's got the 'rgb(X)' structure get the interior
			$re = '^rgba?\((.*)\)';
			preg_match($re, $rgb, $matches);
			if (isset($matches[1])) $rgb = $matches[1];

			// Remove spaces and explode commas
			$rgb = JKNStrings::replace_all(' ', '' , $rgb);
			$rgb = explode(',', $rgb);
		}

		// Create hexadecimal
		return sprintf("#%02x%02x%02x", $rgb[0], $rgb[1], $rgb[2]);
	}
}
