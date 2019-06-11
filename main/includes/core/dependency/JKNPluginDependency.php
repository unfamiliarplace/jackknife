<?php

/**
 * A dependency that requires a particular plugin to be active.
 * TODO Check version range.
 */
final class JKNPluginDependency extends JKNDependency {
    
    private $name;
    private $url;
    private $file;

	/**
	 * Besides the ID, store the plugin's name, URL, and filename.
	 * The filename should be rooted at the plugin's folder:
	 *      'plugin_folder/plugin_file.php'
	 *
	 * @param string[] $args An array containing a name, URL, and file.
	 */
    function __construct(array $args) {
        parent::__construct($args);
        $this->name = $args['name'];
        $this->url = $args['url'];
        $this->file = $args['file'];
    }
    
    /**
     * Register this dependency with JKN.
     */
    function register(): void {
        JKNAPI::registry()->register_plugin_dependency($this);
    }

    /**
     * Return the plugin's name.
     *
     * @return string The name.
     */
    function get_name(): string { return $this->name; }
    
    /**
     * Return the plugin's URL.
     *
     * @return string The URL.
     */
    function get_url(): string { return $this->url; }

    /**
     * Return true iff the plugin is active.
     *
     * @return bool Whether this plugin is active.
     */
    function met(): bool {
        // Can't use is_plugin_active; that's admin-side only
        $active_plugin_files = (array) get_option('active_plugins', []);
        return in_array($this->file, $active_plugin_files);
    }
}
