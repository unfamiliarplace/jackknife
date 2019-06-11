<?php

/**
 * Adds the settings page for the toplevel page. This page is where you can
 * view the status of all the modules and turn them on or off.
 * 
 * It works a little differently from our normal settings pages in that it
 * does not have a module or a settings page ID at its base.
 */
final class JKNModulesPageSettings extends JKNSettingsPageWP {
    
    /*
     * =========================================================================
     * CSS classes
     * =========================================================================
     */
    
    const cl_intro = 'jkn-modules-intro';
    const id_status_legend = 'jkn-modules-status-legend';
    const id_mods = 'jkn-modules-mods';
    const id_mod_legend = 'jkn-modules-mod-legend';

    const cl_mod = 'jkn-modules-mod';
    const cl_mod_status = 'jkn-modules-mod-status';
    const cl_mod_name = 'jkn-modules-mod-name';
    const cl_mod_desc = 'jkn-modules-mod-desc';
    const cl_mod_deps = 'jkn-modules-mod-deps';

    const cl_mod_on = 'jkn-modules-mod-on';
    const cl_mod_pause_can = 'jkn-modules-mod-pause-can';
    const cl_mod_pause_cannot = 'jkn-modules-mod-pause-cannot';
    const cl_mod_off_can = 'jkn-modules-mod-off-can';
	const cl_mod_off_cannot = 'jkn-modules-mod-off-cannot';

    const cl_dep = 'jkn-modules-dep';
    const cl_dep_met = 'jkn-modules-dep-on';
    const cl_dep_unmet = 'jkn-modules-dep-off';

    const state_to_colour = [
	    JKNLifecycle::ON_CAN_ACTIVATE       => self::cl_mod_on,
	    JKNLifecycle::PAUSE_CAN_ACTIVATE    => self::cl_mod_pause_can,
	    JKNLifecycle::PAUSE_CANNOT_ACTIVATE => self::cl_mod_pause_cannot,
	    JKNLifecycle::OFF_CAN_ACTIVATE      => self::cl_mod_off_can,
	    JKNLifecycle::OFF_CANNOT_ACTIVATE   => self::cl_mod_off_cannot
    ];
    
    /*
     * =========================================================================
     * Overridden
     * =========================================================================
     */
    
    /**
     * Return the name.
     *
     * @return string The name.
     */
    function name(): string { return 'Switchboard'; }
    
    /**
     * Return the menu title.
     *
     * @return string The menu title.
     */
    function menu_title(): string { return 'Switchboard'; }
    
    /**
     * Set the default hook. The toplevel one is not derived in the normal way.
     *
     * @return string The default hook.
     */
    function default_hook(): string {
        return sprintf('toplevel_page_%s', $this->slug());
    }
    
    /**
     * Return the page title. This is formatted differently from the others.
     *
     * @return string The page title.
     */
    function page_title(): string {
        return sprintf('%s: %s', $this->space()->name(), $this->name());
    }    
    
    /**
     * Return the qualification prefix for this page.
     * Unlike a normal settings page, this omits an ID for this settings page.
     *
     * @return string The prefix: the module's prefix with the JKN ID prepended.
     */
    protected final function prefix(): string {
        return JKNOpts::prefix_jkn($this->module()->prefix());
    }

	/**
	 * Return the names of all the modules' 'is on' options.
	 * The 'last seen' ones are not necessary because they are not user-facing
	 * options. They are our internal status monitor.
	 *
	 * @return string[] The option names.
	 */
    protected function option_names(): array {
        
        $options = [];
        foreach($this->space()->modules() as $module) {
            
            // We just use the suffix, not qualify, because the registration
            // from parent already qualifies the option name. 
            $options[] = $module->mode_option_id();
        }
        
        return $options;
    }

    /**
     * Add one section and each module as a field.
     */
    function add_sections_and_fields(): void {

        // Add the one settings section on this page	
        add_settings_section(
            $id = 'jkn_switchboard_modules',
            $title = '',
            $callback = function() {},
            $page = $this->slug()
        );

        // Fetch and add the module settings fields
            
        $modules = JKNLifecycle::order_modules($this->space()->modules());
        foreach ($modules as $module) {

            // Each module has a unique name for its 'on' option
            $mode_option_id = $this->qualify($module->mode_option_id());

            // The setting for each module 'on' option
            add_settings_field(
                $id = $mode_option_id,
                $title = $module->name(),
                $callback = [$this, 'output_module'],
                $page = $this->slug(),
                $section = 'jkn_switchboard_modules',
                $args = ['module' => $module]
            );
        }
    }

    
    /*
     * =========================================================================
     * Formatting
     * =========================================================================
     */
    
