<?php

/**
 * Creates admin noticies for indirect module state changes.
 * 
 * Activating or deactivating modules, plugins, and themes can lead to
 * the pausing or resuming of other modules. Notify if this happens.
 */
final class JKNNotices {

    /*
     * =========================================================================
     * Constants
     * =========================================================================
     */

    const m_change = 'A plugin, theme, or Jackknife module has changed state,'
        . ' and dependent modules have changed as a result.';

    const m_paused = 'The following modules have been paused:';
    const m_resumed = 'The following modules have been resumed:';
    const m_chain = 'Some of these may be chain reactions from other modules.';
    const m_review = 'Go to the Modules page to review these changes.';

    // CSS
    const cl_notice = 'jkn-notice';
    const cl_modules = 'jkn-notice-modules';


    /*
     * =========================================================================
     * Hooking
     * =========================================================================
     */

    /**
     * Add a notice that the given modules have been paused.
     *
     * @param JKNModule[] $modules The modules that have been paused.
     */
    static function add_pause_notice(array $modules): void {
        self::add_module_changes_notice($modules,
            self::m_paused, 'notice-warning');
    }

    /**
     * Add a notice that the given modules have been resumed.
     *
     * @param JKNModule[] $modules The modules that have been resumed.
     */
    static function add_resume_notice(array $modules): void {
        self::add_module_changes_notice($modules,
            self::m_resumed, 'notice-success');
    }

    /**
     * Add a notice about a change in state of the given modules.
     *
     * @param JKNModule[] $modules The modules that have changed.
     * @param string $intro An introductory message.
     * @param string $class A class to apply to the notice.
     */
    static function add_module_changes_notice(array $modules, string $intro,
              string $class): void {

        $module_html = self::format_modules($modules);
        $html = sprintf('%s<br><strong>%s</strong>%s%s<br>%s', self::m_change,
            $intro, $module_html, self::m_chain, self::m_review);

        $classes = [$class, 'is-dismissible'];
        $notice = self::format_notice($html, $classes);
        self::add_notice($notice);
    }

    /**
     * Hook the given notice HTML to admin_notices.
     *
     * @param string $notice The notice HTML to output.
     */
    static function add_notice(string $notice): void {
        add_action('admin_notices', function() use ($notice) {
            echo $notice;
        });
    }


    /*
     * =========================================================================
     * Formatting
     * =========================================================================
     */

    /**
     * Return a formatted admin notice with the proper classes and minor CSS.
     *
     * @param string $notice The notice text.
     * @param string[] $classes The classes to apply to the div.
     * @return string The complete notice HTML.
     */
    static function format_notice(string $notice, array $classes): string {
        $classes = array_merge(['notice'], $classes, [self::cl_notice]);
        return sprintf('%s<div class="%s">%s</div>', self::format_style(),
            implode(' ', $classes),  $notice);
    }

    /**
     * Return a formatted list of module names.
     *
     * @param JKNModule[] $modules The modules to format.
     * @return string An unordered list of module names.
     */
    static function format_modules(array $modules): string {
        return JKNLayouts::list($modules, [__CLASS__, 'format_module'],
            'ul', self::cl_modules);
    }

    /**
     * Return a formatted module name.
     *
     * @param JKNModule $module The module to format.
     * @return string The formatted module.
     */
    static function format_module(JKNModule $module): string {
        return sprintf('%s: %s', $module->space()->name(), $module->name());
    }

    /**
     * Return formatted CSS for an admin notice.
     *
     * @return string The formatted CSS.
     */
    static function format_style(): string {
        return JKNCSS::tag('
        
            .'.self::cl_notice.'{
                padding-top: 10px;
                padding-bottom: 10px;
            }
            
            .'.self::cl_modules.' li {
                list-style-type: none;
                margin-left: 15px;
            }
            
        ');
    }
}
