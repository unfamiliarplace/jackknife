<?php

/**
 * A module is the basic unit of Jackknife.
 * 
 * It mainly connects the ideas of an id, name and description; any settings
 * pages associated with it; the dependencies it needs to run; an activation
 * status; and actions to perform on activation or a few select hooks.
 */
abstract class JKNModule {
    
    /*
     * =========================================================================
     * Override
     * =========================================================================
     */

	/**
	 * Return this module's unique ID (also called module ID or mid).
	 *
	 * @return string ID.
	 */
    abstract function id(): string;

	/**
	 * Return this module's name.
	 *
	 * @return string The name.
	 */
    abstract function name(): string;

	/**
	 * Return the module's description.
	 *
	 * @return string The description.
	 */
    abstract function description(): string;

    /**
     * Perform the module's main actions.
     * This takes place on the plugins_loaded hook.
     */
    protected abstract function run_on_startup(): void;
    
    
    /*
     * =========================================================================
     * Optionally override
     * =========================================================================
     */

    /*
     * =========================================================================
     * Module lifecycle
     * =========================================================================
     * A module is loaded when it is constructed.
     *      Use this for non-dependent behaviour, such as preliminary includes.
     * A module is activated when it is switched ON from OFF.
     *      Use this to do one-time actions such as flush_rewrite_rules.
     * A module is started up as long as it is ON and meets its dependencies.
     *      Use this to do your main actions.
     * A module is resumed when it is switched ON from PAUSE.
     *      Use this to undo pausing.
     * A module is paused when it is switched PAUSE from ON.
     *      Use this to 'shelve' any behaviour, such as turning off cron jobs.
     * A module is shut down as long as it was ON at startup.
     *      Use this to clean up short-term data, restore states, etc.
     * A module is deactivated when it is switched OFF from ON,
     *      its plugin is deactivated, or Jackknife is deactivated.
     *      Use this to clean up long-term data.
     * A module is uninstalled when its plugin or Jackknife is uninstalled.
     *      Use this to eliminate database options, clear directories, etc.
     * =========================================================================
     */

    /**
     * Perform actions on module construction, e.g. includes.
     * N.B. Do not do put dependent behaviour here. Modules will always load
     * even if they are not on, do not meet dependencies and do not start up.
     */
    protected function run_on_load(): void {}

    /**
     * Perform actions on activation, i.e. when switching on.
     * This should be for one-time actions.
     */
    protected function run_on_activate(): void {}

    /**
     * Perform actions on resuming, i.e. when coming back from pause.
     */
    protected function run_on_resume(): void {}

    /**
     * Perform actions on pausing, e.g. when dependencies are suddenly unmet.
     * Note that the module will NOT automatically be started up.
     * Therefore, try not to depend on any module behaviour at this juncture.
     */
    protected function run_on_pause(): void {}

    /**
     * Perform actions on deactivation, i.e. when switching off.
     * Note that the module will NOT automatically be started up.
     * Therefore, try not to depend on any module behaviour at this juncture.
     */
    protected function run_on_deactivate(): void {}

    /**
     * Perform actions on deletion, i.e. when Jackknife is uninstalld.
     * Note that the module will NOT automatically be started up.
     * Therefore, try not to depend on any module behaviour at this juncture.
     */
    protected function run_on_uninstall(): void {}

	/**
	 * Perform actions on shutdown, i.e. at the end of a request.
	 */
	protected function run_on_shutdown(): void {}

    /*
     * =========================================================================
     * Run on WordPress hooks
     * =========================================================================
     */
    
    /**
     * Perform any actions on the init hook.
     */
    function run_on_init(): void {}
    
    /**
     * Perform any actions on the admin_init hook.
     */
    function run_on_admin_init(): void {}
    
    /**
     * Perform any actions on the wp_loaded hook.
     */
    function run_on_wp_loaded(): void {}
    
    
    /*
     * =========================================================================
     * Don't override but do use
     * =========================================================================
     */
    
    /*
     * =========================================================================
     * Creation
     * =========================================================================
     */

	/**
	 * Set this module's base file and space. Also add it to the space, load it,
	 * and set the deactivation and uninstallation behaviour.
	 *
	 * @param JKNSpace $space The space for this module.
	 */
    final function __construct(JKNSpace $space) {
	    $this->base_file   = JKNAPI::file($this);
	    $this->plugin_file = JKNAPI::plugin_from_file($this->base_file);
	    $this->space_id    = $space->id();

	    if (static::class !== 'JKNModulesPage') $space->add_module($this);
	    $this->load();

	    // Register hooks for our plugin being deactivated or uninstalled
	    register_deactivation_hook($this->plugin($abs=false),
		    [$this, 'force_deactivate']);

	    $this->set_uninstallable();
	    add_action(sprintf("uninstall_%s", $this->plugin($abs=false)),
		    [$this, 'force_uninstall']);
    }

