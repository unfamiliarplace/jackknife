<?php

/*
 * =============================================================================
 * The two MultiHook classes allow you to make a class or object cron-ready,
 * with the possibility of adding each hook and its callable yourself.
 *
 * Your job if you implement either one is to:
 *
 * 1. Write the callback(s).
 *
 * 2. Override activate_cron to add_action_by_hook each [hook, callback] pair.
 *
 * 3. Override deactivate_cron to clear_schedule each hook.
 *
 *      N.B. Derive the hook via hook_by_name. Pass the result to other methods.
 *      All other methods assume a qualified hook, not an unqualified name.
 *
 * 4. Call activate hook (presumably on activate or startup).
 *
 * 5. Call deactivate_cron (presumably on pause or deactivate).
 *
 * 6. Schedule crons using schedule_by_hook/schedule_single_by_hook.
 *
 *      N.B. You can also use schedule_with_first_by_hook/
 *      schedule_single_with_first_by_hook if you have a timestamp already.
 *
 * 7. Clear them during normal operation using clear_schedule_by_hook.
 * =============================================================================
 */

/**
 * A MultiHook implementation that does not allow an object instance.
 */
trait JKNCron_MultiHook_Static {
	use JKNCron;

	/*
	 * =========================================================================
	 * Must override
	 * =========================================================================
	 */

	/**
	 * (Override) Add actions on startup.
	 */
	abstract static function activate_cron(): void;

	/**
	 * (Override) Unschedule due to deactivation.
	 */
	abstract static function deactivate_cron(): void;
}
