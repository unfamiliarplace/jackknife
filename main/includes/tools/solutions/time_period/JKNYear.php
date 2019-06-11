<?php

/**
 * Represents a single year with an arbitrary root (by default Jan 1).
 */
class JKNYear extends JKNTimePeriod {

	/*
	 * =========================================================================
	 * Setup
	 * =========================================================================
	 */

	/**
	 * Store a given DateTime as a root, as well as the first day.
	 *
	 * @param DateTime $dt To derive the root. If none is supplied, use now.
	 * @param string $day_1
	 */
    function __construct(DateTime $dt=null, string $day_1='0101') {
    	$dt = (empty($dt)) ? JKNTime::dt_now() : clone $dt;
        
        // Determine which academic year this is
        $year = (int) $dt->format('Y');
        $proposed_start = JKNTime::dt(sprintf('%s%s', $year, $day_1));
        
        // If the given dt is the start of fall or later in its year, use it
        if ($dt >= $proposed_start) {
            $start = $proposed_start;
        
        // Otherwise we must back up a year
        } else {
            $prev = ((int) $year) - 1;
            $start = JKNTime::dt(sprintf('%s%s', $prev, $day_1));
        }

        // Next year is Jan 1, minus 1 second
        $next_year_dt = JKNTime::dt(sprintf('%s%s',
	        (int) $start->format('Y') + 1, $day_1));
        $next_year_dt->sub(new DateInterval('PT1S'));

        parent::__construct($start, $next_year_dt);
    }

	/*
	 * =========================================================================
	 * Period manipulation
	 * =========================================================================
	 */

	/**
	 * Get the next JKNYear.
	 * This must be overridden because years are of variable length.
	 *
	 * @return JKNTimePeriod
	 */
	function next(): JKNTimePeriod {
		return static::make_from_year(((int) $this->date('Y')) + 1);
	}

	/**
	 * Get the previous JKNYear.
	 * This must be overridden because years are of variable length.
	 *
	 * @return JKNTimePeriod
	 */
	function previous(): JKNTimePeriod {
		return static::make_from_year((int) $this->start->format('Y') - 1);
	}



	/*
	 * =========================================================================
	 * Static makers
	 * =========================================================================
	 */

	/**
	 * Return a JKNYear from a DateTime. (Current if none.)	 *
	 *
	 * @param DateTime|null $dt The DateTime to make it from.
	 * @return JKNYear|null The resulting year.
	 */
	static function make_from_dt(DateTime $dt=null): JKNYear {
		return new static($dt);
	}

	/**
	 * Return a JKNYear from an existing year format.
	 *
	 * TODO validation.
	 *
	 * @param string $format
	 * @param string $day_1 The first day. Format: 'MMDD'. Defaults to '0101'.
	 * @return JKNYear|null The resulting year.
	 * @internal param string $year The year. Format: '2017/18' (same as the output).
	 */
	static function make_from_format(string $format,
			string $day_1='0101'): ?JKNYear {

		// Extract the year (the first portion) and get the start of its fall
		$y = substr($format, 0, 4);
		$start = JKNTime::dt(sprintf('%s%s', $y, $day_1));
		return new JKNYear($start, $day_1);
	}

	/**
	 * Return a JKNYear constructed from a datestring.
	 *
	 * @param string $datestr A datestring compatible with strtotime.
	 * @param string $day_1
	 * @return JKNYear The resulting academic year.
	 */
	static function make_from_datestring(string $datestr,
			string $day_1='0101'): JKNYear {

        $dt = JKNTime::dt($datestr);
        return new JKNYear($dt, $day_1);
    }

	/**
	 * Return a JKNYear constructed from a year integer.
	 * This year is assumed to be the first, not the second half, of a format.
	 *
	 * @param int $year The year.
	 * @param string $day_1 The first day. Format: 'MMDD'. Defaults to '0101'.
	 * @return JKNYear The resulting year.
	 */
	static function make_from_year(int $year, string $day_1='0101'): JKNYear {
        $datestr = sprintf('%s%s', $year, $day_1);
        return static::make_from_datestring($datestr, $day_1);
    }


	/*
	 * =========================================================================
	 * Formatting
	 * =========================================================================
	 */
     
     /**
      * Return a formatted string representing this year.
      *
      * @return string The formatted year, either '2017' or '2017/18' style.
      */
	function format(): string {
		if ($this->start->format('Y') == $this->end->format('Y')) {
			return $this->date('Y');
		} else {
			$year   = $this->start->format('Y');
			$suffix = $this->next()->start()->format('y');
			return sprintf('%s/%s', $year, $suffix);
		}
	}

	/**
	 * Return the base year as an int.
	 *
	 * @return int
	 */
	function year(): int { return (int) $this->date('Y'); }
}