    /**
     * Output the intro for this page.
     */
    protected function output_intro(): void {

        $intro = sprintf('<h1>%s</h1>', $this->page_title());
        $expl = 'You can activate, pause, or deactivate each module here.'
	            . ' (A paused module is "suspended" and can be resumed.)<br>'
	            . ' The left column is colour-coded for the module\'s status.'
	            . ' Because of missing dependencies, a module\'s status may not'
	            . ' always correspond to the desired setting.<br>'
	            . ' Modules are presented in load order.<br>';
        $intro .= sprintf('<div class="%s"></p>%s</p></div>',
                self::cl_intro, $expl);
        
        echo $intro;
        $this->output_mod_div_open();
	    $this->output_status_legend();
	    $this->output_mod_legend();
    }
    
    /**
     * Output the outtro for this page.
     */
    protected function output_outtro(): void { $this->output_mod_div_close(); }
    
    /**
     * Output the opening of the modules div.
     */
	private function output_mod_div_open(): void {
        printf('<div id="%s">', self::id_mods);
    }
    
    /**
     * Output the closing of the modules div.
     */
	private function output_mod_div_close(): void { echo '</div>'; }
    
    /**
     * Output the HTML for the module status legend.
     */
	private function output_status_legend(): void {
        $cells = '';
        
        $base_cell = '<td class="%s"><span>%s</span></td>';
        //$cells .= sprintf($base_cell, self::cl_mod_status,
	    //    'State');
        $cells .= sprintf($base_cell, self::cl_mod_on,
            'Turned on.');
        $cells .= sprintf($base_cell, self::cl_mod_pause_can,
            'Paused. Can be turned on.');
	    $cells .= sprintf($base_cell, self::cl_mod_pause_cannot,
	        'Paused. Cannot be turned on due to missing dependencies.');
        $cells .= sprintf($base_cell, self::cl_mod_off_can,
            "Off. Can be turned on.");
        $cells .= sprintf($base_cell, self::cl_mod_off_cannot,
            'Off. Cannot be turned on due to missing dependencies.');
        
        printf('<table id="%s"><tr>%s</tr></table>',
			self::id_status_legend, $cells);
    }
    
    /**
     * Output the module table start.
     */
	private function output_mod_legend(): void {
        
        $status = sprintf('<td class="%s">Status</td>', self::cl_mod_status);
        $name = sprintf('<td class="%s">Module</td>', self::cl_mod_name);
        $desc = sprintf('<td class="%s">Description</td>', self::cl_mod_desc);
        
        $mo_deps = sprintf('<td class="%s">Other module dependencies</td>',
                self::cl_mod_deps);
        $pl_deps = sprintf('<td class="%s">Plugin dependencies</td>',
                self::cl_mod_deps);
        $th_deps = sprintf('<td class="%s">Theme dependencies</td>',
                self::cl_mod_deps);
        
        printf('<table class="%s" id="%s">%s%s%s%s%s%s</table>',
                self::cl_mod, self::id_mod_legend,
                $status, $name, $desc, $mo_deps, $pl_deps, $th_deps);
    }

