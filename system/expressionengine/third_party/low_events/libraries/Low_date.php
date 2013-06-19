<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Low Date library class
 *
 * @package        low_events
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-events
 * @copyright      Copyright (c) 2012, Low
 */
class Low_date {

	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------

	/**
	 * Main DateTime object
	 */
	private $_date;

	/**
	 * Original date, unmodified
	 */
	private $_orig_date;

	/**
	 * Optional string [year|month|week|day] depending on initialisation
	 */
	private $_given;

	/**
	 * PHP date formats
	 */
	private $_formats = array(
		'd', 'D', 'j', 'l', 'N', 'S', 'w', 'z', 'W', 'F', 'm', 'M', 'n', 't',
		'L', 'o', 'Y', 'y', 'a', 'A', 'B', 'g', 'G', 'h', 'H', 'i', 's', 'u',
		'e', 'I', 'O', 'P', 'T', 'Z', 'c', 'r', 'U');

	/**
	 * PHP date formats that need translating
	 */
	private $_translate = array('D', 'l', 'S', 'F', 'M', 'a', 'A');

	/**
	 * Units to add/sub
	 */
	private $_units = array('year', 'month', 'week', 'day');

	/**
	 * Now!
	 */
	private $_now;

	/**
	 * Translation cache
	 */
	private static $cache = array();

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access     public
	 * @return     void
	 */
	public function __construct($date = FALSE, $time = FALSE)
	{
		$this->_date = new DateTime;
		$this->_now  = time();

		if ($date) $this->init($date, $time);
	}

	/**
	 * Sets date
	 *
	 * @access     public
	 * @return     void
	 */
	public function init($date = FALSE, $time = FALSE)
	{
		// Check and see if the date format is YYYY-MM-DD HH:II
		if ($date && strpos($date, ' '))
		{
			list($date, $time) = explode(' ', $date, 2);
		}

		// Try and see if $date is a timestamp
		if ($date && is_numeric($date) && strlen($date) != 4)
		{
			list($date, $time) = explode(' ', date('Y-m-d H:i', $date));
		}

		// Check date format
		if (preg_match('/^(\d{4})-?(\d{1,2})?-?(\d{1,2})?$/', $date, $match) ||
			preg_match('/^(\d{4})-(W)(\d{1,2})-?([1-7])?$/', $date, $match))
		{
			$date = array();

			// Week match
			if (@$match[2] == 'W')
			{
				// Check week number
				$week = str_pad($match[3], 2, '0', STR_PAD_LEFT);

				// Check day
				$day = isset($match[4]) ? $match[4] : '1';

				// Determine the actual date
				$date = explode('-', date('Y-m-d', strtotime("{$match[1]}-W{$week}-{$day}")));

				// Say a week is given
				$this->_given = 'week';
			}
			else
			{
				// Year
				$date[] = $match[1];

				// Month
				$date[] = isset($match[2]) ? $match[2] : '01';

				// Day
				$date[] = isset($match[3]) ? $match[3] : '01';

				// What's given
				switch (count($match))
				{
					case 2: $this->_given = 'year';  break;
					case 3: $this->_given = 'month'; break;
					case 4: $this->_given = 'day';   break;
				}
			}

			// Set the DateTime
			$this->_date->setDate($date[0], $date[1], $date[2]);
		}

		// Set the time if given
		if (preg_match('/^(\d{1,2}):(\d{2})$/', $time, $match))
		{
			$this->_date->setTime($match[1], $match[2]);
		}

		// Keep track of original date
		$this->_orig_date = clone $this->_date;
	}

	// --------------------------------------------------------------------

	/**
	 * Get year range: array('2012-01-01', '2012-12-31')
	 */
	public function get_year_range()
	{
		$start = $this->year() . '-01-01';
		$end = $this->_end_date($start, 'year');
		return array($start, $end);
	}

	/**
	 * Get month range: array('2012-06-01', '2012-06-30')
	 */
	public function get_month_range()
	{
		$start = $this->month_url() . '-01';
		$end = $this->_end_date($start, 'month');
		return array($start, $end);
	}

	/**
	 * Get week range: array('2012-07-09', '2012-07-15')
	 */
	public function get_week_range()
	{
		$start = date('Y-m-d', strtotime($this->week_url() . '-1'));
		$end = $this->_end_date($start, 'week');
		return array($start, $end);
	}

