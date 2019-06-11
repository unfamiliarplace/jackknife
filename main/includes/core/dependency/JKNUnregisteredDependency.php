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
     * Return the empty string for the URL.
     *
     * @return string The empty string.
     */
    function get_url(): string { return ''; }
    
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
}
