<?php

/**
 * Provides functions for dealing with Javascript.
 */
final class JKNJavascript {

	/**
	 * Return or output a <script> tag for the given JS.
	 *
	 * TODO add optional enqueueing in head or footer
	 *
	 * @param string $js
	 * @return string
	 */
	static function tag(string $js): string {
		return sprintf('<script type="text/javascript">%s</script>',
			JKNMinifying::minify_js($js));
	}

	/**
	 * Return or output a <script> tag for the given JS source URL.
	 *
	 * TODO add optional enqueueing in head or footer
	 *
	 * @param string $src
	 * @return string
	 */
	static function tag_src(string $src): string {
		return sprintf('<script type="text/javascript" src="%s"></script>',
			$src);
	}

	/**
	 * Enqueue the arrive-2.4.1.js library.
	 * https://github.com/uzairfarooq/arrive
	 *
	 * @param bool $in_footer Whether to place it in the footer.
	 */
	static function enqueue_arrive(bool $in_footer=true): void {
		wp_enqueue_script('arrive-2.4.1.js',
			sprintf('%s/js/arrive-2.4.1.js', JKN_ASSETS),
			['jquery'],
			'2.4.1',
			$in_footer
		);
	}
}
