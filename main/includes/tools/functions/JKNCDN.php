<?php

/**
 * Provides functions for dealing with a CDN.
 */
final class JKNCDN {

	const re_url = '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#';
	const mi_img = [
		'bmp', 'gif', 'ico', 'jpg', 'jpeg', 'png', 'tif', 'tiff', 'webp'
	];

	/**
	 * Rewrite a given URL to a CDN URL.
	 * Attempts to use WP Rocket's method of doing so.
	 *
	 * TODO Broaden search from just Rocket...
	 *
	 * @param string $url
	 * @return string
	 */
	static function url(string $url): string {

		// Short-circuit if Rocket isn't available or general CDN option is off
		if (!function_exists('get_rocket_cdn_url')) return $url;

		// Otherwise apply Rocket's filter
		return get_rocket_cdn_url($url);
	}

	/**
	 * Rewrite all the URLs in the given HTML.
	 * Attempts to use WP Rocket's method of doing so.
	 *
	 * TODO Broaden search from just Rocket...
	 *
	 * @param string $html
	 * @return string
	 */
	static function html(string $html, ?array $mimes=[]): string {

		// Short-circuit if Rocket isn't available or general CDN option is off
		if (!function_exists('get_rocket_cdn_url')) return $html;

		// Get all URLs
		preg_match_all(self::re_url, $html, $match);

		foreach($match[0] as $url) {

			// Skip if MIME types are specified and this one's not allowed
			if (!empty($mimes)) {
				$path = parse_url($url, PHP_URL_PATH);
				$ext = pathinfo($path, PATHINFO_EXTENSION);
				if (empty($ext) || (!in_array(strtolower($ext), $mimes))) {
					continue;
				}
			}

			$html = str_replace($url, self::url($url), $html);
		}

		return $html;
	}


	/**
	 * Return the given HTML with image URLs replaced by CDN URLs.
	 * Attempts to use WP Rocket's method of doing so.
	 *
	 * TODO Is there a better way in terms of non-assumption of Rocket?
	 *
	 * @param string $html
	 * @return string
	 */
	static function images(string $html): string {
		return self::html($html, self::mi_img);
	}
}
