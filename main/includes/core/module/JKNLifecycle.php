<?php

/**
 * Represents a module's state and triggers deactivation and admin notices.
 */
final class JKNLifecycle {

    // Module states
    const UNKNOWN               = -1;
    const OFF_CANNOT_ACTIVATE   =  0;
    const OFF_CAN_ACTIVATE      =  1;
    const PAUSE_CANNOT_ACTIVATE =  2;
    const PAUSE_CAN_ACTIVATE    =  3;
    const ON_CAN_ACTIVATE       =  4;
    const FORCED_OFF            =  5;   // JKN or module plugin is deactivated

    /*
     * =========================================================================
     * Starter
     * =========================================================================
     */

	/**
	 * Determine and perform the appropriate lifecycle action for each module.
	 * Also save each module's state.
	 *
	 * @param JKNModule[] $modules The modules to lifecycle.
	 */
    static function lifecycle(array $modules): void {

        $modules = self::order_modules($modules);
        self::activate_modules(self::to_activate($modules));
        self::start_up_modules(self::to_start_up($modules));
        self::resume_modules(self::to_resume($modules));
        self::pause_modules(self::to_pause($modules));
        self::deactivate_modules(self::to_deactivate($modules));
        self::save_module_states($modules);
    }

	/**
	 * Activate the given modules.
	 *
	 * @param JKNModule[] $modules The modules to activate.
	 */
    static function activate_modules(array $modules): void {
        foreach($modules as $module) {
            $module->activate();
        }
    }

	/**
	 * Start up the given modules. Also schedule their shutdown.
	 *
	 * @param JKNModule[] $modules The modules to start up.
	 */
    static function start_up_modules(array $modules): void {
        foreach($modules as $module) {
            $module->start_up();
            add_action('wp_shutdown', function() use ($module) {
                $module->shut_down();
            });
        }
    }

	/**
	 * Resume the given modules.
	 *
	 * @param JKNModule[] $modules The modules to resume.
	 */
    static function resume_modules(array $modules): void {
        foreach($modules as $module) {
            $module->resume();
        }
    }

	/**
	 * Pause the given modules in reverse order.
	 *
	 * @param JKNModule[] $modules The modules to pause.
	 */
    static function pause_modules(array $modules): void {
        $modules = array_reverse($modules);
        foreach($modules as $module) {
            $module->pause();
        }
    }

	/**
	 * Deactivate the given modules in reverse order.
	 *
	 * @param JKNModule[] $modules The modules to deactivate.
	 */
    static function deactivate_modules(array $modules): void {
        $modules = array_reverse($modules);
        foreach($modules as $module) {
            $module->deactivate();
        }
    }

	/**
	 * Deactivate all modules in reverse order.
	 */
	static function force_deactivate(): void {
		$modules = array_reverse(self::order_modules(JKNAPI::all_modules()));
		foreach($modules as $module) {
			$module->force_deactivate();
		}
	}

	/**
	 * Uninstall all modules in reverse order.
	 */
	static function force_uninstall(): void {
		$modules = array_reverse(self::order_modules(JKNAPI::all_modules()));
		foreach($modules as $module) {
			$module->force_uninstall();
		}
	}

	/**
	 * Save the state of each of the given modules.
	 *
	 * @param JKNModule[] $modules The modules to save the states of.
	 */
    static function save_module_states(array $modules): void {
    	foreach($modules as $module) {
    		$module->save_state();
	    }
    }


    /*
     * =========================================================================
     * Notices
     * =========================================================================
     */

	/**
	 * Check for modules that are newly paused or resumed and create notices.
	 */
    static function notify_state_changes(): void {

        // Get and order all modules
        $modules = self::order_modules(JKNAPI::all_modules());

        $to_resume = self::to_notify_resume($modules);
        $to_pause = self::to_notify_pause($modules);

        if ($to_resume) JKNNotices::add_resume_notice($to_resume);
        if ($to_pause) JKNNotices::add_pause_notice($to_pause);
    }


    /*
     * =========================================================================
     * State change filters
     * =========================================================================
     */

    /**
     * Return the modules that were OFF and are now ON.
     *
     * @param JKNModule[] $modules  The modules to filter.
     * @return JKNModule[] The modules to activate.
     */
    private static function to_activate(array $modules): array {
        $filter = function(JKNModule $m): bool {
            $saved = $m->saved_state();
            $current = $m->current_state();
            return (($current == self::ON_CAN_ACTIVATE) && (in_array($saved,
                    [self::OFF_CANNOT_ACTIVATE, self::ON_CAN_ACTIVATE,
	                    self::FORCED_OFF])));
        };

        return array_filter($modules, $filter);
    }

