<?php

/*
 * =============================================================================
 * The two OneHook classes allow you to make a class or object cron-ready,
 * with the only missing element the callback to run on the schedule.
 *
 *
 * Your job if you implement either one is to:
 *
 * 1. If you want a custom hook, override the hook method using hook_by_name
 *      or any other means of adequately qualifying via JKNOpts.
 *
 * 2. Write the callback function.
 *
 * 3. Override get_cron_callback to return your callback as a callable.
 *
 *      e.g. function get_cron_callback() { return [__CLASS__, 'purge']; }
 *
 * 3. Call activate hook (presumably on activate or startup).
 *
 * 4. Call deactivate_cron (presumably on pause or deactivate).
 *
 * 5. Schedule the cron using schedule/schedule_single.
 *
 *      N.B. You can also call schedule_with_first/schedule_single_with_first
 *      if you have a strtotime-compatible timestamp already.
 *
 * 6. Clear it during normal operation using clear_schedule.
 * =============================================================================
 */

/**
 * A OneHook implementation that does not allow an object instance.
 */
trait JKNCron_OneHook_Static {
	use JKNCron;

	/*
	 * =========================================================================
	 * Must override
	 * =========================================================================
	 */

	/**
	 * Override) Return the callable to run on cron.
	 *
	 * @return callable
	 */
	abstract static function get_cron_callback(): callable;


	/*
	 * =========================================================================
	 * Optionally override
	 * =========================================================================
	 */

	/**
	 * (Optionally override) Return the qualified hook.
	 *
	 * @return string
	 */
	static function hook(): string { return static::hook_by_name('hook'); }


	/*
	 * =========================================================================
	 * Do not override
	 * =========================================================================
	 */

	/**
	 * Add the given callable to the hook.
	 */
	final static function activate_cron(): void {
		$cb = static::get_cron_callback();
		static::add_action($cb);
	}

	/**
	 * Register a schedule clear on deactivation.
	 */
	final static function deactivate_cron(): void { static::clear_schedule(); }

	/**
	 * Add the given callback to the hook.
	 *
	 * @param callable $cb
	 */
	final static function add_action (callable $cb): void {
		static::add_action_by_hook(static::hook(), $cb);
	}

	/**
	 * Return the timestamp of the next scheduled instance for the hook.
	 *
	 * @return int
	 */
	final static function next(): int {
		return static::next_by_hook(static::hook());
	}

	/**
	 * Return true iff the action is scheduled.
	 *
	 * @return bool
	 */
	final static function is_scheduled(): bool {
		return static::is_scheduled_by_hook(static::hook());
	}

	/**
	 * Clear the schedule for the hook.
	 */
	final static function clear_schedule(): void {
		static::clear_schedule_by_hook(static::hook());
	}

	/**
	 * Schedule the hook given a timestamp.
	 *
	 * @param int $first
	 * @param bool $overwrite
	 * @param string $rec
	 */
	final static function schedule_with_first(int $first, bool $overwrite=true,
		string $rec='hourly'): void {

		static::schedule_with_first_by_hook(static::hook(),
			$first, $overwrite, $rec);
	}

	/**
	 * Schedule the first occurrence given schedule information.
	 * $rec is a WP cron schedule; $min, $hour, $day are optional integers.
	 *
	 * @param bool $overwrite
	 * @param string $rec
	 * @param int|null $min The minute on which to schedule it.
	 * @param int|null $hour The hour on which to schedule it.
	 * @param int|null $day The day on which to schedule it.
	 */
	final static function schedule(bool $overwrite=true, string $rec='hourly',
		int $min=null, int $hour=null, int $day=null): void {

		static::schedule_by_hook(static::hook(),
			$overwrite, $rec, $min, $hour, $day);
	}

	/**
	 * Schedule a single occurrence given a timestamp.
	 *
	 * @param int $first
	 * @param bool $overwrite
	 */
	final static function schedule_single_with_first(int $first,
		bool $overwrite=true): void {

		static::schedule_single_with_first_by_hook(static::hook(),
			$first, $overwrite);
	}

	/**
	 * Schedule a single occurrence given schedule information.
	 * $rec is a WP cron schedule; $min, $hour, $day are optional integers.
	 *
	 * @param bool $overwrite
	 * @param int|null $min The minute on which to schedule it.
	 * @param int|null $hour The hour on which to schedule it.
	 * @param int|null $day The day on which to schedule it.
	 */
	final static function schedule_single(bool $overwrite=true,
		int $min=null, int $hour=null, int $day=null): void {

		static::schedule_single_by_hook(static::hook(),
			$overwrite, $min, $hour, $day);
	}
}
