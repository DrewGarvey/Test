<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Low Events Event Model class
 *
 * @package        low_events
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-events
 * @copyright      Copyright (c) 2012, Low
 */
class Low_events_event_model extends Low_events_model {

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access      public
	 * @return      void
	 */
	function __construct()
	{
		// Call parent constructor
		parent::__construct();

		// Initialize this model
		$this->initialize(
			'low_events',
			'event_id',
			array(
				'site_id'    => 'int(4) unsigned NOT NULL DEFAULT 1',
				'entry_id'   => 'int(10) unsigned NOT NULL',
				'field_id'   => 'int(6) unsigned NOT NULL',
				'start_date' => 'date NOT NULL',
				'start_time' => 'time',
				'end_date'   => 'date',
				'end_time'   => 'time',
				'all_day'    => "ENUM('y','n') NOT NULL DEFAULT 'n'"
			)
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Installs given table
	 *
	 * @access      public
	 * @return      void
	 */
	public function install()
	{
		// Call parent install
		parent::install();

		// Add indexes to table
		foreach (array('entry_id', 'field_id', 'site_id', 'start_date', 'end_date') AS $field)
		{
			$this->EE->db->query("ALTER TABLE {$this->table()} ADD INDEX (`{$field}`)");
		}
	}

	// --------------------------------------------------------------

	/**
	 * Return attributes for entry returning
	 *
	 * @access      public
	 * @param       string
	 * @param       bool
	 * @return      array
	 */
	public function entry_attributes($prefix = '', $time = FALSE)
	{
		// Default attributes to fetch
		$attrs = array('entry_id', 'start_date', 'end_date');

		// Add time attributes?
		if ($time)
		{
			$attrs = array_merge($attrs, array('start_time', 'end_time'));
		}

		// Add prefix to attributes?
		if ($prefix)
		{
			foreach ($attrs AS &$attr)
			{
				$attr = $prefix.$attr;
			}
		}

		// Return the attributes
		return $attrs;
	}

	// --------------------------------------------------------------

	/**
	 * Replace into record into DB
	 *
	 * @access      public
	 * @param       array     data to replace
	 * @return      int
	 */
	public function replace($data = array())
	{
		if (empty($data))
		{
			// loop through attributes to get posted data
			foreach ($this->attributes() AS $attr)
			{
				if (($val = $this->EE->input->post($attr)) !== FALSE)
				{
					$data[$attr] = $val;
				}
			}
		}

		// Insert data and return inserted id
		$sql = $this->EE->db->insert_string($this->table(), $data);
		$sql = str_replace('INSERT', 'REPLACE', $sql);

		return $this->EE->db->query($sql);
	}

	// --------------------------------------------------------------

	/**
	 * Retrieve a list of records
	 *
	 * @access      private
	 * @return      array
	 */
	private function _get_rows()
	{
		// Execute query and return list of entry ids
		$query = $this->EE->db->select()
	           ->from($this->table())
	           ->order_by('start_date', 'asc')
	           ->order_by('start_time', 'asc')
	           ->get();

	    $rows = $query->result_array();

	    // clean up
	    unset($query);

		return $rows;
	}

	/**
	 * Get all [upcoming|passed] rows
	 *
	 * @access     private
	 * @param      bool
	 * @return     array
	 */
	private function _get_all($start, $time, $upcoming = TRUE)
	{
		// Set start to now if not given
		if ($start === FALSE)
		{
			$start = date('Y-m-d');
		}

		// Upcoming or passed?
		if ($upcoming)
		{
			$key = 'upcoming:';
			$oper = '>=';
		}
		else
		{
			$key = 'passed:';
			$oper = '<';
		}

		// Cache key
		$key .= trim($start . ' ' . $time);

		// Get from DB if not in cache
		if ( ! ($rows = low_get_cache(LOW_EVENTS_PACKAGE, $key)))
		{
			// Get all entry ids where the end date hasn't past
			if ($time !== FALSE)
			{
				if ($upcoming)
				{
					$sql = "(end_date > '{$start}')	OR "
					     . "(end_date = '{$start}' AND (all_day = 'y' OR (all_day = 'n' AND end_time > '{$time}')))";
				}
				else
				{
					$sql = "(end_date < '{$start}') OR "
					     . "(end_date = '{$start}' AND (all_day = 'n' AND end_time < '{$time}'))";
				}

				$this->EE->db->where("({$sql})", NULL, FALSE);
			}
			else
			{
				$this->EE->db->where("end_date {$oper}", $start);
			}

			// Add to cache array
			$rows = $this->_get_rows();

			// Register the cache
			low_set_cache(LOW_EVENTS_PACKAGE, $key, $rows);
		}

		return $rows;
	}

	/**
	 * Get all upcoming event ids
	 *
	 * @access     public
	 * @param      string    YYYY-MM-DD format
	 * @return     array
	 */
	public function get_upcoming($start = FALSE, $time = FALSE)
	{
		return $this->_get_all($start, $time, TRUE);
	}

	/**
	 * Get all passed event ids
	 *
	 * @access     public
	 * @param      string    YYYY-MM-DD format
	 * @return     array
	 */
	public function get_passed($start = FALSE, $time = FALSE)
	{
		return $this->_get_all($start, $time, FALSE);
	}

	/**
	 * Get range of events
	 *
	 * @access     public
	 * @param      string    YYYY-MM-DD format
	 * @param      string    YYYY-MM-DD format
	 * @param      bool      Include the end date?
	 * @return     array
	 */
	public function get_range($start, $end, $include = TRUE)
	{
		// Set cache key
		$key = $start.':'.$end.':'.($include ? '1' : '0');

		// Get cache
		$ranges = low_get_cache(LOW_EVENTS_PACKAGE, 'ranges');

		if ( ! isset($ranges[$key]))
		{
			// Compose where clause
			$this->where_range($start, $end, $include);

			// Add to cache array
			$ranges[$key] = $this->_get_rows();

			// Register the cache
			low_set_cache(LOW_EVENTS_PACKAGE, 'ranges', $ranges);
		}

		return $ranges[$key];
	}

	/**
	 * Sets where clause for range
	 */
	public function where_range($start, $end, $include = TRUE, $prefix = '')
	{
		$sql_start = $this->EE->db->escape_str($start);
		$sql_end = $this->EE->db->escape_str($end);
		$sql_operator = $include ? '<=' : '<';
		$sql_prefix = $prefix ? $prefix.'.' : '';

		// Compose where clause
		$where = array(
			// All events that start in between the range
			"({$sql_prefix}start_date >= '{$sql_start}' AND {$sql_prefix}start_date {$sql_operator} '{$sql_end}')",
			// All events that start before the end of the range, and end after the start of the range
			"({$sql_prefix}start_date {$sql_operator} '{$sql_end}' AND {$sql_prefix}end_date >= '{$sql_start}')"
		);

		// Add where clause to query
		$this->EE->db->where('('. implode(' OR ', $where) .')', NULL, FALSE);
	}

} // End class

/* End of file low_events_event_model.php */