	/**
	 * Add a given dependency on another module.
	 *
	 * @param JKNModuleDependency $dep The dependency.
	 * @return JKNModule This module (for chaining).
	 */
    final function add_module_dependency(JKNModuleDependency $dep): JKNModule {
        $this->module_deps[$dep->get_id()] = $dep;
        return $this;
    }

	/**
	 * Add a given dependency on another module.
	 *
	 * @param JKNPluginDependency $dep The dependency.
	 * @return JKNModule This module (for chaining).
	 */
    final function add_plugin_dependency(JKNPluginDependency $dep): JKNModule {
        $this->plugin_deps[$dep->get_id()] = $dep;
        return $this;
    }

	/**
	 * Add a given dependency on another module.
	 *
	 * @param JKNThemeDependency $dep The dependency.
	 * @return JKNModule This module (for chaining).
	 */
    final function add_theme_dependency(JKNThemeDependency $dep): JKNModule {
        $this->theme_deps[$dep->get_id()] = $dep;
        return $this;
    }
    
    
    /*
     * =========================================================================
     * Identification
     * =========================================================================
     */
    
    /**
     * Return the (external) URL of the file that registered this module.
     *
     * @return string The URL.
     */
    final function url(): string { return plugin_dir_url($this->base_file); }
    
    /**
     * Return the (internal) directory of the file that registered this module.
     *
     * @return string The path.
     */
    final function path(): string { return plugin_dir_path($this->base_file); }

	/**
	 * Return the path of the plugin file that this module lives in.
	 * If $abs is true, return the absolute path.
	 * Otherwise return plugin_folder/plugin_file.php.
	 *
	 * @param bool $abs Whether to return the absolute path.
	 * @return string The path.
	 */
	final function plugin(bool $abs=true): string {
		if ($abs) {
			return $this->plugin_file;
		} else {
			return plugin_basename($this->plugin_file);
		}
	}
    
    /**
     * Return the module's space.
     */
    final function space(): JKNSpace {
        return JKNAPI::space_from_sid($this->space_id);
    }
    
    
    /*
     * =========================================================================
     * Settings pages
     * =========================================================================
     */
    
    /**
     * Return the settings pages associated with this module, indexed by ID.
     */
    final function settings_pages(): array {
        $pages = [];
        
        foreach($this->space()->menu()->pages() as $page) {
            if ($page->module() === $this) $pages[$page->id()] = $page;
        }
        
        return $pages;
    }

	/**
	 * Get the settings page of the given ID registered to this module.
	 * If ID is blank, use the default one.
	 *
	 * @param string|null $id The settings page ID.
	 * @return JKNSettingsPage The requested settings page.
	 */
    final function settings_page(string $id=null): JKNSettingsPage {        
        if (empty($id)) $id = JKNSettingsPage::default_id();
        return $this->settings_pages()[$id];
    }
    
    
    /*
     * =========================================================================
     * Internal JKN behaviour: no need to override or use
     * =========================================================================
     */
    
    // Internal properties
	protected $space_id;
	protected $base_file;
	protected $plugin_file;

	protected $module_deps = [];
	protected $plugin_deps = [];
	protected $theme_deps = [];

	protected $is_running =  false;
	protected $forced_off = false;


    /*
     * =========================================================================
     * State
     * =========================================================================
     */

    /**
     * Load this module.
     */
    final function load(): void {
        $this->run_on_load();
    }

    /**
     * Start up this module.
     */
    final function start_up(): void {
    	if ($this->is_running) return;

        $this->run_on_startup();

        add_action('init', [$this, 'run_on_init']);
        add_action('admin_init', [$this, 'run_on_admin_init']);
        add_action('wp_loaded', [$this, 'run_on_wp_loaded']);

        $this->is_running = true;
    }

    /**
     * Activate this module.
     */
    final function activate(): void {
        $this->run_on_activate();
    }

    /**
     * Resume this module.
     */
    final function resume(): void {
        $this->run_on_resume();
    }

    /**
     * Pause this module.
     */
    final function pause(): void {
        $this->run_on_pause();
    }

    /**
     * Shut down this module.
     */
    final function shut_down(): void {
	    if (!$this->is_running) return;
        $this->run_on_shutdown();
        $this->is_running = false;
    }

    /**
     * Deactivate this module.
     */
    final function deactivate(): void {
        $this->run_on_deactivate();
    }

	/**
	 * Deactivate due to JKN's or the module's plugin's deactivation.
	 */
	final function force_deactivate(): void {
		$this->forced_off = true;
		$this->save_state();
		$this->deactivate();
	}

    /**
     * Uninstall this module.
     */
    final function uninstall(): void {
        $this->run_on_uninstall();
        $this->delete_mode_option();
        $this->delete_state_option();
    }

