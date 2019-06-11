<?php

/**
 * The space is the bigger organizing unit of JKN.
 * 
 * A space controls a set of modules, including activation, and a menu for
 * their settings pages.
 */
final class JKNSpace {
    
    private $id;
    private $name;
    private $base_file;        
    private $modules = [];
    private $menu;
    private $suppress_modules_page=false;

	/*
	 * =========================================================================
	 * Key behaviour
	 * =========================================================================
	 */

	/**
	 * Set the prefix. You can only use each prefix once.
	 *
	 * @param string $id The ID for this space.
	 * @param string $name The name to use for this space.
	 * @param string $base_file The base file where the space was registered.
	 */
    function __construct(string $id, string $name, string $base_file) {
        $this->id = $id;
        $this->name = $name;
        $this->base_file = $base_file;
        $this->menu = new JKNMenu($name);
    }

	/**
	 * Create and activate the modules page module; add its settings page.
	 */
	function create_modules_page(): void {
		$mpage_mod = new JKNModulesPage($this);
		$mpage_mod->start_up();
		$mpage_settings = new JKNModulesPageSettings($mpage_mod);
		$this->menu->set_top($mpage_settings);

		// Suppress the modules page if requested
		if (!$this->suppress_modules_page) {
			$mpage_settings->add_page_add_action();
		}
	}
    
    /**
     * Set everything in motion. Run the modules through their lifecycle
     * and create the menu and its submenu (if there are any pages to add).
     */
    function run(): void {
                
        // Activate all modules
        JKNLifecycle::lifecycle($this->modules);
        
        // Create the menu
        if (is_admin() && !wp_doing_ajax()) {

        	if (!$this->suppress_modules_page || !empty($this->menu->pages())) {
		        $this->menu->create_main_menu();
		        $this->menu->create_submenu();
	        }
        }
    }

	/**
	 * Return the given name qualified by the space ID.
	 *
	 * @param string $name The name to qualify.
	 * @return string The qualified name.
	 */
    function qualify(string $name): string {
        return sprintf('%s_%s', $this->id, $name);
    }
    
    
    /*
     * =========================================================================
     * Setters
     * =========================================================================
     */

	/**
	 * Add the given module to this space.
	 *
	 * @param JKNModule $module The module to add.
	 */
    function add_module(JKNModule $module): void {
        $this->modules[$module->id()] = $module;
    }

	/**
	 * Add the given settings page to this space and assign it the given order.
	 * If no order is given, the page will go to the end of the submenu.
	 *
	 * @param JKNSettingsPage $page The settings page.
	 * @param int|null $order The requested submenu order, if there is one.
	 */
    function add_settings_page(JKNSettingsPage $page, int $order=null): void {
        $this->menu->add_page($page, $order);
    }

	/**
	 * Set an icon source for the menu. Should be a png 16x16.
	 *
	 * @param string $url The URL to the icon file.
	 */
    function set_icon_url(string $url): void {
        $this->menu->set_icon_url($url);
    }

	/**
	 * Set a menu order for the menu.
	 *
	 * @param int $order The order for the outer admin men.
	 */
    function set_menu_order(int $order): void {
        $this->menu->set_top_order($order);
    }

	/**
	 * Suppress the modules page from the menu.
	 * Useful for sites developed without the intention of having the user
	 * set modules on/off.
	 *
	 * @param bool $suppress Whether to suppress or unsuppress.
	 */
	function suppress_modules_page(bool $suppress=true): void {
		$this->suppress_modules_page = $suppress;
	}
    
    
    /*
     * =========================================================================
     * Getters
     * =========================================================================
     */
    
    /**
     * Return the space's ID.
     *
     * @return string The ID.
     */
    function id(): string { return $this->id; }
    
    /**
     * Return the space's name.
     *
     * @return string The name.
     */
    function name(): string { return $this->name; }
    
    /**
     * Return the (internal) path. This is assumed to be that of the base file.
     *
     * @return string The path.
     */
    function path(): string { return plugin_dir_path($this->base_file); }
    
    /**
     * Return the (external) url. This is assumed to be that of the base file.
     *
     * @return string The url.
     */
    function url(): string { return plugin_dir_url($this->base_file); }
    
    /**
     * Return the menu.
     *
     * @return JKNMenu The menu.
     */
    function menu(): JKNMenu { return $this->menu; }
    
    /**
     * Return the space's modules.
     *
     * @return JKNModule[] The modules registered to this space.
     */
    function modules(): array { return $this->modules; }

	/**
	 * Return the module with the given ID.
	 *
	 * @param string $id The id of the module to get.
	 * @return JKNModule The module with the given ID..
	 */
    function get_module(string $id): JKNModule { return $this->modules[$id]; }
}
