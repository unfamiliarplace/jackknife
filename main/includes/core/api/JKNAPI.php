<?php

/**
 * Provides functions for creating accessing spaces, modules, and more.
 * For example, from any module code you should be able to call:
 * 
 * JKNAPI::space()
 * JKNAPI::module()
 * JKNAPI::settings_page()
 * 
 * ...and get the right one for that particular code.
 */
final class JKNAPI {
    
    /*
     * =========================================================================
     * Dynamic identification via caller files
     * =========================================================================
     */

	/**
	 * Dynamically return the filename of the caller.
	 *
	 * $ider is null or a filename, classname, or object for deriving the mid.
	 * For example, supply __FILE__ or static::class.
	 * If it is null, derive a filename from the debug backtrace.
	 *
	 * A typical way to use this would be to forward the backtrace:
	 *      if (empty($backtrace)) $backtrace = debug_backtrace();
	 *      $file = JKNAPI::file($ider, $backtrace);
	 *
	 * @param null $ider A file path, object, or class name (if supplied).
	 * @param array $backtrace A debug backtrace to extract a filepath from.
	 * @return null|string The file path of the caller or identifier, if extant.
	 */
    static function file($ider=null, array $backtrace=[]): ?string {
        
        // By identifying the file of a supplied object
        if (!empty($ider) && gettype($ider) == 'object') {
            return self::file_from_object($ider);
            
        // By identifying a supplied string
        } elseif (!empty($ider) && gettype($ider) == 'string') {
            
            // If it's an object
            if (is_object($ider) || class_exists($ider)) {
                return self::file_from_object($ider);
                
            // Treat it as a file
            } else {
                return $ider;
            }
            
        // By identifying the file from which the caller originated
        } else {
            if (empty($backtrace)) $backtrace = debug_backtrace();
            return self::file_from_backtrace($backtrace);
        }
    }

	/**
	 * Dynamically return the filename of the given object or class.
	 *
	 * @param mixed $object The object or class.
	 * @return string The filename in which the object or class is registered.
	 */
    static function file_from_object($object): string {
        $c = new ReflectionClass($object);
        return $c->getFileName();
    }

	/**
	 * Return the filename of the most recent call from a given backtrace.
	 *
	 * @param array $backtrace The debug backtrace.
	 * @return string The file path.
	 */
    static function file_from_backtrace(array $backtrace): string {
        return $backtrace[0]['file'];
    }


	/*
	 * =========================================================================
	 * Directory
	 * =========================================================================
	 */

	/**
	 * Return the absolute path to the directory of the caller.
	 * $ider conforms to the spec of JKNAPI::file.
	 *
	 * @param null $ider A file path, object, or class name (if supplied).
	 * @param array $backtrace A debug backtrace to extract a filepath from.
	 * @return null|string The plugin path of the caller or identifier.
	 */
	static function dir($ider=null, array $backtrace=[]): ?string {
		if (empty($backtrace)) $backtrace = debug_backtrace();
		$file = JKNAPI::file($ider, $backtrace);
		return self::dir_from_file($file);
	}

	/**
	 * Return the absolute path to the directory of the caller given a file.
	 *
	 * @param string $file An absolute filepath.
	 * @return null|string The path to the file's directory.
	 */
	static function dir_from_file(string $file): ?string {
		return pathinfo($file, PATHINFO_DIRNAME);
	}


	/*
	 * =========================================================================
	 * Plugin file
	 * =========================================================================
	 */

	/**
	 * Return the absolute path of the main plugin file for the caller.
	 * $ider conforms to the spec of JKNAPI::file.
	 *
	 * @param null $ider A file path, object, or class name (if supplied).
	 * @param array $backtrace A debug backtrace to extract a filepath from.
	 * @return null|string The plugin path of the caller or identifier.
	 */
	static function plugin($ider=null, array $backtrace=null): ?string {
		if (empty($backtrace)) $backtrace = debug_backtrace();
		$file = JKNAPI::file($ider, $backtrace);
		return self::plugin_from_file($file);
	}