	/**
	 * Uninstall due to JKN's or the module's plugin's deactivation.
	 */
	final function force_uninstall(): void {
		$this->forced_off = true;
		$this->save_state();
		$this->uninstall();
	}

	/**
	 * Return true iff this module is between startup and shutdown.
	 *
	 * @return bool Whether this module is running.
	 */
	final function is_running(): bool { return $this->is_running; }

    
    /*
     * =========================================================================
     * Dependencies
     * =========================================================================
     */

	/**
	 * Return true iff this module depends on the given other module.
	 *
	 * @param JKNModule $other The other module.
	 * @return bool Whether this module depends on the other.
	 */
    final function depends_on(JKNModule $other): bool {
        return in_array($other->id(), array_keys($this->module_deps));
    }

	/**
	 * Return this module's module dependencies.
	 *
	 * @return JKNModuleDependency[] The module dependencies.
	 */
	final function module_dependencies(): array { return $this->module_deps; }

	/**
	 * Return this module's plugin dependencies.
	 *
	 * @return JKNPluginDependency[] The plugin dependencies.
	 */
	final function plugin_dependencies(): array { return $this->plugin_deps; }

	/**
	 * Return this module's theme dependencies.
	 *
	 * @return JKNThemeDependency[] The theme dependencies.
	 */
	final function theme_dependencies(): array { return $this->theme_deps; }

	/**
	 * Return the given dependencies filtered by whether they are unmet.
	 *
	 * @param JKNDependency[] $deps The dependencies to filter.
	 * @return JKNDependency[] Those that are not met.
	 */
    protected final function filter_unmet_dependencies(array $deps): array{
    	$filter = function(JKNDependency $dep): bool {
    		return !$dep->met();
	    };

    	return array_filter($deps, $filter);
    }

    /**
     * Return a flat array of this module's unmet module dependencies.
     */
    final function unmet_module_dependencies(): array {
    	return $this->filter_unmet_dependencies($this->module_deps);
    }

    /**
     * Return a flat array of this module's unmet plugin dependencies.
     */
    final function unmet_plugin_dependencies(): array {
	    return $this->filter_unmet_dependencies($this->plugin_deps);
    }

    /**
     * Return a flat array of this module's unmet theme dependencies.
     */
    final function unmet_theme_dependencies(): array {
	    return $this->filter_unmet_dependencies($this->theme_deps);
    }
    
    /**
     * Return a flat array of this module's unmet dependencies.
     */
    final function unmet_dependencies(): array {
        return array_merge(
            $this->unmet_module_dependencies(),
            $this->unmet_plugin_dependencies(),
            $this->unmet_theme_dependencies()
        );
    }

    /**
     * Return true iff this module meets all its dependencies.
     *
     * @return bool Whether this module meets all its dependencies.
     */
    final function meets_dependencies(): bool {
        return empty($this->unmet_dependencies());
    }


    /*
     * =========================================================================
     * States
     * =========================================================================
     */

    /**
     * Determine and return this module's state.
     *
     * @return int The state (a constant of JKNLifecycle).
     */
    protected final function determine_state(): int {

        $mode = $this->mode();
        $meets_deps = $this->meets_dependencies();

        $state = JKNLifecycle::UNKNOWN;

        // This is the state after JKN or this module's plugin
	    // have been deactivated or uninstalled.
        if ($this->forced_off) {
        	$state = JKNLifecycle::FORCED_OFF;

		} elseif ($mode == JKNMode::OFF) {
        	if (!$meets_deps) $state = JKNLifecycle::OFF_CANNOT_ACTIVATE;
        	else $state = JKNLifecycle::OFF_CAN_ACTIVATE;

        } elseif ($mode == JKNMode::PAUSE) {
	        if (!$meets_deps) $state = JKNLifecycle::PAUSE_CANNOT_ACTIVATE;
	        else $state = JKNLifecycle::PAUSE_CAN_ACTIVATE;

        } elseif ($mode == JKNMode::ON) {
	        if (!$meets_deps) $state = JKNLifecycle::PAUSE_CANNOT_ACTIVATE;
	        else $state = JKNLifecycle::ON_CAN_ACTIVATE;
        }

        return $state;
    }

    /**
     * Determine and return the current state of this module.
     * Public wrapper for $this::determine_state.
     *
     * @return int The current state of this module.
     */
    final function current_state(): int {
        return $this->determine_state();
    }

    /**
     * Save this module's currentstate to the database.
     * Return true iff the update took place.
     *
     * @return bool Whether the update occurred.
     */
    final function save_state(): bool {
        $opt = $this->state_option_id();
        return $this->modules_page()->update($opt, $this->determine_state());
    }

