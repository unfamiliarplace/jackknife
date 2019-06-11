<?php

/**
 * Represent a time period from start to end.
 * Can hold sub-periods.
 * Minimum resolution is 1 second.
 *
 */
class JKNTimePeriod {

	/**
	 * @var DateTime $start The start of this period.
	 */
	protected $start;

	/**
	 * @var DateTime $end The end of this period (inclusive).
	 */
	protected $end;

	/**
	 * @var JKNTimePeriod[] $periods Subperiods.
	 */
	protected $periods;

	/**
	 * Set the start and end.
	 *
	 * @param DateTime $start
	 * @param DateTime $end
	 */
	function __construct(DateTime $start, DateTime $end) {
		$this->start = $start;
		$this->end = $end;
	}

	/*
	 * =========================================================================
	 * Period manipulation
	 * =========================================================================
	 */

	/**
	 * Return the length of this period.
	 *
	 * @return int The length.
	 */
	final function length(): int { return $this->end->diff($this->start); }

	/**
	 * Return a cloned start of this period.
	 *
	 * @return DateTime The start, cloned.
	 */
	final function start(): DateTime { return clone($this->start); }

	/**
	 * Return the end of this period.
	 *
	 * @return DateTime The end.
	 */
	final function end(): DateTime { return clone($this->end); }

	/**
	 * Return the next period of the same duration.
	 *
	 * @return JKNTimePeriod The next period.
	 */
	function next(): JKNTimePeriod {
		$next_start = $this->end();
		$next_start->add(new DateInterval('PT1S'));
		$next_end = $this->end();
		$next_end->add(new DateInterval(sprintf('PT%sS', $this->length())));
		return new JKNTimePeriod($next_start, $next_end);
	}

	/**
	 * Return the previous period of the same duration.
	 *
	 * @return JKNTimePeriod The previous period.
	 */
	function previous(): JKNTimePeriod {
		$prev_start = $this->start();
		$prev_start->sub(new DateInterval(sprintf('PT%sS', $this->length())));
		$prev_end = $this->start();
		$prev_end->sub(new DateInterval('PT1S'));
		return new JKNTimePeriod($prev_start, $prev_end);
	}


	/*
	 * =========================================================================
	 * General time
	 * =========================================================================
	 */

	/**
	 * Return true iff the given DateTime falls within this period.
	 *
	 * @param DateTime $dt The DateTime to check.
	 * @return bool Whether it falls within this period.
	 */
	final function contains(DateTime $dt): bool {
		return ($dt >= $this->start) && ($dt <= $this->end);
	}

	/**
	 * Return true iff this period is the same as the given one.
	 *
	 * @param JKNTimePeriod $other The other JKNTimePeriod to check.
	 * @return bool Whether they refer to the same period.
	 */
	final function is(JKNTimePeriod $other): bool {
		return $this->start == $other->start();
	}


	/*
	 * =========================================================================
	 * Subperiods
	 * =========================================================================
	 */

	/**
	 * Return the subperiods.
	 *
	 * @return JKNTimePeriod[]
	 */
	final function periods(): array { return $this->periods; }

	/**
	 * Attempt to add a new subperiod and return whether it was added.
	 * It must not begin before this one nor end after it.
	 *
	 * @param JKNTimePeriod $period The subperiod.
	 * @param string|null $key The key to key it by.
	 * @return bool
	 */
	final function add_period(JKNTimePeriod $period, ?string $key=null): bool {

		// Null if start is before this period's or end is after this period's
		if ($period->start() < $this->start) return false;
		if ($period->end() > $this->end) return false;

		// Normal index if no key supplied, otherwise use key
		if (is_null($key)) {
			$this->periods[] = $period;
		} else {
			$this->periods[$key] = $period;
		}

		return true;
	}

	/**
	 * Return the subperiod with the given key.
	 *
	 * @param string $key The key to check.
	 * @return JKNTimePeriod The period.
	 */
	final function get_period(string $key): ?JKNTimePeriod {
		if (isset($this->periods[$key])) return $this->periods[$key];
		return null;
	}


	/*
	 * =========================================================================
	 * Formatting
	 * =========================================================================
	 */

	/**
	 * Return the result of using a PHP date format on the period's start.
	 *
	 * @param string $format A PHP date format string.
	 * @return string The same applied to this period's start.
	 */
	final function date(string $format): string {
		return $this->start->format($format);
	}
}
