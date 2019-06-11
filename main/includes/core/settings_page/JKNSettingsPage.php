<?php

/**
 * Represent a WP settings page so we can do things with it after adding it.
 * 
 * Some vocabulary for WP settings pages:
 * 
 *      - 'menu_title' is what appears on the WordPress menu item.
 * 
 *      - 'page_title' is what (should) appear on the settigs page itself.
 *          It is also what determines a toplevel page's hook.
 * 
 *      - 'slug' is the link / how the item is identified in the WP menu array.
 *          However, when output it's prefixed by the screen (e.g. 'edit.php').
 * 
 *      - 'hook' is the internal suffix to that page for action purposes
 *          For example, the action for loading page X is 'load-{x's hook}'
 */
abstract class JKNSettingsPage {
    
    protected $module;  // JKNModule: The module
    private $hook;      // string: The WP suffix for hooking page actions
	private $order;     // int: The requested order in the submenu
    
    
   /*
     * =========================================================================
     * Override
     * =========================================================================
     */
    
    /**
     * Add this page to the WP menu.
     */
    abstract function add_page_to_menu(): void;
    
    /**
     * Return an array ['key' => key, 'value' => value] representing the
     * $_GET key and its value that indicate that settings have been updated.
     *
     * @return string[]|null The key/value array.
     */
    abstract protected function get_kv_updated(): ?array;
    
    
    /*
     * =========================================================================
     * Optionally override
     * =========================================================================
     */
    
    /**
     * Return an ID for this settings page, unique to the module.
     * If you don't supply one, a default one will be used.
     * Override if you add more than one settings page to the module.
     *
     * @return string The ID.
     */
    function id(): string { return static::default_id(); }
    
    
    /**
     * Return the name of this page (for composing in the page title).
     *
     * @return string The name.
     */
    function name(): ?string { return null; }
    
    /**
     * Return the menu title.
     *
     * @return string The menu title.
     */
    function menu_title(): string {
	    $name = $this->name();
    	$mod_name = $this->module->name();
    	return is_null($name) ? $mod_name : sprintf('%s: %s', $mod_name, $name);
    }

	/**
	 * Set the module, as well as a default order and hook.
	 *
	 * @param JKNModule $module The module to which this settings page belongs.
	 */
    function __construct(JKNModule $module) {
        $this->module = $module;
        $this->hook = $this->default_hook();
    } 
    
    
    /*
     * =========================================================================
     * Shouldn't have to override
     * =========================================================================
     */

    // Set up
    
    /**
     * Return the default hook. This emulates how WP determines page hooks.
     *
     * @return string The default hook.
     */
    protected function default_hook(): string {
        $top_name = $this->space()->menu()->top_name();
        $top_id = JKNStrings::sanitize($top_name, true, '-');
        return sprintf('%s_page_%s', $top_id, $this->slug());
    }
    
    /**
     * Return the default ID.
     *
     * @return string The default ID.
     */
    final static function default_id(): string { return 's'; }

	/**
	 * Set the hook to the given string (to replace the default).
	 *
	 * @param string $hook The hook to set.
	 */
    final function set_hook(string $hook): void { $this->hook = $hook; }

	/**
	 * Register the add page action to the admin_menu hook.
	 *
	 * @param int $priority
	 */
    final function add_page_add_action(int $priority=10): void {
        add_action('admin_menu', [$this, 'add_page_to_menu'], $priority);
    }
    
    /**
     * Remove this page from the menu.
     */
    final function remove_page_from_menu(): void {
        $top_slug = $this->module->space()->menu()->top_slug();
        remove_submenu_page($top_slug, $this->slug());
    }

	/**
	 * Register the remove page action to the admin menu hook.
	 *
	 * @param int $priority
	 */
    final function add_page_remove_action(int $priority=100): void {
        add_action('admin_menu', [$this, 'remove_page_from_menu'],
	        $priority);
    }

