<?php

/**
 * A settings page for those added by ACF.
 * 
 * To use this, extend JKNACF and set the group location to this page's slug.
 * Little if anything needs to be done in the extending class -- it can even
 * be anonymous.
 */
abstract class JKNSettingsPageACF extends JKNSettingsPage {
    
    /*
     * =========================================================================
     * Identification
     * =========================================================================
     */
    
    /**
     * Return the $_GET key/value pair that means the page has been updated.
     *
     * @return string[] The key/value pair.
     */
    final function get_kv_updated(): array {
        return ['key' => 'message', 'value' => 1];
    }
    
    /**
     * Add this page to the WP menu and ACF's registry.
     */
    final function add_page_to_menu(): void {
        
        // Bail if ACF is not installed
        if (!function_exists('acf_add_options_page')) return;
        
        // Otherwise add the page
        // N.B. We do not get a hook back, unlike WP adding
        acf_add_options_page([
            'title'         => $this->page_title(),
            'menu'          => $this->menu_title(),
            'slug'          => $this->slug(),
            'parent'        => $this->space()->menu()->top_slug(),
            'capability'    => 'manage_options'
        ]);
    }
}
