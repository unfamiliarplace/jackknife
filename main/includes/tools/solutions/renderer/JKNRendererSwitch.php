<?php

/**
 * A variant of a renderer that provides the ability to switch between various
 * kinds of content. It has a select dropdown, and whenever it is switched,
 * the content is reloaded. The initial content is pre-rendered.
 * You can also load a particular option on page ready by using a get parameter.
 *
 * Usage:
 *
 *      1. Implement ::content_option and ::switch_options.
 *
 *      2. Implement ::intro, ::select_note, ::show_on_load, ::preload_options
 *          as desired.
 *
 *      3. As with JKNRenderer, implement ::style and ::kses as desired.
 *
 *      4. As with JKNRenderer, call ::render somewhere in your script.
 *          Note that you can pass argument 'preload_all' with a boolean value;
 *          if this is true, all options will be filled when rendering, rather
 *          than left to be filled by Ajax as the user browses the page.
 *
 *      5. As with JKNRenderer, call ::allow_kses and ::disallow_kses before
 *          and after being filtered by WP. This is not optional since the kses
 *          involved in a Switch renderer are essential to its behaviour.
 *
 */
abstract class JKNRendererSwitch extends JKNRenderer {

	/*
	 * =========================================================================
	 * Override
	 * =========================================================================
	 */

	/**
	 * Return the text content for a given choice.
	 *
	 * @param string $choice
	 * @return string
	 */
	protected abstract static function content_option(string $choice): string;


	/*
	 * =========================================================================
	 * Optionally override
	 * =========================================================================
	 */

	/**
	 * Return the key to use as a GET parameter for the option.
	 *
	 * @return string
	 */
	static function option_key(): string { return 'jknrdro'; }

	/**
	 * Return the text content to go above the switcher, if any.
	 * Empty by default.
	 *
	 * @return string
	 */
	protected static function intro(): string { return ''; }

	/**
	 * Return the text instructions for the switcher, if any.
	 * (E.g. "Select option")
	 *
	 * @return string
	 */
	protected static function select_note(): string { return ''; }

	/**
	 * Return the list of options for the switcher, by default all the volumes
	 * on the main site.
	 *
	 * Optionally override if you want to show something else.
	 *
	 * The format is an array of ['value' => value, 'display' => string].
	 * You can also omit 'display' if you just want to display the value.
	 *
	 * @return string[]
	 */
	protected abstract static function switch_options();

	/**
	 * Return the option to show on load. By default returns the first one.
	 * You can return null if you don't want to show any option on load.
	 *
	 * @return string
	 */
	protected static function show_on_load(): ?string {
		$options = static::_switch_options();
		return reset($options)['value'];
	}

	/**
	 * Return the options to prerender (i.e. without the user having to switch).
	 * By default returns the one shown on load.
	 *
	 * @return string[]
	 */
	protected static function preload_options(): array {
		return [static::show_on_load()];
	}


	/*
	 * =========================================================================
	 * Do not override
	 * =========================================================================
	 */

	/*
	 * =========================================================================
	 * Static variables
	 * =========================================================================
	 */

	protected static $saved_switch_options = null;

	/*
	 * =========================================================================
	 * Constants
	 * =========================================================================
	 */

	// HTML classes
	const cl_main               = 'jkn-rdr-switch-main';
	const cl_above_switcher     = 'jkn-rdr-above-switcher';
	const cl_select             = 'jkn-rdr-switcher';
	const cl_select_note        = 'jkn-rdr-switcher-note';
	const cl_select_dis         = 'jkn-rdr-switcher-disabled';
	const cl_switch_div         = 'jkn-rdr-switch-div';
	const cl_switch_div_hide    = 'jkn-rdr-switch-div-hide';
	const cl_meta_message       = 'jkn-rdr-switch-meta-message';

	// HTML ids
	const id_select             = 'jkn-rdr-switch';
	const id_switch_area        = 'jkn-rdr-switch-content';

	// Javascript details
	const js_path               = 'js/switch_ajax.js';
	const js_handle             = 'jkn_rdr_switch_ajax';
	const js_ver                = '0.000047';

	// Default options
	const choose_option = [
		'value'     => 'jkn-rdr-switch-_choose_',
		'display'   => '-- Select an option --'
	];

	const special_options = [
		'choose'    => self::choose_option['value'],
		'loading'   => 'jkn-rdr-switch-_loading_',
		'error'     => 'jkn-rdr-switch-_error_'
	];

	const messages = [
		'choose'    => 	'Choose an option to load its content.',
		'loading'   =>  'Loading content...',
		'error'     =>  'The selected content could not be loaded.'
	];

	/*
	 * =========================================================================
	 * Ajax
	 * =========================================================================
	 */

	/**
	 * Return the AJAX action with a tag for the static class.
	 * This must be different for each subclass, to avoid overloading actions.
	 */
	protected final static function switch_action(): string {
		return sprintf('jkn_rdr_switch_%s', static::class);
	}