	/**
	 * Set the requested order in the submenu.
	 *
	 * @param int $order
	 */
	final function set_order(int $order): void { $this->order = $order; }

	/**
	 * Get the requested order in the submenu.
	 */
	final function order(): int { return $this->order; }
    
    
    /*
     * =========================================================================
     * Identification
     * =========================================================================
     */
    
    /**
     * Return the space this page belongs to.
     *
     * @return JKNSpace The space of this page's module.
     */
    final function space(): JKNSpace { return $this->module->space(); }
    
    /**
     * Return the module this page belongs to.
     *
     * @return JKNModule This page's module.
     */
    final function module(): JKNModule { return $this->module; }
    
    /**
     * Return the slug for this page. By default the same as the prefix.
     *
     * @return string The page's slug.
     */
    function slug(): string {
        return str_replace('_', '-', $this->prefix());
    }
    
    /**
     * Return the URL for this page.
     *
     * @return string The page's URL.
     */
    function url(): string {
        return admin_url(sprintf('admin.php?page=%s', $this->slug()));
    }
    
    /**
     * Return the WP hook suffix for this page.
     * This cannot reliably be called until after the admin_menu hook,
     * because some settings page types only derive their hook then.
     *
     * @return string The hook suffix.
     */
    function hook(): string { return $this->hook; }
    
    /**
     * Return the WP hook for the action run when this page loads.
     *
     * @return string The page load hook.
     */
    final function load_hook(): string {
        return sprintf('load-%s', $this->hook);        
    }


    /**
     * Schedule a given callback and its arguments to run when this page is
     * updated.
     *
     * @param callable $cb The function to call when the page is updated.
     * @param array $args The args to pass to the callback.
     */
    final function add_action_on_update(callable $cb, array $args=[]): void {
        $page = $this;
        add_action($this->load_hook(),
            function(string $pid) use($page, $cb, $args): void {
                ['key' => $k, 'value' => $v] = $page->get_kv_updated();
                if ((isset($_GET[$k]) && ($_GET[$k] == $v))) $cb($args);
            });
    }
    
    /**
     * Return the page title.
     *
     * @return string The page title.
     */
    function page_title(): string {
    	$name = $this->name();
    	if (!$name) $name = 'Settings';
        return sprintf('%s — %s: %s', $this->module->space()->name(),
            $this->module->name(), $name);
    }
    
    
    /*
     * =========================================================================
     * Options
     * =========================================================================
     */
    
    /**
     * Return the option prefix for this page: the module qualifier plus
     * the ID of this page.
     *
     * @return string The prefix.
     */
    protected function prefix(): string {
        return $this->module->qualify($this->id());
    }

	/**
	 * Return the option name qualified by module ID and settings page ID.
	 *
	 * @param string $name The name to qualify.
	 * @return string The qualified name.
	 */
    final function qualify(string $name): string {
        return sprintf('%s_%s', $this->prefix(), $name);
    }

	/**
	 * Return the value of the given option.
	 *
	 * @param string $option The option name.
	 * @param bool $default The value to return if the option is not set.
	 * @return mixed The value of the option.
	 */
    final function get(string $option, $default=false) {
        return get_option($this->qualify($option), $default);
    }

	/**
	 * Update the given option with the given value and return the success/
	 * failure flag. $autoload is whether to autoload the value on each WP load.
	 *
	 * @param string $option The option name.
	 * @param mixed $value The value to set.
	 * @param bool|null $autoload Whether to load this option on WP load.
	 * @return bool Whether the option was updated.
	 */
    final function update(string $option, $value, bool $autoload=null): bool {
        return update_option($this->qualify($option), $value, $autoload);
    }

	/**
	 * Delete the given option and return the success/failure flag.
	 *
	 * @param string $option The option name.
	 * @return bool Whether the option was deleted.
	 */
    final function delete(string $option): bool {
        return delete_option($this->qualify($option));
    }
}
