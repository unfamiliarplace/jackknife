<?php

/**
 * A dependency that requires a given module to be activatable.
 */
final class JKNModuleDependency extends JKNDependency {
    
    private $space_id;
    private $module_id;

	/**
	 * Besides parent construction, also set a space ID.
	 *
	 * @param string[] $args An array of id and space_id.
	 */
    final function __construct(array $args) {
        $this->module_id = $args['id'];
        $this->space_id = $args['space_id'];
        
        parent::__construct(['id' => $this->module_id]);
    }
    
    /**
     * Return the name of the module.
     *
     * @return string The name.
     */
    final function get_name(): string {
        return $this->module()->name();
    }
    
    /**
     * Return the module.
     *
     * @return JKNModule The module.
     */
    final function module(): JKNModule {
        $space = JKNAPI::space_from_sid($this->space_id);
        return $space->modules()[$this->module_id];
    }
    
    /**
     * Register this dependency with JKN.
     */
    function register(): void {
        JKNAPI::registry()->register_module_dependency($this);
    }

    /**
     * Return null since modules do not have external links.
     *
     * @return string|null A null external link.
     */
    final function author_url(): ?string { return null; }
    
    /**
     * Return true iff the module can be activated.
     *
     * @return bool Whether the module can be activated.
     */
    final function met(): bool {
        return $this->module()->current_state() == JKNLifecycle::ON_CAN_ACTIVATE;
    }

	/**
	 * Return the path to the module's folder.
	 *
	 * @return string|null The path.
	 */
	function path(): string { return $this->module()->path(); }

	/**
	 * Return the URL to the module's folder.
	 *
	 * @return string|null The URL.
	 */
	function url(): ?string { return $this->module()->url(); }
}
