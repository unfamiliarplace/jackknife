<?php

/**
 * A settings page intended to use the WP settings functions.
 * 
 * The general structure of such a page is:
 *      -- Register and unregister all setting names (this class handles that).
 *      -- Add setting sections to the page.
 *      -- Add setting fields to each section.
 *      -- Provide functions to renderer each section and field.
 * 
 * Use this class's 'qualify' and 'get' methods for interacting with all names.
 */
abstract class JKNSettingsPageWP extends JKNSettingsPage {
    
    const form_id = 'main-sections';
    
    /*
     * =========================================================================
     * Override
     * =========================================================================
     */
    
    /**
     * Return a flat array of unqualified option name strings.
     * The class will handle registration/unregistration.
     *
     * @return string[] The option names.
     */
    protected abstract function option_names(): array;
    
    /**
     * Add the settings sections and fields using WP functions.
     */
    abstract function add_sections_and_fields(): void;
    
    
    /*
     * =========================================================================
     * Optionally override (Formatting)
     * =========================================================================
     */
    
    /**
     * Output the HTML for an intro for the page.
     */
    protected function output_intro(): void {
        echo sprintf('<h2>%s</h2>', $this->page_title());
    }
    
    /**
     * Output the HTML for an outtro to the page.
     */
    protected function output_outtro(): void {}
    
    /**
     * Output any CSS desired.
     */
    protected function output_style(): void {}
    
    
    /*
     * =========================================================================
     * No need to override
     * =========================================================================
     */
    
    /*
     * =========================================================================
     * Set up
     * =========================================================================
     */

	/**
	 * Besides parent setup, set the type and add actions for setting name
	 * registration/unregistration and for adding the sections and fields.
	 * Also initialize hook, even though it should not be relied on till after
	 * admin-menu.
	 *
	 * @param JKNModule $module The module of this settinggs page.
	 */
    function __construct(JKNModule $module) {
        parent::__construct($module);
        
        // Add hooks for registration and unregistration
        add_action('admin_init', [$this, 'register_settings']);
        JKNAPI::add_jkn_deactivate_action([$this, 'unregister_settings']);
        
        // Add the sections and fields of this page
        add_action('admin_init', [$this, 'add_sections_and_fields']);
    }
    
    /**
     * Add this page to the WP menu and save the resulting WP hook suffix.
     */
    final function add_page_to_menu(): void {
        
        // Add the page
        $hook = add_submenu_page(
            $parent_slug = $this->space()->menu()->top_slug(),
            $page_title = $this->page_title(),
            $menu_title = $this->menu_title(),
            $capability = 'manage_options',
            $menu_slug = $this->slug(),
            $function = [$this, 'render']
        );

        // Store the returned hook, overwriting our default guess
        $this->set_hook($hook);
    }
    
    
    /*
     * =========================================================================
     * Identification
     * =========================================================================
     */
    
    /**
     * Return an array ['key' => key, 'value' => value] representing the
     * $_GET key and its value that indicate that settings have been updated.
     *
     * @return string[] The key/value pair for this page's updated hook.
     */
    final function get_kv_updated(): array {
        return ['key' => 'settings-updated', 'value' => true];
    }
    
    
    /*
     * =========================================================================
     * Settings registration
     * =========================================================================
     */
    
    /**
     * Register all this page's settings with WP.
     */
    final function register_settings(): void {
        foreach ($this->option_names() as $option) {
            register_setting($this->slug(), $this->qualify($option));
        }
    }

    /**
     * Unregister all this page's settings with WP.
     */
    final function unregister_settings(): void {
        foreach ($this->option_names() as $option) {
            unregister_setting($this->slug(), $this->qualify($option));
        }
    }
    
    
    /*
     * =========================================================================
     * Formatting
     * =========================================================================
     */
    
    /**
     * Output the page.
     * You can technically override this, but you have to keep core elements.
     */
    function render(): void {
        
        echo '<div class="wrap">';
        
        $this->output_style();
        $this->output_intro();

        // Everything from <form> to </form> is what WP needs to work
        $fid = static::form_id;
        printf('<form id="%s"method="post" action="options.php">', $fid);
        
        settings_fields($this->slug());
        do_settings_sections($this->slug());
        submit_button();
        
        echo '</form>';
        
        $this->output_outtro();
        
        echo '</div>';
    }
}
