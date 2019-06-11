<?php

/**
 * Template for a settings page based on a custom post type.
 * 
 * Such pages are somewhat unique in that they are added indirectly (during
 * register_post_type), and we have very little control over that process.
 * 
 * Note that during this process, it's the 'all_items' label that becomes the
 * page's menu slug.
 * 
 * Also unique is that these are not pages for setting options, but an
 * 'edit.php' screen listing all posts of the type.
 */
abstract class JKNSettingsPageCPT extends JKNSettingsPage {
    
    private $id;    
    private $post_type;
    private $name;
    
    /*
     * =========================================================================
     * Identification
     * =========================================================================
     */
    
    /**
     * Return the ID.
     *
     * @return string The ID.
     */
    final function id(): string { return $this->id; }
    
    /**
     * Return the name.
     *
     * @return string The name.
     */
    function name(): string { return $this->name; }
    
    /**
     * Return the menu title.
     *
     * @return string The menu title.
     */
    function menu_title(): string { return $this->name; }
    
    /*
     * =========================================================================
     * Set up
     * =========================================================================
     */

	/**
	 * Besides parent setup, set the type, hook, and post type.
	 *
	 * @param JKNModule $module The module of this settings page.
	 * @param string $id The ID to use for this settings page.
	 * @param string $post_type The ID of the post type this page represents.
	 * @param string $name The name of the post type (in the plural).
	 */
    final function __construct(JKNModule $module,
            string $id, string $post_type, string $name) {
        
        // Post type must be set before parent construct
        $this->post_type = $post_type;  
        parent::__construct($module);        
              
        $this->id = $id;
        $this->name = $name;
        $this->set_hook('edit.php');
    }
    
    /**
     * Return the slug for the edit screen.
     *
     * @return string The slug.
     */
    final function slug(): string {
        return sprintf('edit.php?post_type=%s', $this->post_type);
    }
    
    /**
     * Return the URL for this page.
     *
     * @return string The URL.
     */
    final function url(): string {
        return admin_url(sprintf('edit.php?post_type=%s', $this->post_type));
    }
    
    /**
     * Return the ID of the post type for which this is the edit screen.
     *
     * @return string The ID of the WP post type.
     */
    final function post_type(): string { return $this->post_type; }
    
    /**
     * Dummy function: CPT pages are added during CPT registration.
     */
    final function add_page_to_menu(): void {}
    
    /**
     * Dummy function: edit screens are not updated like options pages are.
     *
     * @return string[]|null Null since there is no key/value pair for this page.
     */
    final function get_kv_updated(): ?array { return null; }
}
