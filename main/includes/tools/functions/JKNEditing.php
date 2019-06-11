<?php

/**
 * Provides functions for dealing with strings.
 */
final class JKNEditing {

	/**
	 * Allow a given HTML tag in the tinyMCE editor.
	 * N.B. This code is adapated from somewhere but I can't remember where.
	 *
	 * @param string $tag
	 * @param int $priority The filter priority.
	 */
	static function allow_in_tinyMCE(string $tag, int $priority=100) {

		$filter_cb = function(array $options) use ($tag): array {

			if (!isset($options['extended_valid_elements'])) {
				$options['extended_valid_elements'] = "$tag";
			} else {
				$options['extended_valid_elements'] .= ",$tag";
			}

			if (!isset($options['valid_children'])) {
				$options['valid_children'] = "+body[$tag]";
			} else {
				$options['valid_children'] .= ",+body[$tag]";
			}

			if (!isset($options['custom_elements'])) {
				$options['custom_elements'] = "$tag";
			} else {
				$options['custom_elements'] .= ",$tag";
			}

			return $options;
		};

		add_filter('tiny_mce_before_init', $filter_cb, $priority);
	}

	/**
	 * Disallow a given HTML tag in the tinyMCE editor.
	 *
	 * @param string $tag
	 * @param int $priority The filter priority.
	 */
	static function disallow_in_tinyMCE(string $tag, int $priority=100) {

		$filter_cb = function(array $options) use ($tag): array {

			// Remove from extended valid elements
			if (isset($options['extended_valid_elements'])) {
				$data = $options['extended_valid_elements'];
				$data = str_replace($tag, '', $data);
				$data = str_replace(',,', '', $data);
				$options['extended_valid_elements'] = $data;
			}

			// Remove from valid children
			if (isset($options['valid_children'])) {
				$data = $options['valid_children'];
				$data = str_replace("+body[$tag]", '', $data);
				$data = str_replace(',,', '', $data);
				$options['valid_children'] = $data;
			}

			// Remove from custom elements
			if (isset($options['custom_elements'])) {
				$data = $options['custom_elements'];
				$data = str_replace($tag, '', $data);
				$data = str_replace(',,', '', $data);
				$options['custom_elements'] = $data;
			}

			return $options;
		};

		add_filter('tiny_mce_before_init', $filter_cb, $priority);
	}


	/**
	 * Allow a given HTML tag and any subtags it has in the kses.
	 *
	 * @param string $tag
	 * @param array $subtags
	 * @param int $priority The filter priority.
	 */
	static function allow_in_kses(string $tag, array $subtags=[],
		int $priority=100) {

		$filter_cb = function(array $data, string $context)
		use ($tag, $subtags): array {

			// Bail if this is not a post
			if ($context !== 'post') return $data;

			// If the key is not there, set it
			if (!in_array($tag, array_keys($data))) $data[$tag] = [];

			// Merge and return
			$data[$tag] = array_merge($data[$tag], $subtags);
			return $data;
		};

		add_filter('wp_kses_allowed_html', $filter_cb, $priority, 2);
	}

	/**
	 * Disallow a given HTML tag and any subtags it has in the kses.
	 *
	 * @param string $tag
	 * @param array $subtags
	 * @param int $priority The filter priority.
	 */
	static function disallow_in_kses(string $tag, array $subtags=[],
		int $priority=100) {

		$filter_cb = function(array $data, string $context)
		use ($tag, $subtags): array {

			// Bail if this is not a post
			if ($context !== 'post') return $data;

			// Bail if the tag is already missing
			if (!in_array($tag, array_keys($data))) return $data;

			// If the user supplied no subtags, delete the tag
			if (empty($subtags)) {
				unset($data[$tag]);

				// Otherwise, remove given subtags from the kses
			} else {
				foreach($data[$tag] as $key) {
					if (in_array($key, array_keys($subtags))) {
						unset($data[$tag][$key]);
					}
				}
			}

			return $data;
		};

		add_filter('wp_kses_allowed_html', $filter_cb, $priority, 2);
	}

	/**
	 * Remove wpautop for a given post ID.
	 *
	 * @param string $pid
	 */
	static function disable_wpautop_by_pid(string $pid): void {
		if (empty($pid)) return;

		global $post;
		if ($post && $post->ID == $pid) {
			remove_filter('the_content', 'wpautop');
		}
	}
}
