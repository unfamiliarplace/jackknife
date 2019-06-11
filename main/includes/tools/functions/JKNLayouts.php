<?php

/**
 * Provides functions for formatting certain simple layouts.
 */
final class JKNLayouts {

	/**
	 * Return a <ul> or <ol> formatted list for the given items using the given
	 * formatter callback, list tag, and CSS classes for the list and items.
	 *
	 * @param array $items
	 * @param callable $format_cb
	 * @param string $outer_tag
	 * @param string $list_class
	 * @param string $item_class
	 * @return string
	 */
	static function list(array $items, callable $format_cb,
		string $outer_tag='ul', string $list_class='',
		string $item_class=''): string {

		// Get a string of <li> s
		$items_html = '';
		foreach($items as $item) {
			$items_html .= sprintf('<li class="%s">%s</li>', $item_class,
				call_user_func($format_cb, $item));
		}

		// Wrap them in an outer list
		$html = sprintf('<%1$s class="%2$s">%3$s</%1$s>',
			$outer_tag, $list_class, $items_html);

		return $html;
	}

	/**
	 * Return a formatted grid for the given items using the given formatter
	 * callback. If Visual Composer is available, use it, otherwise a table.
	 *
	 * @param array $items
	 * @param int $cols The number of columns in ecah row.
	 * @param callable $format_cb
	 * @param string $grid_class
	 * @param string $row_class
	 * @param string $col_class
	 * @return string
	 */
	static function grid(array $items, int $cols, callable $format_cb,
		$grid_class='', $row_class='', $col_class=''): string {

		// If VC is present, use it
		if (JKNAPI::plugin_dep_met('vc')) {

			// Ensure VC shortcodes are on
			if (class_exists('WPBMap') &&
			    method_exists('WPBMap', 'addAllMappedShortcodes')) {
				WPBMap::addAllMappedShortcodes();
			}

			$grid_open = '<div class="%s">';
			$row_open = '[vc_row el_class="%s"]';
			$col_open = '[vc_column width="1/%s" el_class="%s"]';
			$row_close = '[/vc_row]';
			$col_close = '[/vc_column]';
			$grid_close = '</div>';

			// Otherwise use a table
		} else {

			$grid_open = '<div class="%s">';
			$row_open = '<div class="%s" style="clear: both;">';
			$col_open = '<div style="width: 100/%s%; float: left;" class="%s">';
			$col_close = '</div>';
			$row_close = '</div>';
			$grid_close = '</div>';
		}

		// Format
		$grid_open = sprintf($grid_open, $grid_class);
		$row_open = sprintf($row_open, $row_class);
		$col_open = sprintf($col_open, $cols, $col_class);

		$grid = '';

		// Row counter
		$i = 0;

		foreach($items as $item) {

			// Initiate row
			if ($i % $cols == 0) $grid .= $row_open;

			// Do the column, formatting the item
			$inner = call_user_func($format_cb, $item);
			$grid .= sprintf('%s%s%s', $col_open, $inner, $col_close);

			// End the row if it's at the right time
			if ((($i + 1) % $cols) == 0) $grid .= $row_close;

			$i++;
		}

		// End the row if we didn't have enough groups to end it via the above code
		if ($i % $cols != 0) $grid .= $row_close;

		$html = sprintf('%s%s%s', $grid_open, $grid, $grid_close);
		return do_shortcode($html);
	}
}