    /**
     * Return this module's state as saved in the database.
     *
     * @return int The state saved in the database. Defaults to unknown.
     */
    final function saved_state(): int {
        $opt = $this->state_option_id();
        return (int) $this->modules_page()->get($opt, JKNLifecycle::UNKNOWN);
    }

    
    /*
     * =========================================================================
     * Qualification
     * =========================================================================
     */
    
    /**
     * Return the qualification prefix for this module and namespace.
     *
     * @return string The prefix.
     */
    final function prefix(): string {
        return $this->space()->qualify($this->id());
    }

	/**
	 * Return the given name qualified by the module's namespace and ID.
	 *
	 * @param string $name The name to qualify.
	 * @return string The qualified name.
	 */
    final function qualify(string $name): string {
        return sprintf('%s_%s', $this->prefix(), $name);
    }

	/**
	 * Return the given name qualified by just the module's ID.
	 * N.B. This is used by the modules page, which supplies its own part.
	 *
	 * @param string $name The name to short-qualify.
	 * @return string The short-qualified name.
	 */
    final function short_qualify(string $name): string {
        return sprintf('%s_%s', $this->id(), $name);
    }


    /*
     * =========================================================================
     * Modules settings page interaction
     * =========================================================================
     */

    /**
     * Return the modules settings page for this module's space.
     *
     * @return JKNModulesPageSettings The modules settings page.
     */
    final function modules_page(): JKNModulesPageSettings {
        return $this->space()->menu()->top();
    }

    /**
     * Return the 'mode' option name.
     * N.B. This is used by the modules page, which adds its own qualification.
     *
     * @return string The 'mode' option name.
     */
    final function mode_option_id(): string {
        return sprintf('%s_mode', $this->id());
    }

    /**
     * Return the 'state' option name.
     * N.B. This is used by the modules page, which adds its own qualification.
     *
     * @return string The 'state' option name.
     */
    final function state_option_id(): string {
        return sprintf('%s_state', $this->id());
    }

    /**
     * Return the value of the 'is on' option.
     *
     * @return int The mode, a constant of JKNMode. Defaults to ON.
     */
    final function mode(): int {
        $opt = $this->mode_option_id();
        return $this->modules_page()->get($opt, JKNMode::ON);
    }

    /**
     * Delete the 'mode' option.
     *
     * @return bool Whether the option was deleted.
     */
    protected final function delete_mode_option(): bool {
        $opt = $this->mode_option_id();
        return $this->modules_page()->delete($opt);
    }

    /**
     * Delete the 'state' option.
     *
     * @return bool Whether the option was deleted.
     */
    protected final function delete_state_option(): bool {
        $opt = $this->state_option_id();
        return $this->modules_page()->delete($opt);
    }


	/*
	 * =========================================================================
	 * Uninstallation
	 * =========================================================================
	 */

	/**
	 * Update the WP uninstallable plugins option to ensure this module's plugin
	 * is uninstallable. This is so that WP uninstall_plugin will be run at all.
	 *
	 * N.B. Only one function can be hooked onto this option itself. Therefore
	 * we use a dummy. The actual actions we want to run are manually added.
	 *
	 * @return bool If the option was updated.
	 */
	protected final function set_uninstallable(): bool {

		$pfile = $this->plugin($abs=false);
		$ups = get_option('uninstall_plugins', []);

		// If it's already set, don't overwrite it; the writer of this function
		// could have intended it to be uninstallable with another callback.
		if (isset($ups[$pfile])) {
			return false;

		// Otherwise, use time as a dummy function.
		} else {
			$ups[$pfile] = ['time'];
			return update_option('uninstall_plugins', $ups);
		}
	}


    /*
     * =========================================================================
     * Module option qualification (qualification sans settings page)
     * =========================================================================
     */

	/**
	 * Get the value of the given option qualified by the module.
	 *
	 * @param string $opt The option name.
	 * @param bool $default The value to return if the option is not set.
	 * @return mixed The value of the option.
	 */
    final function get(string $opt, $default=false) {
        return get_option($this->qualify($opt), $default);
    }

	/**
	 * Update the value of the given option with the given value.
	 * Set autoload if requested.
	 * Return true iff the value was successfully updated.
	 *
	 * @param string $opt The option name.
	 * @param mixed $value The value to set.
	 * @param bool $autoload Whether to load this option on WP load.
	 * @return bool Whether the option was updated.
	 */
    final function update(string $opt, $value, bool $autoload=false): bool {
        return update_option($this->qualify($opt), $value, $autoload);
    }

	/**
	 * Delete the value of the given option.
	 * Return iff it it was deleted.
	 *
	 * @param string $opt
	 * @return bool Whether the option was deleted.
	 */
    final function delete(string $opt): bool {
        return delete_option($this->qualify($opt));
    }
}
