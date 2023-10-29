<?php

/**
 * A dependency that requires a particular plugin to be active.
 * TODO Check version range.
 */
final class JKNPluginDependency extends JKNDependency {
    
    private $name;
    private $author_url;
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
        $this->author_url = $args['url'];
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
     * Return the plugin author's URL.
     *
     * @return string The URL.
     */
    function author_url(): string { return $this->author_url; }

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

	/**
	 * Return the path to this dependency's folder.
	 *
	 * @return string|null The path.
	 */
	function path(): string {
		return sprintf('%swp-content/plugins/%s/',
			ABSPATH, explode('/', $this->file)[0]); # ABSPATH has '/'
	}

	/**
	 * Return the URL to this dependency's folder.
	 *
	 * @return string|null The URL.
	 */
	function url(): string {
		return plugins_url(explode('/', $this->file)[0] . '/');
	}
}
