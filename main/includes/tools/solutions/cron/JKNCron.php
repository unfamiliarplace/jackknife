<?php

/**
 * JKNCron provides methods for simplifying basic cron job scheduling.
 * 
 * TODO Currently cannot supply arguments to callbacks.
 * That's because clearing the schedule or getting the next occurrence requires
 * having the original arguments supplied when scheduling. Too much for now.
 */

/*
 * =============================================================================
 * Provides the core overlay on WP cron functions.
 * N.B. All methods but hook_by_hook assume they're passed a hook, not a name.
 * =============================================================================
 */
trait JKNCron {

	/**
	 * Return the qualified hook for the given name.
	 *
	 * @param string $name
	 * @return string
	 */
    final static function hook_by_name(string $name): string {
        $module = JKNAPI::module(static::class);        
        $prefixed = sprintf('%s_%s', static::class, $name);
        return $module->qualify($prefixed);
    }

	/**
	 * Add a given action to the given hook.
	 *
	 * @param string $hook
	 * @param callable $cb
	 */
    final static function add_action_by_hook(string $hook, callable $cb): void {
        add_action($hook, $cb);
    }

	/**
	 * Return the next scheduled instance for the given hook.
	 *
	 * @param string $hook
	 * @return int The timestamp of the next schedule instance.
	 */
    final static function next_by_hook(string $hook): int {
        return wp_next_scheduled($hook);
    }

	/**
	 * Return true iff a schedule exists for the given hook.
	 *
	 * @param string $hook
	 * @return bool
	 */
    final static function is_scheduled_by_hook(string $hook): bool {
        return (bool) static::next_by_hook($hook);
    }

	/**
	 * Clear the schedule for the given hook.
	 *
	 * @param string $hook
	 */
    final static function clear_schedule_by_hook(string $hook): void {
        
        // First clear the hook
        wp_clear_scheduled_hook($hook);

        // Then be thorough, just in case a next event remains scheduled...
        $next = static::next_by_hook($hook);
        if ($next) wp_unschedule_event($next, $hook);
    }

	/**
	 * Schedule first occurrence for the given hook and timestamp.
	 * Overwrite an existing one iff $overwrite is true.
	 *
	 * @param string $hook
	 * @param int $first The timestamp of the first occurrence.
	 * @param bool $overwrite
	 * @param string $rec A registered WP cron schedule.
	 */
    final static function schedule_with_first_by_hook(string $hook, int $first,
            bool $overwrite=true, string $rec='hourly') {
        
        JKNTime::reset_timezone();
        if ($overwrite || empty(static::next_by_hook($hook))) {
            static::clear_schedule_by_hook($hook);
            wp_schedule_event($first, $rec, $hook);
        }
    }

	/**
	 * Schedule first occurrence for the given hook and schedule information.
	 * $rec is a WP cron schedule; $min, $hour, $day are optional integers.
	 * Overwrite an existing one iff $overwrite is true.
	 *
	 * @param string $hook
	 * @param bool $overwrite
	 * @param string $rec A registered WP cron schedule.
	 * @param int|null $min The minute on which to schedule it.
	 * @param int|null $hour The hour on which to schedule it.
	 * @param int|null $day The day on which to schedule it.
	 */
    final static function schedule_by_hook(string $hook, bool $overwrite=true,
            string $rec='hourly', int $min=null, int $hour=null, int $day=null) {
        
        $schedule = new JKNSchedule($min, $hour, $day);
        $first = $schedule->first($rec);        
        static::schedule_with_first_by_hook($hook, $first, $overwrite, $rec);
    }

	/**
	 * Schedule a single occurrence for the given hook and timestamp.
	 * Overwrite an existing one iff $overwrite is true.
	 *
	 * @param string $hook
	 * @param int $first
	 * @param bool $overwrite
	 */
    final static function schedule_single_with_first_by_hook(string $hook,
            int $first, bool $overwrite=true) {
        
        JKNTime::reset_timezone();
        if ($overwrite || empty(static::next())) {
            static::clear_schedule_by_hook($hook);
            wp_schedule_single_event($first, $hook);
        }
    }

	/**
	 * Schedule a single occurrence for the given hook and schedule information.
	 * $rec is a WP cron schedule; $min, $hour, $day are optional integers.
	 * Overwrite an existing one iff $overwrite is true.
	 *
	 * @param string $hook
	 * @param bool $overwrite
	 * @param int|null $min The minute on which to schedule it.
	 * @param int|null $hour The hour on which to schedule it.
	 * @param int|null $day The day on which to schedule it.
	 */
    final static function schedule_single_by_hook(string $hook,
            bool $overwrite=true, int $min=null, int $hour=null, int $day=null) {
        
        $schedule = new JKNScheduleSingle($min, $hour, $day);
        $first = $schedule->first();
        static::schedule_single_with_first_by_hook($hook, $first, $overwrite);
    }
}
