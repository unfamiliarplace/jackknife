<?php

/**
 * Provides functions for dealing with strings.
 */
final class JKNTime {

	/**
	 * Return the timestamp for now + the given recurrence's length.
	 *
	 * @param string $recurrence A registered WP cron schedule.
	 * @return int
	 */
	static function elapse(string $recurrence): int {
		$interval = wp_get_schedules()[$recurrence]['interval'];
		return self::elapse_interval($interval);
	}

	/**
	 * Return the timestamp for now + the given number of seconds.
	 *
	 * @param int $interval The number of seconds to elapse.
	 * @return int
	 */
	static function elapse_interval(int $interval): int {
		$date_interval = new DateInterval(sprintf('PT%sS', $interval));

		$dt = self::dt_now();
		$dt->add($date_interval);
		return $dt->getTimestamp();
	}

	/**
	 * Return the name of the day of the week given its integer representation.
	 *
	 * @param int $day_of_week 0 - 6 where 0 is Sunday.
	 * @return string
	 */
	static function day_name(int $day_of_week): string {
		$time_str = sprintf('Sunday +%s days', $day_of_week);
		return date('l', strtotime($time_str));
	}

	/**
	 * Set the default timezone.
	 *
	 * N.B. This is mainly because I was going crazy working with times earlier
	 * and the timezone changed between different plugins... I think it's fixed
	 * via php.ini, but this makes sure.
	 *
	 * https://xkcd.com/1883/
	 *
	 * @param string $tz
	 */
	static function reset_timezone(string $tz=JKN_TIMEZONE) {
		date_default_timezone_set($tz);
	}

	/**
	 * Return the timestamp for the given strtotime-compatible string.
	 *
	 * @param string $tstr
	 * @return int
	 */
	static function ts(string $tstr): int {
		$dt = self::dt($tstr);
		return $dt->getTimestamp();
	}

	/**
	 * Return a formatted date for the given timestamp with a guaranteed
	 * timezone.
	 *
	 * @param string $format A usual PHP date format string.
	 * @param int|null $ts
	 * @return string
	 */
	static function date(string $format, int $ts=null): string {
		$dt = (empty($ts)) ? self::dt_now() : self::dt_timestamp($ts);
		return $dt->format($format);
	}

	/**
	 * Return a DateTime with a guaranteed timezone.
	 * The given datestring is compatible with strtotime.
	 *
	 * @param string $datestr
	 * @param string $tz
	 * @return DateTime
	 */
	static function dt(string $datestr, string $tz=JKN_TIMEZONE): DateTime {
		$datestr_tz = sprintf('%s %s', $datestr, $tz);
		return new DateTime($datestr_tz, new DateTimeZone($tz));
	}

	/**
	 * Return a DateTime with a guaranteed timezone for the present moment.
	 *
	 * @param string $tz
	 * @return DateTime
	 */
	static function dt_now(string $tz=JKN_TIMEZONE): DateTime {
		return self::dt('now', $tz);
	}

	/**
	 * Return a DateTime with a guaranteed timezone from a Unix timestamp.
	 *
	 * @param int $ts
	 * @param string $tz
	 * @return DateTime
	 */
	static function dt_timestamp(int $ts, string $tz=JKN_TIMEZONE): DateTime {
		// Timezone is ignored by DateTime constructor if it's a Unix timestamp
		return self::dt(date('Y-m-d H:i:s', $ts), $tz);
	}

	/**
	 * Get a DateTime with a guaranteed timezone from a given post.
	 *
	 * @param WP_Post $p
	 * @param string $tz
	 * @return DateTime
	 */
	static function dt_post(WP_Post $p, string $tz=JKN_TIMEZONE): DateTime {
		return self::dt_pid($p->ID, $tz);
	}

	/**
	 * Get a DateTime with a guaranteed timezone from a post ID.
	 *
	 * @param string $pid
	 * @param string $tz
	 * @return DateTime
	 */
	static function dt_pid(string $pid, string $tz=JKN_TIMEZONE): DateTime {
		return self::dt(get_the_time('Y-m-d H:i:s', $pid), $tz);
	}

	/**
	 * Return a DateTime for the start of the supplied DateTime.
	 * If none is supplied, use the start of this week.
	 * Uses the "Week starts on?" option from WP. Monday is the default.
	 *
	 * @param DateTime|null $dt
	 * @return DateTime
	 */
	static function dt_start_of_week(DateTime $dt=null): DateTime {

		// If they supplied a DateTime, clone it; otherwise use now.
		if (!empty($dt)) {
			$dt = clone $dt;
		} else {
			$dt = self::dt_now();
		}

		// Get the day the week starts on as an integer (Sunday is 0)
		$day = get_option('start_of_week', 1);
		$name = self::day_name($day);

		// Add a day and get the most recent previous instance of the day
		$dt->add(new DateInterval('P1D'));
		return self::dt(sprintf('last %s %s', $name, $dt->format('Ymd H:i:s')));
	}
}