	/**
	 * Return the absolute path of the plugin to which the given file belongs.
	 *
	 * @param string $file A file within a plugin folder.
	 * @return null|string The path to the main plugin file.
	 */
	static function plugin_from_file(string $file): ?string {

		if (!function_exists('get_plugins')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$bdir = explode('/', plugin_basename($file))[0];

		$plugins = get_plugins();
		foreach($plugins as $file => $data) {
			if (JKNStrings::starts_with($file, $bdir)) {
				return sprintf('%s/%s', WP_PLUGIN_DIR, $file);
			}
		}

		return null;
	}
    
    /*
     * =========================================================================
     * Space ID
     * =========================================================================
     */

	/**
	 * Return the space ID of the caller.
	 * $ider conforms to the spec of JKNAPI::file.
	 *
	 * @param null $ider A file path, object, or class name (if supplied).
	 * @param array $backtrace A debug backtrace to extract a filepath from.
	 * @return null|string The space ID of the caller or identifier, if extant.
	 */
    static function sid($ider=null, array $backtrace=null): ?string {
        if (empty($backtrace)) $backtrace = debug_backtrace();
        $file = self::file($ider, $backtrace);
        return self::sid_from_file($file);
    }

	/**
	 * Return the space ID given a file.
	 *
	 * @param string $file An absolute file path.
	 * @return null|string The ID of the space in which the file lives.
	 */
    static function sid_from_file(string $file): ?string {
        foreach(self::spaces() as $space) {
            if (strpos($file, $space->path()) !== false) return $space->id();
        }

        return null;
    }
    

    /*
     * =========================================================================
     * Space
     * =========================================================================
     */

	/**
	 * Return the space of the caller.
	 * $ider conforms to the spec of JKNAPI::file.
	 *
	 * @param null $ider A file path, object, or class name (if supplied).
	 * @param array $backtrace A debug backtrace to extract a filepath from.
	 * @return JKNSpace|null The space of the caller or identifier, if extant.
	 */
    static function space($ider=null, array $backtrace=null): ?JKNSpace {
        if (empty($backtrace)) $backtrace = debug_backtrace();
        $sid = self::sid($ider, $backtrace);
        return self::space_from_sid($sid);
    }

	/**
	 * Return the space given a file.
	 *
	 * @param string $file An absolute file path.
	 * @return JKNSpace|null The space in which the file lives, if extant.
	 */
    static function space_from_file(string $file): ?JKNSpace {
        return self::space_from_sid(self::sid_from_file($file));
    }

	/**
	 * Return the space given a space ID.
	 *
	 * @param string $sid The ID of a space.
	 * @return JKNSpace|null The space, if registered.
	 */
    static function space_from_sid(string $sid): ?JKNSpace {
        return self::spaces()[$sid];
    }
    
    
    /*
     * =========================================================================
     * Module ID
     * =========================================================================
     */

	/**
	 * Return the module ID of the caller.
	 * $ider conforms to the spec of JKNAPI::file.
	 *
	 * @param null $ider A file path, object, or class name (if supplied).
	 * @param array $backtrace A debug backtrace to extract a filepath from.
	 * @return null|string The ID of the module of the caller or identifier.
	 */
    static function mid($ider=null, array $backtrace=null): ?string {
        if (empty($backtrace)) $backtrace = debug_backtrace();
        $file = self::file($ider, $backtrace);
        return self::mid_from_file($file);
    }

	/**
	 * Return the module ID given a file.
	 *
	 * @param string $file An absolute file path.
	 * @return null|string The ID of the module to which the file belongs.
	 */
    static function mid_from_file(string $file): ?string {
        $space = self::space_from_file($file);
        foreach($space->modules() as $module) {            
            if (strpos($file, $module->path()) !== false) return $module->id();
        }

        return null;
    }
    
    
    /*
     * =========================================================================
     * Module
     * =========================================================================
     */

	/**
	 * Return the module of the caller.
	 * $ider conforms to the spec of JKNAPI::file.
	 *
	 * @param null $ider A file path, object, or class name (if supplied).
	 * @param array $backtrace A debug backtrace to extract a filepath from.
	 * @return null|JKNModule The module of the caller or identifier, if extant.
	 */
    static function module($ider=null, array $backtrace=null): ?JKNModule {
        if (empty($backtrace)) $backtrace = debug_backtrace();
        $file = self::file($ider, $backtrace);
        return self::module_from_file($file);
    }

	/**
	 * Return the module given a file.
	 *
	 * @param string $file An absolute file path.
	 * @return JKNModule|null The module to which the file belongs, if extant.
	 */
    static function module_from_file(string $file): ?JKNModule {
        $space = self::space_from_file($file);
        $mid = self::mid_from_file($file);
        return $space->modules()[$mid];
    }
    
    
    /*
     * =========================================================================
     * Settings page
     * =========================================================================
     */

	/**
	 * Return the settings page of the caller with the given ID (or default).
	 * $ider conforms to the spec of JKNAPI::file.
	 *
	 * @param string|null $id The ID of the settings page within the module.
	 * @param null $ider A file path, object, or class name (if supplied).
	 * @param array|null $backtrace A debug backtrace to extract a filepath from.
	 * @return JKNSettingsPage|null The requested settings page.
	 */
    static function settings_page(string $id=null, $ider=null,
            array $backtrace=null): ?JKNSettingsPage {
        
        if (empty($backtrace)) $backtrace = debug_backtrace();
        $file = self::file($ider, $backtrace);
        $module = self::module_from_file($file);
        return self::settings_page_from_module($module, $id);
    }

	/**
	 * Return the settings page of the given module with the given ID (or
	 * default).
	 *
	 * @param JKNModule $module The module to which the settings page belongs.
	 * @param string|null $id The ID of the settings page.
	 * @return JKNSettingsPage|null The settings page.
	 */
    static function settings_page_from_module(JKNModule $module,
            string $id=null): ?JKNSettingsPage {
        
        return $module->settings_page($id);
    }
    
    
    /*
     * =========================================================================
     * Module path
     * =========================================================================
     */

	/**
	 * Return the (internal) path of the module of the caller.
	 * $ider conforms to the spec of JKNAPI::file.
	 *
	 * @param null $ider A file path, object, or class name (if supplied).
	 * @param array|null $backtrace A debug backtrace to extract a filepath from.
	 * @return null|string The path of the module of the caller or identifier.
	 */
    static function mpath($ider=null, array $backtrace=null): ?string {
        if (empty($backtrace)) $backtrace = debug_backtrace();
        $file = self::file($ider, $backtrace);
        $module = self::module_from_file($file);
        return self::mpath_from_module($module);
    }

	/**
	 * Return the (internal) path of the module with the given id.
	 *
	 * @param JKNModule $module The module.
	 * @return string Its path.
	 */
    static function mpath_from_module(JKNModule $module): string {
        return $module->path();
    }
    
    
    /*
     * =========================================================================
     * Module URL
     * =========================================================================
     */

	/**
	 * Return the (external) URL of the module of the caller.
	 * $ider conforms to the spec of JKNAPI::file.
	 *
	 * @param null $ider A file path, object, or class name (if supplied).
	 * @param array|null $backtrace A debug backtrace to extract a filepath from.
	 * @return null|string The URL of the module.
	 */
    static function murl($ider=null, array $backtrace=null): ?string {
        if (empty($backtrace)) $backtrace = debug_backtrace();
        $file = self::file($ider, $backtrace);
        $module = self::module_from_file($file);
        return self::murl_from_module($module);
    }

	/**
	 * Return the (external) URL of the given module.
	 *
	 * @param JKNModule $module The module.
	 * @return string Its URL.
	 */
    static function murl_from_module(JKNModule $module): string {
        return $module->url();
    }
    
    
    /*
     * =========================================================================
     * Registration (static interface with the JKN registry)
     * =========================================================================
     */
    
    /**
     * Return the JKN registry.
     *
     * @return JKNRegistry JKN's registry.
     */
    static function registry(): JKNRegistry {
        global $JKN;
        return $JKN->registry();
    }

	/**
	 * Add the given space to the registry and return it.
	 *
	 * @param string $id The requested ID.
	 * @param string $name The requested name.
	 * @return JKNSpace The instantiated space.
	 */
    static function create_space(string $id, string $name): JKNSpace {
        $base_file = self::file_from_backtrace(debug_backtrace());
        $space = new JKNSpace($id, $name, $base_file);
        self::registry()->register_space($space);
        return $space;
    }
    
    /**
     * Return all registered spaces.
     *
     * @return JKNSpace[] The registered spaces.
     */
    static function spaces(): array {
        return self::registry()->spaces();
    }
    
    /**
     * Return all registered modules.
     *
     * @return JKNModule[] The registered modules.
     */
    static function all_modules(): array {
        $modules = [];
        
        foreach(self::spaces() as $space) {
            $modules = array_merge($modules, $space->modules());
        }
        
        return $modules;
    }
    
    
    /*
     * =========================================================================
     * On-the-fly dependency checking, for enabling optional behaviour
     * =========================================================================
     */

	/**
	 * Return true iff the given module is active.
	 * A dependency for it must be registered, or it will be false by default.
	 *
	 * @param string $id The ID of an extant module dependency.
	 * @return bool Whether the module is active.
	 */
    static function module_dep_met(string $id): bool {
        return self::registry()->get_module_dependency($id)->met();
    }

    /**
     * Return true iff the given plugin is active.
     * A dependency for it must be registered, or it will be false by default.
     *
     * @param string $id The ID of an extant plugin dependency.
     * @return bool Whether the plugin is active.
     */
    static function plugin_dep_met(string $id): bool {
        return self::registry()->get_plugin_dependency($id)->met();
    }

    /**
     * Return true iff the given theme is active.
     * A dependency for it must be registered, or it will be false by default.
     *
     * @param string $id The ID of an extant theme dependency.
     * @return bool Whether the theme is active.
     */
    static function theme_dep_met(string $id): bool {
        return self::registry()->get_theme_dependency($id)->met();
    }


	/*
	 * =========================================================================
	 * Dependency paths and URLs
	 * =========================================================================
	 */

	/**
	 * Return the path of the module dependency with the given ID.
	 *
	 * @param string $id The dependency's ID.
	 * @return string The path.
	 */
	static function module_dep_path(string $id): string {
		return self::registry()->get_module_dependency($id)->path();
	}

	/**
	 * Return the url of the module dependency with the given ID.
	 *
	 * @param string $id The dependency's ID.
	 * @return string The URL.
	 */
	static function module_dep_url(string $id): string {
		return self::registry()->get_module_dependency($id)->url();
	}

	/**
	 * Return the path of the theme dependency with the given ID.
	 *
	 * @param string $id The dependency's ID.
	 * @return string The path.
	 */
	static function theme_dep_path(string $id): string {
		return self::registry()->get_theme_dependency($id)->path();
	}

	/**
	 * Return the url of the theme dependency with the given ID.
	 *
	 * @param string $id The dependency's ID.
	 * @return string The URL.
	 */
	static function theme_dep_url(string $id): string {
		return self::registry()->get_theme_dependency($id)->url();
	}

	/**
	 * Return the path of the plugin dependency with the given ID.
	 *
	 * @param string $id The dependency's ID.
	 * @return string The path.
	 */
	static function plugin_dep_path(string $id): string {
		return self::registry()->get_plugin_dependency($id)->path();
	}

	/**
	 * Return the url of the plugin dependency with the given ID.
	 *
	 * @param string $id The dependency's ID.
	 * @return string The URL.
	 */
	static function plugin_dep_url(string $id): string {
		return self::registry()->get_plugin_dependency($id)->url();
	}

    
    /*
     * =========================================================================
     * JKN activation and deactivation hooks
     * =========================================================================
     */

	/**
	 * Register the given callable to the deactivation of Jackknife itself.
	 *
	 * @param callable $cb The callable to add.
	 */
    static function add_jkn_deactivate_action(callable $cb): void {
        register_deactivation_hook(JKN_FILE, $cb);
    }

	/**
	 * Register the given callable to the uninstallation of Jackknife itself.
	 *
	 * @param callable $cb The callable to add.
	 */
	static function add_jkn_uninstall_action(callable $cb): void {
		register_uninstall_hook(JKN_FILE, $cb);
	}
}
