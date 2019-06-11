<?php

/**
 * Represents an academic year (a year beginning in September).
 * It's a simple model with three semeters: fall, winter, and summer.
 * See the code for how it could be extended to allow more semesters.
 */
class JKNAcademicYear extends JKNYear {


	/*
	 * =========================================================================
	 * Setup
	 * =========================================================================
	 */
    
    /**
     * Store a given DateTime as a root, as well as semester starts.
     *
     * @param DateTime $dt To derive the root. If none is supplied, use now.
     * @param string $fall_day_1 Fall semester first day. Format 'MMDD'
     * @param string $winter_day_1 Winter semester first day. Format 'MMDD'
     * @param string $summer_day_1 Summer semester first day. Format 'MMDD'
     */
    function __construct(DateTime $dt=null, string $fall_day_1='0901',
			string $winter_day_1='0101', string $summer_day_1='0501') {

    	parent::__construct($dt, $fall_day_1);

    	// Make the semesters
	    $y = (int) $this->start->format('Y');
	    $y_winter = (((int) substr($winter_day_1, 0, 2)) < 9) ? $y + 1 : $y;

	    // Starts
	    $fall_start     = JKNTime::dt(sprintf('%s%s', $y, $fall_day_1));
	    $winter_start   = JKNTime::dt(sprintf('%s%s', $y_winter, $winter_day_1));
	    $summer_start   = JKNTime::dt(sprintf('%s%s', $y + 1, $summer_day_1));

	    // Ends
	    $fall_end = clone($winter_start);
	    $fall_end->sub(new DateInterval('PT1S'));
	    $winter_end = clone($summer_start);
	    $winter_end->sub(new DateInterval('PT1S'));
	    $summer_end = $this->end();

	    // Periods
	    $fall = new JKNTimePeriod($fall_start, $fall_end);
	    $winter = new JKNTimePeriod($winter_start, $winter_end);
	    $summer = new JKNTimePeriod($summer_start, $summer_end);

	    // Add
    	$this->add_period($fall, 'fall');
	    $this->add_period($winter, 'winter');
	    $this->add_period($summer, 'summer');
    }



	/*
	 * =========================================================================
	 * Period manipulation
	 * =========================================================================
	 */

	/**
	 * Get the next JKNAcademicYear.
	 *
	 * @return JKNTimePeriod
	 */
	function next(): JKNTimePeriod {
		return static::make_from_year(((int) $this->date('Y')) + 1);
	}

	/**
	 * Get the previous JKNAcademicYear.
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
	 * Return an JKNAcademicYear from an existing academic year format.
	 *
	 * TODO validation.
	 *
	 * @param string $format The year. Format: '2017/18' (same as the output).
	 * @param string $day_1 The first day. Format: 'MMDD'. Defaults to '0901'.
	 * @return JKNYear|null The resulting academic year.
	 */
    static function make_from_format(string $format,
	    string $day_1='0901'): ?JKNYear {

	    $y = substr($format, 0, 4);
	    $start = JKNTime::dt(sprintf('%s%s', $y, $day_1));
	    return new JKNAcademicYear($start, $day_1);
    }

	/**
	 * Return a JKNAcademicYear constructed from a datestring.
	 *
	 * @param string $datestr A datestring compatible with strtotime.
	 * @param string $day_1
	 * @return JKNYear The resulting academic year.
	 */
	static function make_from_datestring(string $datestr,
		string $day_1='0101'): JKNYear {

		$dt = JKNTime::dt($datestr);
		return new JKNAcademicYear($dt, $day_1);
	}

	/**
	 * Return an JKNAcademicYear constructed from a year integer.
	 * It is assumed that this year refers to the fall semester; that is,
	 * the year 2017 is assumed to be part of 2017/18, not 2016/17.
	 *
	 * @param int $year The year.
	 * @param string $day_1 The first day. Format: 'MMDD'. Defaults to '0901'.
	 * @return JKNYear The resulting year.
	 */
    static function make_from_year(int $year, string $day_1='0901'): JKNAcademicYear {
	    $datestr = sprintf('%s%s', $year, $day_1);
	    return static::make_from_datestring($datestr, $day_1);
    }


	/*
	 * =========================================================================
	 * Semester easy access
	 * =========================================================================
	 */
    
    /**
     * Return a DateTime for the beginning of the fall semester.
     *
     * @return DateTime The first day of the fall semester.
     */
     function start_of_fall(): DateTime {
     	return $this->get_period('fall')->start();
     }

	/**
	 * Return a DateTime for the end of the fall semester.
	 *
	 * @return DateTime The last day of the fall semester.
	 */
	function end_of_fall(): DateTime {
		return $this->get_period('fall')->end();
	}
     
    /**
     * Return a DateTime for the beginning of the winter semester.
     *
     * @return DateTime The first day of the winter semester.
     */
    function start_of_winter(): DateTime {
	    return $this->get_period('winter')->start();
    }

	/**
	 * Return a DateTime for the end of the winter semester.
	 *
	 * @return DateTime The last day of the winter semester.
	 */
	function end_of_winter(): DateTime {
		return $this->get_period('winter')->end();
	}
    
    /**
     * Return a DateTime for the beginning of the summer semester.
     *
     * @return DateTime The first day of the summer semester.
     */
    function start_of_summer(): DateTime {
	    return $this->get_period('summer')->start();
    }

	/**
	 * Return a DateTime for the end of the summer semester.
	 *
	 * @return DateTime The last day of the summer semester.
	 */
	function end_of_summer(): DateTime {
		return $this->get_period('summer')->end();
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
	final function format(): string {
		$year   = $this->start->format('Y');
		$suffix = $this->next()->start()->format('y');
		return sprintf('%s/%s', $year, $suffix);
	}
}