	/**
	 * Output the HTML for an individual module (on) field.
	 * $args contains one argument: 'module', a JKNModule.
	 *
	 * @param JKNModule[] $args An array carrying the module.
	 */
    function output_module(array $args): void {
        
        // Extract key bits
        $module = $args['module'];
        $unmet_deps = $module->unmet_dependencies();
        
        // Prepare the status
	    $mode = $this->render_mode($module);
        $colour_class = $this->get_colour_class($module);
        $status_class = sprintf('%s %s', self::cl_mod_status, $colour_class);
        $status = sprintf('<td class="%s">%s</td>', $status_class, $mode);
        
        // Name
        $name = sprintf('<td class="%s">%s</td>', self::cl_mod_name,
	        $module->name());
        
        // Description
        $desc = sprintf('<td class="%s">%s</td>', self::cl_mod_desc,
	        $module->description());
        
        // Module dependencies
	    $mo_deps = $module->module_dependencies();
        $mo_deps_str = $this->render_deps($mo_deps, $unmet_deps);
        $mo_deps_html = sprintf('<td class="%s">%s</td>', self::cl_mod_deps,
                $mo_deps_str);

        // Plugin dependencies
	    $pl_deps = $module->plugin_dependencies();
        $pl_deps_str = $this->render_deps($pl_deps, $unmet_deps);
        $pl_deps_html = sprintf('<td class="%s">%s</td>', self::cl_mod_deps,
                $pl_deps_str);
        
        // Theme dependencies
	    $th_deps = $module->theme_dependencies();
        $th_deps_str = $this->render_deps($th_deps, $unmet_deps);
        $th_deps_html = sprintf('<td class="%s">%s</td>', self::cl_mod_deps,
                $th_deps_str);
        
        $tr = sprintf('<tr>%s%s%s%s%s%s</tr>', $status, $name, $desc,
                $mo_deps_html, $pl_deps_html, $th_deps_html);
        
        printf('<table class="%s">%s</table>', self::cl_mod, $tr);
    }

	/**
	 * Return the mode switcher for a given module.
	 * This switcher consists of radio buttons for on, pause, and off.
	 *
	 * @param JKNModule $module The module.
	 * @return string The HTML for the mode switcher.
	 */
	private function render_mode(JKNModule $module): string {

		// Extract key variables
		$mode = $module->mode();
		$opt_id = $module->mode_option_id();

		// Get a base radio button going
		$base_radio = '<label><input type="radio" name="%s"value="%s" %s/>'
		              . '&nbsp;%s</label>';

		// Make each button

		$on = sprintf($base_radio, $this->qualify($opt_id), JKNMode::ON,
			checked($mode, JKNMode::ON, false),
			'On');

		$pause = sprintf($base_radio, $this->qualify($opt_id), JKNMode::PAUSE,
			checked($mode, JKNMode::PAUSE, false),
			'Pause');

		$off = sprintf($base_radio, $this->qualify($opt_id), JKNMode::OFF,
			checked($mode, JKNMode::OFF, false), 'Off');

		// Concatenate
		return sprintf('%s<br>%s<br>%s', $on, $pause, $off);
	}

	/**
	 * Return the dependency report for a given set of dependencies.
	 *
	 * @param JKNDependency[] $deps The dependencies to renderer.
	 * @param JKNDependency[] $unmet_deps The unmet dependencies of thee module.
	 * @return string The HTML for the formatted dependencies.
	 */
    private function render_deps(array $deps, array $unmet_deps): string {

        // Bail if there are no dependencies.
        if (empty($deps)) return '';
        
        // Create a report on these dependencies
        $dependency_items = '';
        foreach ($deps as $dep) {
            $met = !in_array($dep, $unmet_deps);
            $dependency_items .= sprintf('<li>%s</li>',
                    $this->render_dep($dep, $met));
        }
        $dependency_report = sprintf('<ul>%s</ul>', $dependency_items);

        return $dependency_report;
    }

	/**
	 * Return the dependency report for a given dependency.
	 *
	 * @param JKNDependency $dep The dependency.
	 * @param bool $met Whether the dependency is met.
	 * @return string The formatted HTML for the dependency.
	 */
    private function render_dep(JKNDependency $dep, bool $met): string {        
        $html = '';
        
        // Determine dependency colour
        $colour_class = ($met) ? self::cl_dep_met : self::cl_dep_unmet;
        
        // Get the dependency's URL
        $url = $dep->get_url();
        
        // Get the text shown: a link if there is a URL, otherwise just text
        $inner = $dep->get_name();
        if (!empty($url)) {
            $inner = sprintf('<a href="%1$s" title="%2$s">%2$s</a>',
                    $url, $inner);
        }

        // Wrap inside a span tag with the appropriate colour
        $html .= sprintf('<span class="%s %s">%s</span>',
                self::cl_dep, $colour_class, $inner);
        
        return $html;
    }

	/**
	 * Return the colour class of a module, based on its state.
	 *
	 * @param JKNModule $module The module.
	 * @return string The class.
	 */
	private function get_colour_class(JKNModule $module): string {
        return self::state_to_colour[$module->current_state()];
    }
    