    /**
     * Return the modules that were ON and are now OFF.
     *
     * @param JKNModule[] $modules  The modules to filter.
     * @return JKNModule[] The modules to deactivate.
     */
    private static function to_deactivate(array $modules): array {
        $filter = function(JKNModule $m): bool {

	        // Do the state check
            $saved = $m->saved_state();
            $current = $m->current_state();

            return (($saved == self::ON_CAN_ACTIVATE) && (in_array($current, [
	             	self::OFF_CANNOT_ACTIVATE, self::OFF_CAN_ACTIVATE])));
        };

        return array_filter($modules, $filter);
    }

    /**
     * Return the modules that are ON and can activate.
     *
     * @param JKNModule[] $modules  The modules to filter.
     * @return JKNModule[] The modules to start up.
     */
    private static function to_start_up(array $modules): array {
        $filter = function(JKNModule $m): bool {
            $current = $m->current_state();
            return $current == self::ON_CAN_ACTIVATE;
        };

        return array_filter($modules, $filter);
    }

    /**
     * Return the modules that were PAUSE and are now ON.
     *
     * @param JKNModule[] $modules  The modules to filter.
     * @return JKNModule[] The modules to resume.
     */
    private static function to_resume(array $modules): array {
        $filter = function(JKNModule $m): bool {
            $saved = $m->saved_state();
            $current = $m->current_state();
            return (($current == self::ON_CAN_ACTIVATE) && (in_array($saved,
                    [self::PAUSE_CANNOT_ACTIVATE,
                        self::PAUSE_CAN_ACTIVATE])));
        };

        return array_filter($modules, $filter);
    }

    /**
     * Return the modules that were PAUSE CANNOT ACTIVATE and are now ON.
     *
     * @param JKNModule[] $modules  The modules to filter.
     * @return JKNModule[] The modules to notify about resuming.
     */
    private static function to_notify_resume(array $modules): array {
        $filter = function(JKNModule $m): bool {
            $saved = $m->saved_state();
            $current = $m->current_state();
            return (($current == self::ON_CAN_ACTIVATE) &&
                ($saved == self::PAUSE_CANNOT_ACTIVATE));
        };

        return array_filter($modules, $filter);
    }

    /**
     * Return the modules that were ON and are now PAUSE.
     *
     * @param JKNModule[] $modules  The modules to filter.
     * @return JKNModule[] The modules to pause.
     */
    private static function to_pause(array $modules): array {
        $filter = function(JKNModule $m): bool {
            $saved = $m->saved_state();
            $current = $m->current_state();
            return (($saved == self::ON_CAN_ACTIVATE) && (in_array($current,
                    [self::PAUSE_CANNOT_ACTIVATE,
                        self::PAUSE_CAN_ACTIVATE])));
        };

        return array_filter($modules, $filter);
    }

    /**
     * Return the modules that were ON and are now PAUSE CANNOT ACTIVATE.
     *
     * @param JKNModule[] $modules  The modules to filter.
     * @return JKNModule[] The modules to notify about pauseing.
     */
    private static function to_notify_pause(array $modules): array {
        $filter = function(JKNModule $m): bool {
            $saved = $m->saved_state();
            $current = $m->current_state();
            return (($saved == self::ON_CAN_ACTIVATE) &&
                ($current == self::PAUSE_CANNOT_ACTIVATE));
        };

        return array_filter($modules, $filter);
    }

	/*
	 * =========================================================================
	 * Module ordering
	 * =========================================================================
	 */

	/**
	 * Return the given modules in order.
	 * For any module x that depends on another module y, x comes after y.
	 *
	 * @param JKNModule[] $modules The modules to order.
	 * @return JKNModule[] The ordered modules.
	 */
	static function order_modules(array $modules): array {
		$n = count($modules);

		// Prepare enough slots that each module could have its own if needed
		// All the modules go in the first slot
		$slots = [$modules];
		for ($i = 0; $i < ($n-1); $i++) {
			$slots[] = [];
		}

		// Go through each slot till you come to an empty one
		for ($i = 0; $i < $n-1; $i++) {
			if (empty($slots[$i])) break;

			// Modules that depend on no other from the slot
			$survivors = [];

			// For each item in the slot, compare it to every other
			foreach($slots[$i] as $a) {
				foreach($slots[$i] as $b) {

					// If I depend on you, I go to the next slot
					if ($a !== $b &&
					    $a->depends_on($b) && !($b->depends_on($a))) {

						$slots[$i+1][] = $a;
						break;
					}
				}

				// If I depended on no one else, I stay in this slot
				if (!in_array($a, $slots[$i+1])) $survivors[] = $a;
			}

			// Update this slot with the remainers
			$slots[$i] = $survivors;
		}

		// Flatten all slots
		$modules = JKNArrays::flatten_2D($slots);

		// Re-index by ID
		$indexed = [];
		foreach($modules as $module) {
			$indexed[$module->id()] = $module;
		}

		return $indexed;
	}
}
