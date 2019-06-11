<?php

/**
 * A similar tool to JKNSchedule, for an event with a single occurrence.
 * It has nullable parameters because it calculates the first occurrence
 * based on a guess about which parameters were handed to it.
 *
 * TODO Not tested in its newest format!
 */
final class JKNScheduleSingle {

	private $set_minute;
	private $set_hour;
	private $set_day;
	private $schedule;

	/**
	 * Construct the schedule, keeping track of which arguments were null.
	 *
	 * @param int|null $minute
	 * @param int|null $hour
	 * @param int|null $day
	 */
	function __construct(int $minute=null, int $hour=null, int $day=null) {
		$this->set_minute = is_null($minute);
		$this->set_hour = is_null($hour);
		$this->set_day = is_null($day);
		$this->schedule = new JKNSchedule($minute, $hour, $day);
	}

	/**
	 * Return the timestamp of the first occurrence of the event.
	 */
	function first(): int {
		if (!$this->set_minute) return $this->schedule->first_from_interval(5);
		if (!$this->set_hour) return $this->schedule->first_from_interval(60);
		if (!$this->set_day) return $this->schedule->first_from_interval(60*60);
		return $this->schedule->first_from_interval(60*60*24);
	}
}
