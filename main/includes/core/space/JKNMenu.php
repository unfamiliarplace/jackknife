<?php

/**
 * Stores and provides functions for managing a space's menu.
 * Also provides functions for access by the submenu.
 */
final class JKNMenu {    
    
    // Pages whose order is not set get this order and are placed at the end
    const default_sub_order = -1;
    
    private $name;
    private $top;
    private $pages=[];
    private $icon_url = JKN_ICON_URL;
    private $top_order=83;

	/**
	 * Set the name of this menu (and by consequence the modules page base).
	 *
	 * @param string $name The name of the menu.
	 */
    function __construct(string $name) { $this->name = $name; }
    
    /*
     * =========================================================================
     * Menu creation
     * =========================================================================
     */
    
    /**
     * Schedule adding the toplevel menu to WP.
     */
    function create_main_menu(): void {

        add_action('admin_menu', function() {
        
            // Add the toplevel page
            $hook = add_menu_page(
                $page_title = $this->top_name(),
                $menu_title = $this->top_name(),
                $capability = 'manage_options',
                $menu_slug = $this->top_slug(),
                $function = [$this->top, 'render'],
                $icon_url = $this->icon_url,
                $position = $this->top_order
            );

            // Save the hook to our space
	        $this->top->set_hook($hook);
        });
    }
    
    /**
     * Create the submenu in WP.
     */
    function create_submenu(): void {
        $submenu = new JKNSubmenu($this);
        $submenu->create();
    }
    
    /*
     * =========================================================================
     * Setters
     * =========================================================================
     */

	/**
	 * Set the toplevel page (the modules page).
	 *
	 * @param JKNModulesPageSettings $page The page to set as the toplevel page.
	 */
    function set_top(JKNModulesPageSettings $page): void {
        $this->top = $page;
        $page->set_order(0);
    }

	/**
	 * Add a page to this menu with a given order.
	 * If the order is not supplied, the page will go to the end.
	 *
	 * @param JKNSettingsPage $page The page to add.
	 * @param int|null $order The requested order in the submenu.
	 */
    function add_page(JKNSettingsPage $page, int $order=null): void {
        if (empty($order)) $order = self::default_sub_order;
        $this->pages[] = $page;
        $page->set_order($order);
    }
    
    
    /*
     * =========================================================================
     * Getters
     * =========================================================================
     */

	/**
	 * Set the icon URL.
	 *
	 * @param string $url The (external) URl to the icon file.
	 */
    function set_icon_url(string $url): void { $this->icon_url = $url; }

	/**
	 * Set the order in the overall WP admin menu.
	 *
	 * @param int $order The requested order.
	 */
    function set_top_order(int $order): void { $this->top_order = $order; }
    
    /**
     * Return the top page.
     *
     * @return JKNModulesPageSettings The top page in the menu.
     */
    function top(): JKNModulesPageSettings { return $this->top; }
    
    /**
     * Return the name of the top page.
     *
     * @return string The name of the top page in the menu.
     */
    function top_name(): string { return $this->name; }
    
    /**
     * Return the slug of the top page.
     *
     * @return string The slug of the top page in the menu.
     */
    function top_slug(): string { return $this->top->slug(); }
    
    /**
     * Return all the pages in this menu.
     *
     * @return JKNSettingsPage[] The registered pages in this menu.
     */
    function pages(): array { return $this->pages; }
    
    /**
     * Return the default order for a submenu item.
     *
     * @return int The default order.
     */
    static function default_sub_order(): int { return self::default_sub_order; }
}
