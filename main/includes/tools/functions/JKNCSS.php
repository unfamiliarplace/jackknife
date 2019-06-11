<?php

/**
 * Provides functions for dealing with CSS.
 */
final class JKNCSS {

	/*
	 * =========================================================================
	 * CSS
	 * =========================================================================
	 */

	/**
	 * Return a <style> tag for the given CSS, minified.
	 * TODO add optional enqueueing in head or footer
	 *
	 * @param string $css
	 * @return string
	 */
	static function tag(string $css): string {
		return sprintf('<style type="text/css">%s</style>',
			JKNMinifying::minify_css($css));
	}

	/**
	 * Return or output a <script> tag for the given CSS source URL.
	 *
	 * TODO add optional enqueueing in head or footer
	 *
	 * @param string $src
	 * @return string
	 */
	static function tag_src(string $src): string {
		return sprintf('<link rel="stylesheet" type="text/css" href="%s" />',
			$src);
	}
}
