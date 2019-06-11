<?php

/**
 * An API for wrapping the qualifying, getting, and updating of WP options.
 * The possible verbs are qualify, get, and update.
 * 
 * Each one gets the module of the caller or the one the caller supplied,
 * and uses one of its settings page (identified or default) to qualify.
 * 
 * For example, you should be able to do the following in any module code:
 * 
 * JKNOpts::get(name)
 * JKNOpts::update(name, value)
 * 
 * ... and have it be appropriate for the space, module, and settings page.
 * 
 * For reference, a fully qualified option looks like this:
 *      SpaceID_ModuleID_SettingsPageID_OptionName
 * 
 * If NULL is supplied for the settings page ID, then there is no settings page
 * qualification, only module qualification:
 *      SpaceID_ModuleID_OptionName
 * This is to be discouraged since full qualification helps avoid collisions.
 */
final class JKNOpts {
    
    /*
     * =========================================================================
     * General prefix
     * =========================================================================
     */

	/**
	 * Return the given name prefixed by the JKN ID.
	 *
	 * @param string $name The name to prefix.
	 * @return string The prefixed name.
	 */
    static function prefix_jkn(string $name): string {
        return sprintf('%s_%s', JKN_ID, $name);
    }
    

    /*
     * =========================================================================
     * Option qualification using space, module and settings page
     * =========================================================================
     */

	/**
	 * Qualify a given option using the module ID of the caller and an optional
	 * settings page ID.
	 *
	 * $ider conforms to the spec of JKNAPI::file.
	 *
	 * @param string $opt The option to qualify.
	 * @param null $ider A file path, class, or object (if supplied).
	 * @param string|null $spid The ID of the settings page (default if null).
	 * @param bool $mod_q Whether to resort to module qualification.
	 * @return string The qualified option.
	 */
    static function qualify(string $opt, $ider=null, string $spid=null,
            bool $mod_q=false): string {
        
        $module = JKNAPI::module($ider, debug_backtrace());
        return self::qualify_from_module($module, $opt, $spid, $mod_q);
    }

	/**
	 * Qualify a given option given a module.
	 *
	 * @param JKNModule $module The module to use to qualify.
	 * @param string $opt The option name.
	 * @param string|null $spid The ID of the settings page (default if null).
	 * @param bool $mod_q Whether to resort to module qualification.
	 * @return string The qualified option.
	 */
    static function qualify_from_module(JKNModule $module, string $opt,
            string $spid=null, bool $mod_q=false): string {
        
        if ($mod_q) {
            return $module->qualify($opt);
        } else {
            $spage = $module->settings_page($spid);
            return $spage->qualify($opt);
        }
    }
    
    
    /*
     * =========================================================================
     * Option getting using space, module and settings page
     * =========================================================================
     */

	/**
	 * Return the value of the given option for the module of the caller and
	 * an optional settings page ID.
	 *
	 * $ider conforms to the spec of JKNAPI::file.
	 *
	 * @param string $opt The option name.
	 * @param bool $default The value to return if the option is not set.
	 * @param null $ider A file path, class or object (if supplied).
	 * @param string|null $spid The ID of the settings page (default if null).
	 * @param bool $mod_q Whether to resort to module qualification.
	 * @return mixed The value of the option.
	 */
    static function get(string $opt, $default=false, $ider=null,
            string $spid=null, bool $mod_q=false) {
        
        $module = JKNAPI::module($ider, debug_backtrace());
        return self::get_from_module($module, $opt, $default, $spid, $mod_q);
    }