	/**
	 * Hook the switch content action to the hooks.
	 */
	final static function hook_ajax(): void {
		$base_action = static::switch_action();
		$action = JKNAjax::qualify_action($base_action);
		$nopriv = JKNAjax::qualify_nopriv_action($base_action);
		add_action($action, [static::class, 'ajax_switch_content']);
		add_action($nopriv, [static::class, 'ajax_switch_content']);
	}

	/**
	 * AJAX: Switch the content, i.e. send_json the content for a given choice.
	 */
	final static function ajax_switch_content(): void {
		$option = $_POST['option'];

		// If it doesn't validate, send an error.
		if (!static::validate_option($option)) {
			wp_send_json('-1');

		// Otherwise, renderer a year and return that content.
		} else {
			$content = static::content_option($option);
			wp_send_json($content);
		}
	}

	/**
	 * Return true iff a given choice is valid. A choice is valid if it
	 * corresponds to an existing option. This is because frontend AJAX allows
	 * arbitrary strings to come in from users who are not logged in.
	 *
	 * @param string $data
	 * @return bool
	 */
	protected final static function validate_option(string $data): bool {

		// Data is defined as OK if it matches the value of one of the options
		$options = static::_switch_options();
		foreach($options as $option) {
			if ($option['value'] == $data) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Enqueue the JS for the switcher.
	 *
	 * @param string $pid The post ID to enqueue it on.
	 */
	final static function enqueue_js(string $pid): void {

		add_action('wp_enqueue_scripts',
			function(string $hook) use($pid): void {

				// Short-circuit if this is an admin page
				if (is_admin()) return;

				// Short-circuit if not on the right page
				global $post;
				if (empty($post) || ($post->ID != $pid)) return;

				// Enqueue the script
				wp_enqueue_script(static::js_handle,
					plugins_url(static::js_path, __FILE__),
					$deps=['jquery'],
					$ver=static::js_ver,
					$in_footer=true
				);

				// Need to get the choose option in here if no show on load
				$opts_to_ids = static::options_to_div_ids();
				if (is_null(static::show_on_load())) {
					$choose = self::special_options['choose'];
					$opts_to_ids[$choose] = $choose;
				}

				// Localize it
				$localizations = [
					'ajaxurl'           => admin_url('admin-ajax.php'),
					'switch_action'     => static::switch_action(),
					'get_key'           => static::option_key(),
					'opts_to_ids'       => $opts_to_ids,
					'special_options'   => self::special_options,
					'id_select'         => static::id_select,
					'id_switch_area'    => static::id_switch_area,
					'cl_select_dis'     => static::cl_select_dis,
					'cl_switch_div'     => static::cl_switch_div,
					'cl_hide'           => static::cl_switch_div_hide
				];

				wp_localize_script(static::js_handle, 'JKNRendererSwitch',
					$localizations);
			}
		);
	}


	/*
	 * =========================================================================
	 * Rendering
	 * =========================================================================
	 */

	/**
	 * Return the memorized switch options (get them first if not memorized).
	 *
	 * @return string[]
	 */
	protected static function _switch_options(): array {
		if (is_null(static::$saved_switch_options)) {
			static::$saved_switch_options = static::switch_options();
		}

		return static::$saved_switch_options;
	}

	/**
	 * Return an array of [$tag => [$subtag => [$value_1, $value_2...]]
	 * for the HTML tags presumed necessary to generate this page.
	 *
	 * @return array[]
	 */
	protected final static function all_kses(): array {
		$parent_kses = parent::all_kses();
		$child_kses = static::kses();
		return array_merge($parent_kses, $child_kses, [
			'option'    => ['value'     => true],
			'select'    => ['disabled'  => true,
			                'name'      => true,
			                'id'        => true,
			                'class'     => true,
			                'onchange'  => true]
		]);
	}

	/**
	 * Return a table of [option_value => sanitized_div_id] for all options.
	 *
	 * @return string[]
	 */
	protected final static function options_to_div_ids(): array {
		$table = [];

		// Sanitize each option value for a div id
		foreach(static::_switch_options() as $option) {
			$val = $option['value'];
			$table[$val] = sprintf('jkn-rdr-%s', JKNStrings::sanitize($val));
		}

		return $table;
	}

	/**
	 * Return the page content and insert both switcher and page-related style.
	 *
	 * @param array $args
	 * @return string
	 */
	protected final static function content(array $args=[]): string {

		// Extract preload_all argument
		if (isset($args['preload_all'])) {
			$preload_all = (bool) $args['preload_all'];
		} else {
			$preload_all = false;
		}

		$switch_area = static::switch_area($preload_all);

		// Format all
		return sprintf('%s%s%s%s', static::switch_style(),
			static::above_switcher(), static::switcher(), $switch_area);
	}

	/**
	 * Return the content above the switcher.
	 *
	 * @return string
	 */
	protected final static function above_switcher(): string {
		return sprintf('<div class="%s">%s</div>',
			static::cl_above_switcher, static::intro());
	}

	/**
	 * Return the switcher div.
	 *
	 * @return string
	 */
	protected final static function switcher(): string {
		$html = '';

		$options = static::_switch_options();

		$show_on_load = static::show_on_load();
		if (is_null($show_on_load)) {
			$show_on_load = self::choose_option;
			array_unshift($options, $show_on_load);
		}

		// Gather the options as the interior of a select field
		$options_html = '';
		foreach($options as $opt) {

			$value = $opt['value'];
			$display = (isset($opt['display'])) ? $opt['display'] : $value;
			$selected = ($opt['value'] == static::show_on_load()) ? 'selected' : '';

			$options_html .= sprintf(
				'<option value="%s" %s>%s</option>',
				$value, $selected, $display);
		}


		// Format the select field
		$select_html = sprintf(
			'<select id="%s">%s</select>', static::id_select, $options_html);

		// Format the select note
		$select_note = static::select_note();
		if (!empty($select_note)) {
			$select_note = sprintf('<span class="%s">%s</span>',
				static::cl_select_note, $select_note);
		}

		// Format the div
		$html .= sprintf('<div class="%s">%s%s</div>',
			static::cl_select, $select_note, $select_html);

		return $html;
	}


	/**
	 * Return the content area.
	 *
	 * @param bool $preload_all Whether to preload all the options at once.
	 * @return string
	 */
	protected final static function switch_area(bool $preload_all) {

		// Get the option to ID table and the option to show on load
		$opt_to_id = static::options_to_div_ids();
		$show_on_load = static::show_on_load();
		if (empty($show_on_load)) $show_on_load = 'choose';

		// Get the options to preload
		if ($preload_all) {
			$preload = array_keys($opt_to_id);
		} else {
			$preload = static::preload_options();
		}

		// If the show on load option isn't preloaded, put it there
		if (!in_array($show_on_load, $preload)) $preload[] = $show_on_load;

		// Format the content area
		$divs = static::switch_divs($opt_to_id, $show_on_load, $preload);
		return sprintf('<div class="%s">%s</div>', static::id_switch_area,
			implode('', $divs));
	}


	/**
	 * Return an array of switch divs corresponding to the available options
	 * They will have content if they are set to preload or are the one set to
	 * show on load. The one set to show on load will be visible; all others
	 * will be hidden. (If none is set to show on load, use the first option.)
	 *
	 * @param array $opt_to_id
	 * @param null|string $show_on_load
	 * @param array $preload The options to preload.
	 * @return array
	 */
	protected final static function switch_divs(array $opt_to_id,
			?string $show_on_load, array $preload): array {

		$divs = [];

		// Fill out the opt to id array with special opts
		$opt_to_id = self::special_options + $opt_to_id;

		// Compile each div
		foreach($opt_to_id as $opt => $id) {

			// Special option
			if (in_array($opt, array_keys(static::special_options))) {
				$content = self::messages[$opt];
				$cl_meta = self::cl_meta_message;

			// Get its content if it's preloaded
			} elseif (in_array($opt, $preload)) {
				$content = static::content_option($opt);
				$cl_meta = '';

			// Otherwise empty
			} else {
				$content = '';
				$cl_meta = '';
			}

			// Set a hide class if it's not the one to show on load
			$cl_hide = ($opt == $show_on_load) ? '' : self::cl_switch_div_hide;

			$divs[] = sprintf('<div class="%s %s %s" id="%s">%s</div>',
				static::cl_switch_div, $cl_hide, $cl_meta, $id, $content);
		}

		return $divs;
	}

	/**
	 * Return the style related to the dropdown and switchable div.
	 *
	 * @return string
	 */
	protected final static function switch_style(): string {
		return JKNCSS::tag('
            
            .'.self::cl_above_switcher.':empty {
                display: none;
            }
            
            .'.self::cl_above_switcher.' {
                margin-bottom: 10px;
            }
                
            .'.self::cl_select.' {
                font-size: 17px;
                padding: 10px 0;
                margin-bottom: 10px;
                border-top: 1px solid #ddd;
                border-bottom: 1px solid #ddd;
                text-align: center;
            }

            .'.self::cl_select_note.' {
                margin-right: 7px;
                font-style: italic;
            }

            #'.self::id_select.' {
                border: none;
                border-bottom: 1px solid #bbb;
            }

            .'.self::cl_select_dis.' {
                color: #777;
                background-color: #ededed;
            }
            
            .'.self::cl_meta_message.' {
				background-color: #ededed;
				text-align: center;
				padding-top: 5%;
				padding-bottom: 5%;
				font-size: 17px;
				font-style: italic;
				margin-bottom: 10px;
            }
            
            .'.self::cl_switch_div_hide.' {
                display: none;
            }
        ');
	}
}