	/**
	 * Calculate To date based on from date and given length
	 */
	private function _end_date($from, $length)
	{
		$from = new DateTime($from);
		$from->modify("+ 1 {$length}");
		$from->modify("- 1 day");

		return $from->format('Y-m-d');
	}

	// --------------------------------------------------------------------

	/**
	 * Get next item
	 */
	public function get_next($what = 'month')
	{
		return $this->_prev_next_item('+', $what);
	}

	/**
	 * Get previous item
	 */
	public function get_prev($what)
	{
		return $this->_prev_next_item('-', $what);
	}

	/**
	 * Return previous or next item
	 */
	private function _prev_next_item($mod = '+', $what = 'month')
	{
		$date = clone $this->_date;
		$date->modify("{$mod} 1 {$what}");
		return $date->format('Y-m-d');
	}

	// --------------------------------------------------------------------

	/**
	 * Set current date to the first of the month
	 */
	public function first_of_month()
	{
		$this->_date->setDate($this->year(), $this->month(), 1);
		return $this;
	}

	/**
	 * Add a unit to the date
	 */
	public function add($unit)
	{
		if (in_array($unit, $this->_units)) $this->_date->modify("+ 1 {$unit}");
		return $this;
	}

	/**
	 * Subtract a unit to the date
	 */
	public function sub($unit)
	{
		if (in_array($unit, $this->_units)) $this->_date->modify("- 1 {$unit}");
		return $this;
	}

	/**
	 * Modify the date
	 */
	public function modify($str)
	{
		$this->_date->modify($str);
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Return the class' date
	 */
	public function date()
	{
		return $this->_date->format('Y-m-d');
	}

	/**
	 * Return year only
	 */
	public function year()
	{
		return $this->_date->format('Y');
	}

	/**
	 * Return month only
	 */
	public function month()
	{
		return $this->_date->format('m');
	}

	/**
	 * Return day only
	 */
	public function day()
	{
		return $this->_date->format('d');
	}

	/**
	 * Return YYYY-MM
	 */
	public function month_url()
	{
		return $this->_date->format('Y-m');
	}

	/**
	 * Return YYYY-Www
	 */
	public function week_url()
	{
		return $this->_date->format('o-\WW');
	}

	/**
	 * Return YYYY-MM-DD
	 */
	public function day_url()
	{
		return $this->_date->format('Y-m-d');
	}

	/**
	 * Return number of days in month
	 */
	public function days_in_month()
	{
		return $this->_date->format('t');
	}

	/**
	 * Return the time in HH:II format
	 */
	public function time()
	{
		return $this->_date->format('H:i');
	}

	/**
	 * Return date in Unix timestamp
	 */
	public function stamp()
	{
		return $this->_date->format('U');
	}

	/**
	 * Return numeric representation of first day of the month
	 */
	public function first_day_of_month()
	{
		$date = clone $this->_date;
		$date->setDate($this->year(), $this->month(), 1);
		return $date->format('N');
	}

	// --------------------------------------------------------------------

	/**
	 * Return all possible units
	 */
	public function units()
	{
		return $this->_units;
	}

	/**
	 * Return Year, Month, Week or Day
	 */
	public function given()
	{
		return $this->_given;
	}

	/**
	 * Return now
	 */
	public function now()
	{
		return $this->_now;
	}

	/**
	 * Set work date to original date
	 */
	public function reset()
	{
		$this->_date = clone $this->_orig_date;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Return an EE date format in translated form
	 * aka fuck DST, localisation and other nonsense
	 *
	 * @access     public
	 * @param      string
	 * @return     string
	 */
	public function ee_format($format = '')
	{
		if ( ! isset($this->cache[$this->stamp()][$format]))
		{
			$this->cache[$this->stamp()][$format] = preg_replace_callback(
				'/(%('.implode('|', $this->_formats).'))/',
				array($this, '_apply_format'),
				$format
			);
		}

		return $this->cache[$this->stamp()][$format];
	}

	/**
	 * Used by preg_replace_callback
	 *
	 * @see        ee_format()
	 */
	private function _apply_format($match)
	{
		$str = $this->_date->format($match[2]);

		if (in_array($match[2], $this->_translate))
		{
			// Account for May
			if ($str == 'May' && $match[2] == 'F') $str .= '_l';

			// Translate it
			$str = get_instance()->lang->line($str);
		}

		return $str;
	}

	// --------------------------------------------------------------------

}

// End Low_date class