	/**
	 * Return the given option as qualified by the settings page for the
	 * given mid and (optional) settings page ID.
	 *
	 * @param JKNModule $module The module to use to qualify.
	 * @param string $opt The option name.
	 * @param bool $default The value to return if the option is not set.
	 * @param string|null $spid The ID of the settings page (default if null).
	 * @param bool $mod_q Whether to resort to module qualification.
	 * @return mixed The value of the option.
	 */
    static function get_from_module(JKNModule $module, string $opt,
            $default=false, string $spid=null, bool $mod_q=false) {
        
        
        if ($mod_q) {
            return $module->get($opt, $default);
        } else {
            $spage = $module->settings_page($spid);
            return $spage->get($opt, $default);
        }
    }
    
    
    /*
	 * =========================================================================
	 * Option updating using space, module and settings page
	 * =========================================================================
	 */

	/**
	 * Update the given option with the given value and autoload setting, for
	 * the module of the caller and an optional settings page ID.     *
	 * As with WP update_option, return true iff the update succeeded.
	 *
	 * $ider conforms to the spec of JKNAPI::file.
	 *
	 * @param string $opt The option name.
	 * @param mixed $value The value to update the option with.
	 * @param bool|null $autoload Whether to autoload the option on WP load.
	 * @param null $ider A file path, class or object (if supplied).
	 * @param string|null $spid The ID of the settings page (default if null).
	 * @param bool $mod_q Whether to resort to module qualification.
	 * @return bool Whether the option was updated.
	 */
    static function update(string $opt, $value, bool $autoload=null, $ider=null,
           string $spid=null, bool $mod_q=false): bool {

        $module = JKNAPI::module($ider, debug_backtrace());
        return self::update_from_module($module, $opt, $value, $autoload,
            $spid, $mod_q);
    }

	/**
	 * Update the given option with the given value and autoload setting.
	 * Use the settings page for the given mid and (optional) settings page ID.
	 *
	 * As with WP update_option, return true iff the update succeeded.
	 *
	 * @param JKNModule $module The module to use to qualify.
	 * @param string $opt The option name.
	 * @param mixed $value The value to update the option with.
	 * @param bool|null $autoload Whether to autoload the option on WP load.
	 * @param string|null $spid The ID of the settings page (default if null).
	 * @param bool $mod_q Whether to resort to module qualification.
	 * @return bool Whether the module was updated.
	 */
    static function update_from_module(JKNModule $module, string $opt, $value,
            bool $autoload=null, string $spid=null,
            bool $mod_q=false): bool {

        if ($mod_q) {
            return $module->update($opt, $value, $autoload);
        } else {
            $spage = $module->settings_page($spid);
            return $spage->update($opt, $value, $autoload);
        }
    }

    /*
     * =========================================================================
     * Option deleting using space, module and settings page
     * =========================================================================
     */

	/**
	 * Delete the given option for the module of the caller and an optional
	 * settings page ID. As with WP delete_option, return true iff it's deleted.
	 *
	 * $ider conforms to the spec of JKNAPI::file.
	 *
	 * @param string $opt The option name.
	 * @param null $ider A file path, class or object (if supplied).
	 * @param string|null $spid The ID of the settings page (default if null).
	 * @param bool $mod_q Whether to resort to module qualification.
	 * @return bool Whether the option was deleted.
	 */
    static function delete(string $opt, $ider=null, string $spid=null,
           bool $mod_q=false): bool {

        $module = JKNAPI::module($ider, debug_backtrace());
        return self::delete_from_module($module, $opt,  $spid, $mod_q);
    }

	/**
	 * Delete the given option for the module of the caller and an optional
	 * settings page ID. As with WP delete_option, return true iff it's deleted.
	 *
	 * @param JKNModule $module The module to use to qualify.
	 * @param string $opt The option name.
	 * @param string|null $spid The ID of the settings page (default if null).
	 * @param bool $mod_q Whether to resort to module qualification.
	 * @return bool Whether the option was deleted.
	 */
    static function delete_from_module(JKNModule $module, string $opt,
           string $spid=null, bool $mod_q=false): bool {

        if ($mod_q) {
            return $module->delete($opt);
        } else {
            $spage = $module->settings_page($spid);
            return $spage->delete($opt);
        }
    }
}
