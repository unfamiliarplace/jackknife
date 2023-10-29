<?php

/**
 * A dependency returned when the API checks for an ID that does not exist.
 * Just fails.
 */
final class JKNUnregisteredDependency extends JKNDependency {
    
    /**
     * Do not register this mock dependency.
     */
    function register(): void {}
    
    /**
     * Return null for the author URL.
     *
     * @return string The empty string.
     */
    function author_url(): ?string { return null; }
    
    /**
     * Return a name indicating that this represents an unregistered dependency.
     *
     * @return string The name.
     */
    function get_name(): string {
        return sprintf('(Unregistered dependency: "%s")', $this->get_id());        
    }
    
    /**
     * Return false to indicate that this dependency was not checkable.
     *
     * @return bool False.
     */
    function met(): bool { return false; }

	/**
	 * Return null for no path.
	 *
	 * @return string|null The path.
	 */
	function path(): ?string { return null; }

	/**
	 * Return null for no URL.
	 *
	 * @return string|null The URL.
	 */
	function url(): ?string { return null; }
}
