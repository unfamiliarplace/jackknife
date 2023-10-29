<?php

/**
 * Allows you to create an above-the-fold announcement on the front page.
 */
final class MJKAnc extends JKNModule {

	/*
	 * =========================================================================
	 * Module registration
	 * =========================================================================
	 */

	/**
	 * @return string
	 */
    function id(): string { return 'anc'; }

	/**
	 * @return string
	 */
    function name(): string { return 'Announcement'; }

	/**
	 * @return string
	 */
    function description(): string {
        return 'Adds the option of an announcement banner.';
    }


	/*
	 * =========================================================================
	 * Tasks
	 * =========================================================================
	 */

	/**
	 * Autoload classes.
	 */
    function run_on_load(): void {
    	JKNClasses::autoload([
    		'MJKANC_ACF'                => 'includes/acf_api/registry.php',
		    'MJKAnnouncementShortcode'  => 'includes/shortcodes/announcement.php'
	    ]);
    }

	/**
	 * Add the ACF filters and the shortcode.
	 */
    function run_on_startup(): void {
        
        // Add filters using that
        MJKANC_ACF::add_filters();

        // Instantiate the shortcode
        MJKAnnouncementShortcode::add_shortcode();
    }
}
