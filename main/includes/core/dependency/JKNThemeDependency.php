<?php

/**
 * A dependency that requires a given theme to be active.
 * TODO Check version range.
 */
final class JKNThemeDependency extends JKNDependency {
    
    private $name;
    private $author_url;
    private $author;
    private $allow_child = true;

	/**
	 * Besides the ID, store the name, URL, author,
	 * and whether a child theme is allowed.
	 * The author is used to verify the theme (themes don't have unique names).
	 *
	 * @param array $args An array of name, URL, author, and allow_child.
	 */
    function __construct(array $args) {
        
        parent::__construct($args);
        $this->name = $args['name'];
        $this->author_url = $args['url'];
        $this->author = $args['author'];
        
        if (isset($args['allow_child'])) {
            $this->allow_child = $args['allow_child'];
        }
    }
    
    /**
     * Register this dependency with JKN.
     */
    function register(): void {
        JKNAPI::registry()->register_theme_dependency($this);
    }

    /**
     * Return the name of this theme, plus a note about the use of a child
     * theme if one is allowed.
     *
     * @return string The name of the theme.
     */
    function get_name(): string {
        return $this->name . (($this->allow_child) ? ' (or child theme)' : '');
    }
    
    /**
     * Return the theme author's URL.
     *
     * @return string The URL.
     */
    function author_url(): string { return $this->author_url; }

    /**
     * Return true iff the theme is active.
     *
     * @return bool Whether the theme is active.
     */
    function met(): bool {
        
        // Extract current theme information
        $theme = wp_get_theme();
        $theme_name = $theme->get('Name');
        $theme_template = $theme->get('Template');
        $theme_author = $theme->get('Author');

        // Determine allowed names
        // If a child theme is active, the parent theme is the 'template'
        $allowed_names = [strtolower($theme_name)];
        if ($this->allow_child) $allowed_names[] = strtolower($theme_template);
        
        // Check that the author and name are correct
        return in_array(strtolower($this->name), $allowed_names) &&
                (strtolower($this->author) == strtolower($theme_author));
    }

	/**
	 * Return the path to this dependency's folder.
	 *
	 * @return string|null The path.
	 */
	function path(): string {
		return sprintf('%swp-content/themes/%s/', ABSPATH, $this->name);
	}

	/**
	 * Return the URL to this dependency's folder.
	 *
	 * @return string|null The URL.
	 */
	function url(): string {
		return sprintf('%s/%s/', get_theme_root_uri($this->name), $this->name);
	}
}
