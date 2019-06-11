<?php

/**
 * A module that tests a plugin dependency.
 * TODO The test suite is prepreprealpha... it contributes almost nothing.
 */
final class JKNDepTest extends JKNModule {
    
    /**
     * Return the ID of this module.
     *
     * @return string The ID.
     */
    function id(): string { return 'dep_test'; }
    
    /**
     * Return the name of this module.
     *
     * @return string The name.
     */
    function name(): string { return 'Dependency Test'; }
    
    /**
     * Return the description of this module.
     *
     * @return string The description.
     */
    function description(): string { return 'Tests a module dependency.'; }
    
    /**
     * Create an admin notice to say this module was activated.
     */
    function run_on_startup(): void {
        $name = $this->name();
        add_action('admin_notices', function() use ($name) {
            printf('<div class="notice notice-info is-dismissible">'
                . 'The %s module was activated.</div>', $name);
        });
    }
}
