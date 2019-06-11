<?php

/**
 * Creates a submenu for the space.
 */
class JKNSubmenu {
    
    private $menu;

	/**
	 * Set the parent menu.
	 *
	 * @param JKNMenu $menu The menu to set as the parent.
	 */
    function __construct(JKNMenu $menu) { $this->menu = $menu; }
    
    
    /*
     * =========================================================================
     * Creation
     * =========================================================================
     */
    
    /**
     * Create the submenu by scheduling the page adding and ordering.
     */
    function create(): void {
        $this->add_page_add_actions();
        $this->add_order_filter();
    }
    
    /**
     * Add the page add action for each page.
     */
    private function add_page_add_actions(): void {
        foreach($this->menu->pages() as $page) {
            if ($page->module()->is_running()) {
	            $page->add_page_add_action();
            }
        }
    }
    
    
    /*
     * =========================================================================
     * Submenu order
     * =========================================================================
     */
    
    /**
     * Add the custom menu order filter.
     */
    private function add_order_filter(): void {
        add_filter('custom_menu_order', '__return_true');
        add_filter('menu_order', [$this, 'customize_wp_order']);
    }

	/**
	 * Customize the WP submenu with the order of our pages.
	 * $menu_order is only needed by the filter; we don't use it.
	 *
	 * @param array $menu_order The order of the (main) admin menu.
	 * @return array The same thing untouched.
	 */
    function customize_wp_order(array $menu_order): array {
        $wp_submenu = $this->get_wp_submenu();
        
        if (!empty($wp_submenu)) {
            $pages = $this->order_pages($this->menu->pages());
            $new_submenu = $this->order_submenu($wp_submenu, $pages);
            $this->set_wp_submenu($new_submenu);
        }
        
        return $menu_order;
    }
    
    /**
     * Return the approprate WP submenu.
     * WP submenu structure:
     *  [
     *      [0] menu_title
     *      [1] capability
     *      [2] menu_slug
     *      [3] page_title
     *  ]
     *
     * @return array The WP submenu for this submenu's slug.
     */
    private function get_wp_submenu(): array {
        global $submenu;
        if (!isset($submenu[$this->menu->top_slug()])) return [];
        return $submenu[$this->menu->top_slug()];
    }

	/**
	 * Set the appropriate WP submenu to the given submenu.
	 *
	 * @param array $new_submenu The submenu to replace the WP one with.
	 */
    private function set_wp_submenu(array $new_submenu): void {
        global $submenu;
        if (!isset($submenu[$this->menu->top_slug()])) return;
        $submenu[$this->menu->top_slug()] = $new_submenu;
    }

	/**
	 * Return the settings pages, ordered.
	 *
	 * @param JKNSettingsPage[] $pages The pages to order.
	 * @return JKNSettingsPage[] The ordered pages.
	 */
    private function order_pages(array $pages): array {
        
        // Basic sort, putting default order last
	    $def = $this->menu->default_sub_order();
        usort($pages,
	        function(JKNSettingsPage $a, JKNSettingsPage $b) use ($def): int {
        	if ($a->order() == $def) return 1;
        	if ($b->order() == $def) return -1;
        	return $a->order() <=> $b->order();
        });
        
        // Prepend the top page
        $top = $this->menu->top();
        array_unshift($pages, $top);

        return $pages;
    }

	/**
	 * Return the given WP submenu ordered according to the given pages.
	 * If any proposed pages are not in the given menu, drop them.
	 *
	 * @param array $submenu The original WP submenu.
	 * @param JKNSettingsPage[] $pages The pages to order within it.
	 * @return array The ordered submenu.
	 */
    private function order_submenu(array $submenu, array $pages): array {        
        $new_submenu = [];
        
        // The submenu doesn't just contain slugs; it contains arrays.
        // We must identify the indexes so we can preserve those arrays.
        
        foreach($pages as $page) {
            $i = $this->get_submenu_index($submenu, $page->slug());
            if (!is_null($i)) $new_submenu[] = $submenu[$i];
        }
    
        return $new_submenu;
    }

    /**
     * Return the index where the given slug appears in the given submenu.
     * It's possible for a page to be in our list but not the submenu, because
     * pages are provided with remove actions. If this happens, return null.
     *
     * @param array $submenu A WP submenu; a sub-array of global $submenu.
     * @param string $slug The slug of the page to look for in the submenu.
     * @return int|null The index of the page, or null if it does not appear.
     */
    private function get_submenu_index(array $submenu, string $slug): ?int {
        
        // Slug in WP submenu is $submenu[index][2]
        $n = count($submenu);
        for ($i=0; $i < $n; $i++) {
            if ($submenu[$i][2] == $slug) return $i;
        }
        
        // If not found
        return null;
    }
}
