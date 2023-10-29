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
	 *
	 * @return string
	 */
	static function list(
		array $items, callable $format_cb,
		string $outer_tag = 'ul', string $list_class = '',
		string $item_class = ''
	): string {

		// Get a string of <li> s
		$items_html = '';
		foreach ( $items as $item ) {
			$items_html .= sprintf( '<li class="%s">%s</li>', $item_class,
				call_user_func( $format_cb, $item ) );
		}

		// Wrap them in an outer list
		$html = sprintf( '<%1$s class="%2$s">%3$s</%1$s>',
			$outer_tag, $list_class, $items_html );

		return $html;
	}

	/**
	 * Return a formatted grid for the given items using the given formatter
	 * callback. If Visual Composer is available, use it (iff $try_vc),
	 * otherwise a table.
	 *
	 * @param array $items
	 * @param int $n_cols
	 * @param callable $format_cb
	 * @param string $grid_class
	 * @param string $row_class
	 * @param string $col_class
	 * @param bool $is_inner
	 * @param bool $try_vc
	 *
	 * @return string
	 */
	static function grid(
		array $items, int $n_cols, callable $format_cb,
		string $grid_class = '', string $row_class = '', string $col_class = '',
		bool $is_inner = false, bool $try_vc = true
	): string {

		// If VC is present, use it
		if ( $try_vc && JKNAPI::plugin_dep_met( 'vc' ) ) {

			// Ensure VC shortcodes are on
			if ( class_exists( 'WPBMap' ) &&
			     method_exists( 'WPBMap', 'addAllMappedShortcodes' ) ) {
				WPBMap::addAllMappedShortcodes();
			}

			$inner = $is_inner ? '_inner' : '';

			$grid_open  = '<div class="%s">';
			$row_open   = sprintf( '[vc_row%s', $inner ) . ' el_class="%s"]';
			$col_open   = sprintf( '[vc_column%s', $inner ) . ' width="1/%s" el_class="%s"]';
			$row_close  = sprintf( '[/vc_row%s]', $inner );
			$col_close  = sprintf( '[/vc_column%s]', $inner );
			$grid_close = '</div>';

			//JKNDebugging::autopsy($row_open);

			// Otherwise use a table
		} else {

			$grid_open  = '<div class="%s">';
			$row_open   = '<div class="%s" style="clear: both;">';
			$col_open   = '<div style="width: 100/%s%; float: left;" class="%s">';
			$col_close  = '</div>';
			$row_close  = '</div>';
			$grid_close = '</div>';
		}

		// Format
		$grid_open = sprintf( $grid_open, $grid_class );
		$row_open  = sprintf( $row_open, $row_class );
		$col_open  = sprintf( $col_open, $n_cols, $col_class );

		$grid = '';

		// Row counter
		$i = 0;

		foreach ( $items as $item ) {

			// Initiate row
			if ( $i % $n_cols == 0 ) {
				$grid .= $row_open;
			}

			// Do the column, formatting the item
			$content = call_user_func( $format_cb, $item );
			$grid    .= sprintf( '%s%s%s', $col_open, $content, $col_close );

			// End the row if it's at the right time
			if ( ( ( $i + 1 ) % $n_cols ) == 0 ) {
				$grid .= $row_close;
			}

			$i ++;
		}

		// End the row if we didn't have enough groups to end it via the above code
		if ( $i % $n_cols != 0 ) {
			$grid .= $row_close;
		}

		$html = sprintf( '%s%s%s', $grid_open, $grid, $grid_close );

		return do_shortcode( $html );
	}

	/**
	 *
	 * Return a formatted flex div for the given items using the given formatter callback.
	 *
	 * @param array $items
	 * @param callable $format_cb
	 * @param string $grid_class
	 * @param string $item_class
	 *
	 * @return string
	 */
	static function flex(
		array $items, callable $format_cb,
		string $flex_class = '', string $item_class = ''
	): string {

		$style_grid = "display: flex; flex-direction: row; justify-content: center; flex-wrap: wrap;";
		$style_item = "display: flex; flex-direction: column; justify-content: center;";

		$inner = "";
		foreach ($items as $item) {
			$f = $format_cb($item);
			$inner .= "<div style='{$style_item}' class='{$item_class}'>{$f}</div>";
		}

		return "<div style='{$style_grid}' class='{$flex_class}'>{$inner}</div>";
	}
}