    /**
     * Output the CSS.
     */
    protected function output_style(): void {
        echo JKNCSS::tag('
        
			#wpcontent {
				background: #41464c;
			}
			
			#wpbody-content {
				padding-bottom: 10px;
			}
			
			#submit {
				width: 80%;
				height: 40px;
			}
			
			p.submit {
				margin-left: auto;
				margin-right: auto;
				text-align: center;
			}
			
			.wrap h1 {
				color: #fdfdfd;
				margin-bottom: 10px;
			    font-size: 28px;
			}
			
			#wpfooter {
				display: none;
			}
            
            #'.self::form_id.' th {
              display: none;
              width: 0;
            }

            h4 {
              font-size: 16px;
              margin-top: 0;
            }

            h5 {
              margin-bottom: 0;
              font-size: 13px;
            }

            .submit {
                margin-left: 10px;
            }
            
            p.submit {
              padding-bottom: 10px;
          }

            .'.self::cl_intro.' {
                background: rgba(255,255,255,.9);
                margin-bottom: 10px;
                padding: 10px 0 10px 10px;
                max-width: 1320px;
            }

            .'.self::cl_intro.' p {
                font-size: 14px;
                color: #000;
                margin: 0;
            }

            #'.self::id_mods.' {
                background: #61656b;
                padding: 5px;
                padding-left: 7px;
                max-width: 1320px;
            }

            #'.self::id_status_legend.' {
                border-spacing: 15px;
                padding: 0px;
                margin-bottom: 0px;
            }
            
            #'.self::id_status_legend.' td {
                height: 15px;
                padding: 10px 20px 10px 20px;
            }
            
            #'.self::id_status_legend.' td span {
				font-weight: 600;
				color: #111;
			}
            
            #'.self::id_mod_legend.' .'.self::cl_mod_status.' {
                background-color: #fff;
            }

            #'.self::id_mod_legend.' {
                font-weight: bold;
                margin-left: 13px;
                margin-top: -5px;
            }

            #'.self::id_mod_legend.' td {
                height: 25px;
            }
            
            #'.self::form_id.' {
                margin-left: 3px;
            }

            #'.self::form_id.' td {
                padding-top: 0px;
                padding-bottom: 0px;
            }

            .'.self::cl_mod.' tr {
                background: #fff;
            }

            .'.self::cl_mod_status.' { 
                text-align: center;
                min-width: 60px;                
                width: 60px;
                color: #000;
            }

            .'.self::cl_mod_status.' input {
                margin: 0;
            }

            .'.self::cl_mod.' {
                border-collapse: separate;
                border-spacing: 2px;
            }

            .'.self::cl_mod.' td {
                padding: 10px !important;
                height: 42px;
            }

            .'.self::cl_mod_name.' {
                min-width: 180px;
                width: 180px;
                font-weight: bold;
            }


            .'.self::cl_mod_desc.' {
                min-width: 300px;
                width: 300px;
            }


            .'.self::cl_mod_deps.' {
                min-width: 205px;
                width: 205px;
            }
            
           .'.self::cl_mod_deps.' ul {
                margin: 0;
            }
            
            .'.self::cl_mod_deps.' li {
              list-style: disc;
              margin-left: 15px;
            }
            
            .'.self::cl_mod_deps.' li:last-child {
                margin-bottom: 0;
            }

            
            .'.self::cl_mod.' th {
                display: initial;
            }

            .'.self::cl_mod_on.' {
                background-color: '.JKNColours::good().' !important;
            }
            
            .'.self::cl_mod_pause_can.' {
                background-color: '.JKNColours::meh().' !important;
            }
            
            .'.self::cl_mod_pause_cannot.' {
                background: url("'
                             .sprintf('%s/images/stripe-pause.gif', JKN_ASSETS).
                             '");
            }
            
            .'.self::cl_mod_off_can.' {
                background-color: '.JKNColours::bad().' !important;
            }
            
            .'.self::cl_mod_off_cannot.' {
                background: url("'
                             .sprintf('%s/images/stripe-off.gif', JKN_ASSETS).
                             '");
            }
            
            .'.self::cl_dep_met.', .'.self::cl_dep_met.' a,
            .'.self::cl_dep_met.' a:hover {
                color: #45a845;
            }
            
            .'.self::cl_dep_unmet.', .'.self::cl_dep_unmet.' a,
            .'.self::cl_dep_unmet.' a:hover {
                color: #c42121;
            }            
        ');
    }
}
