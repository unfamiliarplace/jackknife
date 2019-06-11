<?php

/**
 * A registry for JKN's spaces and dependencies.
 * This registry is accessed by JKNAPI using a global instance of JKN.
 */
final class JKNRegistry {
    
    private $spaces = [];
    private $module_deps = [];
    private $plugin_deps = [];
    private $theme_deps = [];
    
    
    /*
     * =========================================================================
     * Setters
     * =========================================================================
     */

    /**
     * Register a space.
     *
     * @param JKNSpace $space The space to be registered.
     */
    function register_space(JKNSpace $space): void {
        $this->spaces[$space->id()] = $space;
    }

	/**
	 * Register the given module dependency.
	 *
	 * @param JKNModuleDependency $dep The dependency to be registered.
	 */
    function register_module_dependency(JKNModuleDependency $dep): void {
        $this->module_deps[$dep->get_id()] = $dep;
    }

	/**
	 * Register the given plugin dependency.
	 *
	 * @param JKNPluginDependency $dep The dependency to be registered.
	 */
    function register_plugin_dependency(JKNPluginDependency $dep): void {
        $this->plugin_deps[$dep->get_id()] = $dep;
    }

	/**
	 * Register the given theme dependency.
	 *
	 * @param JKNThemeDependency $dep The dependency to be registered.
	 */
    function register_theme_dependency(JKNThemeDependency $dep): void {
        $this->theme_deps[$dep->get_id()] = $dep;
    }
    
    
    /*
     * =========================================================================
     * Getters
     * =========================================================================
     */
    
    /**
     * Return all registered spaces.
     */
    function spaces(): array { return $this->spaces; }

	/**
	 * Return the registered space with the given ID.
	 *
	 * @param string $id The id of the space to return.
	 * @return JKNSpace The space.
	 */
    function get_space(string $id): JKNSpace {
        return $this->spaces[$id];
    }

	/**
	 * Return the registered module dependency with the given ID.
	 * If it is not registered, return a mock (unregistered) dependency.
	 *
	 * @param string $id The ID of the dependency to return.
	 * @return JKNDependency The dependency.
	 */
    function get_module_dependency(string $id): JKNDependency {
        $d = $this->module_deps[$id];
        return (empty($d)) ? new JKNUnregisteredDependency(['id' => $id]) : $d;
    }

	/**
	 * Return the registered plugin dependency with the given ID.
	 * If it is not registered, return a mock (unregistered) dependency.
	 *
	 * @param string $id The ID of the dependency to return.
	 * @return JKNDependency The dependency.
	 */
    function get_plugin_dependency(string $id): JKNDependency {
        $d = $this->plugin_deps[$id];
        return (empty($d)) ? new JKNUnregisteredDependency(['id' => $id]) : $d;
    }

	/**
	 * Return the registered theme dependency with the given ID.
	 * If it is not registered, return a mock (unregistered) dependency.
	 *
	 * @param string $id The ID of the dependency to return.
	 * @return JKNDependency The dependency.
	 */
    function get_theme_dependency(string $id): JKNDependency {
        $d = $this->theme_deps[$id];
        return (empty($d)) ? new JKNUnregisteredDependency(['id' => $id]) : $d;
    }
}
