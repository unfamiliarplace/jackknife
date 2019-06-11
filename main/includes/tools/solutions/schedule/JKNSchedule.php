<?php

/**
 * A Schedule instance allows for the easy handling of schedule information.
 * In particular, it provides for formatting based on minute, hour, and day,
 * and determining the first occurrence of an event based on those data.
 *
 * It can handle recurrences from < 1 hour to N weeks.
 * (To handle shorter or longer periods is unrealistic for WP Cron.
 * To handle < 1 minute requires keeping track of seconds.
 * To handle a month requires a non-fixed interval -- a very different system.)
 */
final class JKNSchedule {

	private $minute;
	private $hour;
	private $day;

	/*
	 * =========================================================================
	 * Set up
	 * =========================================================================
	 */

	/**
	 * Take an optional minute, hour, and day as integers.
	 * Use the first minute, hour, or day if any of the above are not supplied.
	 *
	 * @param int|null $minute
	 * @param int|null $hour
	 * @param int|null $day
	 */
	function __construct(int $minute=null, int $hour=null, int $day=null) {

		$this->minute = (is_null($minute)) ? 0 : $minute;
		$this->hour = (is_null($hour)) ? 0 : $hour;
		$this->day = (is_null($day)) ? get_option('start_of_week', 1) : $day;
	}


	/*
	 * =========================================================================
	 * Formatting
	 * =========================================================================
	 */

	/**
	 * Convert an int to a string and pad it to 2 digits.
	 *
	 * @param int $n
	 * @return string
	 */
	static function pad(int $n): string {
		return str_pad((string) $n, 2, '0', STR_PAD_LEFT);
	}

	/**
	 * Return the day as a string (a full day name), if one has been set.
	 *
	 * @param int $day
	 * @return string
	 */
	static function day_str(int $day): string {
		$time_str = sprintf('Sunday +%s days', $day);
		return date('l', strtotime($time_str));
	}


	/*
	 * =========================================================================
	 * First occurrence determination
	 * =========================================================================
	 */

	/**
	 * Return the timestamp of the first occurrence of the event for the given
	 * recurrence.
	 *
	 * @param string $recurrence
	 * @return int
	 */
	function first(string $recurrence): int {
		$interval = wp_get_schedules()[$recurrence]['interval'];
		return $this->first_from_interval($interval);
	}

	/**
	 * Return the timestamp of the first occurrence of the event for the given
	 * interval. (This is to allow non-dependence on registered schedules.)
	 *
	 * @param int|null $interval
	 * @return int
	 */
	function first_from_interval(int $interval=null): int {
		JKNTime::reset_timezone();

		// Determine the proposed upcoming date and the current date
		$upcoming = $this->upcoming($interval);
		$now = JKNTime::ts('now + 9 seconds'); // Extra time for calculation!

		// If the upcoming one hasn't taken place yet, use that
		if ($now < $upcoming) {
			return $upcoming;

			// Otherwise add the interval to it and use that
		} else {
			$interval = new DateInterval(sprintf('PT%sS', $interval));
			$dt = JKNTime::dt_timestamp($upcoming);
			$dt->add($interval);
			return $dt->getTimestamp();
		}
	}

	/**
	 * Return the timestamp for the nearest possible interpretation of the
	 * given interval, for this schedule's minute, day, and hour.
	 *
	 * @param int $interval
	 * @return int
	 */
	private function upcoming(int $interval): int {

		// If interval <= 1 hour, find the applicable minute
		if ($interval <= (60 * 60)) {
			$tstr = $this->first_this_hour();

			// If interval <= 1 day, find the applicable hour and minute
		} else if ($interval <= (60 * 60 * 24)) {
			$tstr = $this->first_this_day();

			// If interval <= a week, find the applicabnle hour, minute, and day
		} else {
			$tstr = $this->first_this_week();
		}

		// Convert to timestamp
		return JKNTime::ts($tstr);
	}

	/**
	 * Return the timestamp of the nearest usable minute this hour.
	 */
	private function first_this_hour(): string {
		$minute = self::pad($this->minute);
		return sprintf('%s:%s', JKNTime::date('H'), $minute);
	}

	/**
	 * Return the timestamp of the nearest usable minute and hour this day.
	 */
	private function first_this_day(): string {
		$hour = self::pad($this->hour);
		$minute = self::pad($this->minute);
		return sprintf('%s:%s', $hour, $minute);
	}

	/**
	 * Return the timestamp for the nearest usable minute, hour & day this week.
	 */
	private function first_this_week(): string {
		$hour = self::pad($this->hour);
		$minute = self::pad($this->minute);
		return sprintf('%s %s:%s', self::day_str($this->day), $hour, $minute);
	}
}
