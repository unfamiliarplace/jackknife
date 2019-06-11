<?php

/**
 * Provides functions for dealing with strings.
 */
final class JKNAjax {

	/**
	 * Return a qualified action name such that it works for WP purposes.
	 *
	 * @param string $action
	 * @return string
	 */
	static function qualify_action(string $action): string {
		return sprintf('wp_ajax_%s', $action);
	}

	/**
	 * Return a qualified non-private action name that works for WP purposes.
	 * A nopriv action is one usable by non-logged-in users.
	 *
	 * @param string $action
	 * @return string
	 */
	static function qualify_nopriv_action(string $action): string {
		return self::qualify_action(sprintf('nopriv_%s', $action));
	}
}
