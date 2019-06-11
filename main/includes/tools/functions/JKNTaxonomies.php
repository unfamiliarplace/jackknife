<?php

/**
 * Provides functions for dealing with categories.
 */
final class JKNTaxonomies {

	/**
	 * Return the most specific category for the given post.
	 * (Inspired by Newspaper td_module_slide::get_category.)
	 *
	 * TODO This looks wrong... it's old code and I can't remember how it works.
	 *
	 * @param WP_Post $p
	 * @return null|WP_Term
	 */
	static function get_most_specific_category(WP_Post $p): ?WP_Term {
		$cats = get_the_category($p->ID);
		if (!empty($cats[0])) return $cats[0];
		return null;
	}
}
