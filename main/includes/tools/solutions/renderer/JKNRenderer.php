<?php

/**
 * A basic template for a content renderer. Meant to be extended.
 *
 * Usage:
 *
 *      1. Implement ::content.
 *
 *      2. Implement ::style if you need to add CSS,
 *          and ::kses if you need to add more HTML tags.
 *          For the CSS, you can take advtange of ::cl_main for a unique
 *          CSS class for the main div.
 *
 *      3. Call ::render somewhere in your script.
 *
 *      4. If you're going to be filtered by WP (e.g. when updating a post),
 *          call ::allow_kses before being filtered and ::disallow_kses after
 *          being filtered (to restore normal restrictions).
 */
abstract class JKNRenderer {

	/*
	 * =========================================================================
	 * Override
	 * =========================================================================
	 */

	/**
	 * Return the content.
	 *
	 * @param array $args Any arguments to pass.
	 * @return string
	 */
	protected abstract static function content(array $args=[]): string;


	/*
	 * =========================================================================
	 * Optionally override
	 * =========================================================================
	 */

	/**
	 * Return some CSS for the style of this page.
	 *
	 * @return string
	 */
	protected static function style(): string { return ''; }

	/**
	 * Return some kind of "Sorry, empty!" message.
	 *
	 * @return string
	 */
	protected static function empty(): string { return 'No content found.'; }

	/**
	 * Return an array of [$tag => [$subtag => [$value_1, $value_2...]]
	 * for the HTML tags presumed necessary to generate this page.
	 *
	 * @return array[]
	 */
	protected static function kses(): array { return []; }


	/*
	 * =========================================================================
	 * Do not override
	 * =========================================================================
	 */

	/*
	 * =========================================================================
	 * Rendering
	 * =========================================================================
	 */

	/**
	 * Return the rendered content.
	 *
	 * @param array $args Any arguments to pass to content.
	 * @return string
	 */
	final static function render(array $args=[]): string {

		// The content
		$content = static::content($args);
		if (empty($content)) $content = static::empty();

		// The style
		$style = trim(static::style());
		if (!empty($style)) {
			if (!JKNStrings::starts_with($style, '<style')) {
				$style = JKNCSS::tag($style);
			}
		}

		// All together now
		return sprintf('%s<div class="%s">%s</div>',
			$style, static::cl_main(), $content);
	}

	/**
	 * Return <style>, <script>, and any kses added by an extender.
	 *
	 * @return array[]
	 */
	protected static function all_kses(): array {
		$child_kses = static::kses();
		return array_merge($child_kses, [
			'style'     => ['type' => true],
			'script'    => ['type' => true]
		]);
	}

	/**
	 * Allow the necessary KSes.
	 * This should be done before updating a post with this content.
	 */
	final static function allow_kses(): void {
		foreach(static::all_kses() as $tag => $subtags) {
			JKNEditing::allow_in_kses($tag, $subtags);
		}
	}

	/**
	 * Disallow the necessary KSes.
	 * This should be done after updating a post with this content.
	 */
	final static function disallow_kses(): void {
		foreach(static::all_kses() as $tag => $subtags) {
			JKNEditing::disallow_in_kses($tag, $subtags);
		}
	}


	/*
	 * =========================================================================
	 * Styling
	 * =========================================================================
	 */

	/**
	 * @return string The CSS class for the main div.
	 */
	protected final static function cl_main(): string {
		$me = static::class;
		return sprintf('%s-%s-main', JKNAPI::mid($me), $me);
	}
}
