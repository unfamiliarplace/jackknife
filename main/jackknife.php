<?php
/**
 Plugin Name: Jackknife
 Plugin URI: https://sawczak.ca
 Description: A framework for simple plugin creation, focused on modularity.
 Author: Luke Sawczak
 Version: 1.3
 Author URI: https://sawczak.ca
 */

// Set constants and include classes
define('JKN_FILE', (__FILE__));
require_once 'constants.php';
require_once 'includes.php';

// Add a quick craptastic test
//add_action(JKN_HOOK, function(): void {
//	require_once 'includes/test/jkn_api.php'; }
//);

/**
 * Organizer for Jackknife behaviour.
 */
final class JKN_Jackknife {

    private $registry;
    
    /**
     * Set the registry and schedule the main action.
     * Also reset the timezone and schedule deleting all cache dirs.
     */
    function __construct() {

    	// Create the registry, schedule the main action
        $this->registry = new JKNRegistry();
        add_action('plugins_loaded',  [$this, 'main']);
        
        // TODO This is probably no longer necessary / must be rethought
	    // https://xkcd.com/1883/
        JKNTime::reset_timezone();

        // Take all modules out with us when we go down
        register_deactivation_hook(JKN_FILE, [__CLASS__, 'deactivate']);
        register_uninstall_hook(JKN_FILE, [__CLASS__, 'uninstall']);
    }
    
    /**
     * Collect modules and go!
     */
    function main(): void {
        
        // This is where all the spaces will have hooked onto
        do_action(JKN_HOOK);
        $spaces = $this->registry->spaces();

        // Add module pages for each space
		self::add_space_module_pages($spaces);

        // Check state changes
        JKNLifecycle::notify_state_changes();

        // Run each space
	    self::run_spaces($spaces);
    }

	/**
	 * Return the registry for JKN.
	 *
	 * @return JKNRegistry The registry.
	 */
	function registry(): JKNRegistry { return $this->registry; }

    /*
     * =========================================================================
     * Static lifecycle functions (static for better hooking)
     * =========================================================================
     */

	/**
	 * Add module pages for each of the given spaces.
	 *
	 * @param JKNSpace[] $spaces The spaces whose module pages will be added.
	 */
	static function add_space_module_pages(array $spaces): void {
		foreach($spaces as $space) {
			$space->create_modules_page();
		}
	}

	/**
	 * Run all the given spaces.
	 *
	 * @param JKNSpace[] $spaces The spaces to run.
	 */
	static function run_spaces(array $spaces): void {
		foreach($spaces as $space) {
			$space->run();
		}
	}

	/**
	 * Deactivate: deactivate all modules.
	 */
	static function deactivate(): void {
		JKNLifecycle::force_deactivate();

	}

    /**
     * Uninstall: uninstall all modules and delete cache dirs.
     * TODO Option collection and deletion..
     */
    static function uninstall(): void {
    	JKNLifecycle::force_uninstall();
	    JKNFilesystem::rmdir_recursive(JKN_CACHE_DIR_INT);

    }
}

// Instantiate and create a global reference to JKN
global $JKN;
$JKN = new JKN_Jackknife();
