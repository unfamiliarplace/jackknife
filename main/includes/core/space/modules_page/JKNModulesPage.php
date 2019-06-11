<?php

/**
 * A special module that provides a settings page for all other modules.
 * The space can't actually register this module, just activate it.
 */
final class JKNModulesPage extends JKNModule {
    
    /**
     * Return the ID.
     *
     * @return string The ID.
     */
    function id(): string { return 'modules'; }
    
    /**
     * Return the name.
     * N.B. This is not front-facing anyway.
     *
     * @return string The name.
     */
    function name(): string { return 'Modules'; }
    
    /**
     * Return the description.
     * N.B. This is not front-facing anyway.
     *
     * @return string The description.
     */
    function description(): string {
        return 'Provides the "Modules" settings page.';        
    }
    
    /**
     * This module doesn't need to do anything in particular on activation.
     */
    function run_on_startup(): void {}
}
