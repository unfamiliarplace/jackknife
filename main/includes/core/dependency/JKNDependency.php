<?php

/**
 * Template for a dependency that can identify itself and check whether it
 * has been met. Self-registers with JKN on instantiation.
 */
abstract class JKNDependency {
    
    private $id;

	/**
	 * Set the ID.
	 *
	 * @param array $args
	 */
    function __construct(array $args) {
        $this->id = $args['id'];
        $this->register();
    }
    
    /**
     * Register this dependency with JKN.
     */
    abstract function register(): void;
    
    /**
     * Return the ID.
     *
     * @return string The ID.
     */
    final function get_id(): string { return $this->id; }

    /**
     * Return a human-readable name.
     *
     * @return string The name.
     */
    abstract function get_name(): string;
    
    /**
     * Return an author URL. This may be null in a child class.
     *
     * @return string The URL, if there is one.
     */
    abstract function author_url(): ?string;

    /**
     * Return true iff this dependency is met.
     *
     * @return bool Whether this dependency is met.
     */
    abstract function met(): bool;

    /**
     * Return the path to this dependency's folder.
     *
     * @return string|null The path.
     */
    abstract function path(): ?string;

	/**
	 * Return the URL to this dependency's folder.
	 *
	 * @return string|null The URL.
	 */
	abstract function url(): ?string;
}
