<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include config file
include(PATH_THIRD.'low_events/config.php');

/**
 * Low Events Module class
 *
 * @package        low_events
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-events
 * @copyright      Copyright (c) 2012, Low
 */
class Low_events {

	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------

	/**
	 * EE Superobject
	 *
	 * @access      private
	 * @var         object
	 */
	private $EE;

	/**
	 * Custom channel:entries params
	 *
	 * @access     private
	 * @var        array
	 */
	private $params = array(
		'date',
		'unit',
		'show_passed',
		'show_active',
		'events_field'
	);

	/**
	 * Date formats for variables
	 *
	 * @access     private
	 * @var        array
	 */
	private $formats = array(
		'%F' => 'month',
		'%m' => 'month_num',
		'%M' => 'month_short',
		'%n' => 'month_num_short',
		'%Y' => 'year',
		'%y' => 'year_short'
	);

	/**
	 * Weekdays and their ISO numeric representation
	 *
	 * @access     private
	 * @var        array
	 */
	private $weekdays = array(
		1 => 'monday',
		2 => 'tuesday',
		3 => 'wednesday',
		4 => 'thursday',
		5 => 'friday',
		6 => 'saturday',
		7 => 'sunday'
	);

	/**
	 * Shortcut to Low_date lib
	 *
	 * @access     private
	 * @var        Object
	 */
	private $date;

	/**
	 * Shortcut to Low_events_event_model lib
	 *
	 * @access     private
	 * @var        Object
	 */
	private $model;

	/**
	 * Site id shortcut
	 *
	 * @access     private
	 * @var        int
	 */
	private $site_id;

	/**
	 * channel fields shortcut/cache
	 *
	 * @access     private
	 * @var        array
	 */
	private $fields;

	/**
	 * Shortcut to today's date
	 *
	 * @access     private
	 * @var        string
	 */
	private $today;

	/**
	 * Shortcut to now
	 *
	 * @access     private
	 * @var        string
	 */
	private $now;

	// --------------------------------------------------------------------
	// PUBLIC METHODS
	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access     public
	 * @return     void
	 */
	public function __construct()
	{
		// --------------------------------------
		// Get global object
		// --------------------------------------

		$this->EE =& get_instance();

		// --------------------------------------
		// Load stuff
		// --------------------------------------

		$this->EE->load->add_package_path(PATH_THIRD.LOW_EVENTS_PACKAGE);
		$this->EE->load->helper(LOW_EVENTS_PACKAGE);
		$this->EE->load->library(LOW_EVENTS_PACKAGE.'_model');
		$this->EE->load->library('Low_date');

		Low_events_model::load_models();

		// --------------------------------------
		// Shortcuts
		// --------------------------------------

		$this->date    =& $this->EE->low_date;
		$this->model   =& $this->EE->low_events_event_model;
		$this->site_id =  $this->EE->config->item('site_id');
		$this->today   =  date('Y-m-d');

		// Make sure fields are present
		$this->_get_channel_fields();
	}

	// --------------------------------------------------------------------

	/**
	 * Show events
	 *
	 * @access      public
	 * @return      string
	 */
	public function entries()
	{
		// --------------------------------------
		// Initiate the date to work with
		// --------------------------------------

		$this->_init_date();

		// --------------------------------------
		// Prep no_results to avoid conflicts
		// --------------------------------------

		$this->_prep_no_results();

		// --------------------------------------
		// Initiate rows
		// --------------------------------------

		$rows = array();

		// --------------------------------------
		// Filter by event field, not prefixed in DB
		// --------------------------------------

		$this->_event_field_filter();

		// --------------------------------------
		// Determine unit to display
		// --------------------------------------

		// Unit parameter defaults to what was given in date param
		$unit = $this->EE->TMPL->fetch_param('unit', $this->date->given());

		// Log it for debugging
		$this->_log("Unit: {$unit}");

		// Then get the rows depending on unit
		switch ($unit)
		{
			case 'year':
				list($start, $end) = $this->date->get_year_range();
				$rows = $this->model->get_range($start, $end);
			break;

			case 'month':
				list($start, $end) = $this->date->get_month_range();
				$rows = $this->model->get_range($start, $end);
			break;

			case 'week':
				list($start, $end) = $this->date->get_week_range();
				$rows = $this->model->get_range($start, $end);
			break;

			case 'day':
				// Use range to get events overlapping the day
				$rows = $this->model->get_range($this->date->date(), $this->date->date());
			break;

			case 'passed':
				$rows = $this->model->get_passed($this->date->date(), $this->date->time());
			break;

			default:
				$unit = 'upcoming';
				$time = ($this->EE->TMPL->fetch_param('show_passed') == 'no')
				      ? $this->date->time()
				      : FALSE;
				$rows = $this->model->get_upcoming($this->date->date(), $time);
			break;
		}

		// Remove active?
		if ($unit == 'upcoming' && $this->EE->TMPL->fetch_param('show_active') == 'no')
		{
			foreach ($rows AS $i => $row)
			{
				// Start date and start time
				$sd = $row['start_date'];
				$st = ($row['all_day'] == 'y') ? '00:00' : $row['start_time'];

				// End date and end time
				$ed = $row['end_date'];
				$et = ($row['all_day'] == 'y') ? '23:59' : $row['end_time'];

				// Start stamp and end stamp
				$ss = strtotime("{$sd} {$st}");
				$es = strtotime("{$ed} {$et}");

				// Unset active rows
				if ($ss < $this->date->now() && $es > $this->date->now())
				{
					unset($rows[$i]);
				}
			}
		}

		// Get ids only
		$entry_ids = $rows ? low_flatten_results($rows, 'entry_id') : array();

		// Clean up
		unset($rows);

		// --------------------------------------
		// Check for existing entry_id parameter
		// --------------------------------------

		if (isset($this->EE->TMPL->tagparams['entry_id']) && strlen($this->EE->TMPL->tagparams['entry_id']))
		{
			$this->_log('entry_id parameter found, filtering event ids accordingly');

			// Get the parameter value
			list($ids, $in) = low_explode_param($this->EE->TMPL->tagparams['entry_id']);

			// Either remove $ids from $entry_ids OR limit $entry_ids to $ids
			$method = $in ? 'array_intersect' : 'array_diff';

			// Get list of entry ids that should be listed
			$entry_ids = $method($entry_ids, $ids);
		}

		// --------------------------------------
		// If there are no entry_ids, return nothin
		// --------------------------------------

		if (empty($entry_ids))
		{
			$this->_log('No event ids found, returning no results');
			return $this->EE->TMPL->no_results();
		}

		// --------------------------------------
		// set fixed_order / entry_id according to presence of orderby param
		// --------------------------------------

		$param = ($this->EE->TMPL->fetch_param('orderby')) ? 'entry_id' : 'fixed_order';
		$param_val = implode('|', $entry_ids);

		$this->_log(sprintf('Setting %s="%s"', $param, $param_val));
		$this->EE->TMPL->tagparams[$param] = $param_val;

		// --------------------------------------
		// Make sure the following params are set
		// --------------------------------------

		$set_params = array(
			'dynamic'  => 'no',
			'paginate' => 'bottom'
		);

		foreach ($set_params AS $key => $val)
		{
			if ( ! $this->EE->TMPL->fetch_param($key))
			{
				$this->EE->TMPL->tagparams[$key] = $val;
			}
		}

		// --------------------------------------
		// Let the Channel module do all the heavy lifting
		// --------------------------------------

		return $this->_channel_entries();
	}

	// --------------------------------------------------------------------

	/**
	 * Get current/given date
	 *
	 * @access     public
	 * @return     string
	 */
	public function this_date()
	{
		return $this->_format();
	}

	/**
	 * Get next date
	 */
	public function next_date()
	{
		return $this->_format('add');
	}

	/**
	 * Get previous date
	 */
	public function prev_date()
	{
		return $this->_format('sub');
	}

	/**
	 * Return date in format
	 */
	private function _format($mod = FALSE)
	{
		// --------------------------------------
		// Initiate the date to work with
		// --------------------------------------

		$this->_init_date();

		// --------------------------------------
		// What are we going to display?
		// --------------------------------------

		$unit = $this->EE->TMPL->fetch_param('unit', $this->date->given());

		// --------------------------------------
		// Do we need to modify the date given
		// --------------------------------------

		if ($mod && $unit)
		{
			$this->date->$mod($unit);

			// Log it
			$this->_log("Modify date: {$mod} {$unit} to ".$this->date->date());
		}

		// --------------------------------------
		// Check in what format
		// --------------------------------------

		// Get format="" param
		$format = $this->EE->TMPL->fetch_param('format');

		// Check for year_format="", month_format="" or day_format=""
		$format = $this->EE->TMPL->fetch_param($unit.'_format', $format);

		if ( ! $format)
		{
			switch ($unit)
			{
				case 'week':
					$this->return_data = $this->date->week_url();
				break;

				case 'month':
					$this->return_data = $this->date->month_url();
				break;

				case 'year':
					$this->return_data = $this->date->year();
				break;

				default:
					$this->return_data = $this->date->date();
			}
		}
		else
		{
			$this->return_data = $this->date->ee_format($format);
		}

		return $this->return_data;
	}

	// --------------------------------------------------------------------

	/**
	 * Generate Calendar based on events
	 *
	 * @access      public
	 * @return      string
	 */
	public function calendar()
	{
		// --------------------------------------
		// Initiate the date to work with
		// --------------------------------------

		$this->_init_date();

		// Keep track of given date
		$given_date  = $this->date->date();
		$given_month = $this->date->month_url();
		$given_week  = ($this->date->given() == 'week') ? $this->date->week_url() : FALSE;

		// --------------------------------------
		// If a week is given, make sure the thursday
		// is in the same month, or else advance one month
		// --------------------------------------

		if ($given_week)
		{
			// Get the thursday
			$this->date->modify('+ 3 days');

			if ($given_month == $this->date->month_url())
			{
				// No probs, same month given, reset back to original given date
				$this->date->reset();
			}
			else
			{
				// Thursday is in next month, so set the date to that
				$given_date = $this->date->date();
				$given_month = $this->date->month_url();
			}
		}

		// Set date to first of the month
		$this->date->first_of_month();

		// Get next and previous month
		$next = new Low_date($this->date->date());
		$prev = new Low_date($this->date->date());
		$next->add('month');
		$prev->sub('month');

		// Days in month
		$dim = $this->date->days_in_month();

		// Day of the Week
		$dotw = $this->date->first_day_of_month();

		// Week of the month
		$wotm = 0;

		// --------------------------------------
		// Day to start the week
		// --------------------------------------

		$start_day = strtolower($this->EE->TMPL->fetch_param('start_day'));

		// Force monday on non-existent weekday or if a week is given
		if ( ! in_array($start_day, $this->weekdays) || $given_week !== FALSE)
		{
			$start_day = 'monday';
		}

		$start_day = array_search($start_day, $this->weekdays);

		// --------------------------------------
		// Calculate number of leading days (prev month)
		// --------------------------------------

		if ($leading_days = (($dotw - $start_day) + 7) % 7)
		{
			$this->date->modify("- {$leading_days} days");
		}

		// Keep track of start date
		$start_date = $this->date->date();

		// Initiate weeks and weekdays arrays
		$weeks = $weekdays = $days = array();

		// Initiate day count
		$day_count = 0;

		// Add leading 0s to day number?
		$leading = ($this->EE->TMPL->fetch_param('leading_zeroes', 'no') == 'yes');

		// --------------------------------------
		// Populate weeks array
		// --------------------------------------

		while (TRUE)
		{
			// Initiate week
			if ( ! isset($weeks[$wotm]))
			{
				$weeks[$wotm] = array(
					'days'     => array(),
					'week_url' => $this->date->week_url(),
					'is_given_week' => ($given_week == $this->date->week_url()) ? 'y' : ''
				);
			}

			// Add the day row to the week
			$weeks[$wotm]['days'][] = array(
				'day_number'    => $leading ? $this->date->day() : intval($this->date->day()),
				'day_url'       => $this->date->day_url(),
				'day'           => $this->date->day_url(),
				'is_prev'       => ($this->date->month_url() == $prev->month_url()) ? 'y' : '',
				'is_next'       => ($this->date->month_url() == $next->month_url()) ? 'y' : '',
				'is_current'    => ($this->date->month_url() == $given_month) ? 'y' : '',
				'is_given'      => ($this->date->given() == 'day' && $this->date->date() == $given_date) ? 'y' : '',
				'is_today'      => ($this->date->date() == $this->today) ? 'y' : '',
				'events_on_day' => 0
			);

			// Populate weekdays
			if ( ! $wotm)
			{
				$weekdays[] = array(
					'weekday' => $this->date->ee_format('%l'),
					'weekday_short' => $this->date->ee_format('%D'),
					'weekday_1' => substr($this->date->ee_format('%D'), 0, 1)
				);
			}

			// Advance by one day
			$this->date->add('day');

			// if days is divisible by 7, a week is done
			if ($done = ! (++$day_count % 7))
			{
				// If we're caught up with the next month too, exit the loop
				if ($this->date->month_url() == $next->month_url()) break;

				// Or else just increase the week of the month
				$wotm++;
			}
		}

		// End date
		$end_date = $this->date->date();
		$this->date->reset();

		$this->_log("Initiated calendar from {$start_date} to {$end_date}");

		// --------------------------------------
		// Get events for this calendar range
		// --------------------------------------

		// Initiate events
		$events = $entries = array();

		$this->model->where_range($start_date, $end_date, TRUE, 'e');

		// Query the rest of the entry details if there are events present
		if ($entries = $this->_get_event_entries())
		{
			foreach ($entries AS $row)
			{
				// Skip the ones not found in $entries
				if ($row['start_date'] == $row['end_date'])
				{
					$events[$row['start_date']][] = $row;
				}
				else
				{
					// Assign each day between start and end to events array
					$date = new Low_date($row['start_date']);

					while (($start = $date->date()) <= $row['end_date'])
					{
						$events[$start][] = $row;
						$date->add('day');
					}
				}
			}
		}
		else
		{
			// No events in this range
		}

		// Keep track of total events found
		$total_entries = count($entries);
		$total_days    = count($events);

		$this->_log("In this range: {$total_entries} entries, spanning {$total_days} days");

		// --------------------------------------
		// Assign entry count to days
		// --------------------------------------

		if ($events)
		{
			foreach ($weeks AS &$week)
			{
				foreach ($week['days'] AS &$day)
				{
					if (array_key_exists($day['day'], $events))
					{
						$day['events_on_day'] = count($events[$day['day']]);
					}
				}
			}
		}

		// --------------------------------------
		// Parse prev/this/next month links ourselves
		// --------------------------------------

		$this->return_data = $this->EE->TMPL->tagdata;

		foreach ($this->EE->TMPL->var_single AS $key => $format)
		{
			if (preg_match('/^(prev|this|next)_month(\s|$)/', $key, $match))
			{
				$format = (strpos($format, '%') !== FALSE) ? $format : '%Y-%m';

				if (($match[1]) == 'this')
				{
					$month = $this->date->ee_format($format);
				}
				else
				{
					$month = $$match[1]->ee_format($format);
				}

				$this->return_data = str_replace(LD.$key.RD, $month, $this->return_data);
			}
		}

		// --------------------------------------
		// Create data array for parsing vars
		// --------------------------------------

		$data = array(
			'next_month_url' => $next->month_url(),
			'prev_month_url' => $prev->month_url(),
			'this_month_url' => $this->date->month_url(),
			'weekdays' => $weekdays,
			'weeks' => $weeks
		);

		$this->_log('Parsing calendar tagdata');

		return $this->EE->TMPL->parse_variables_row($this->return_data, $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Generate month list based on events
	 *
	 * @access      public
	 * @return      string
	 */
	public function archive()
	{
		// --------------------------------------
		// Prep no_results to avoid conflicts
		// --------------------------------------

		$this->_prep_no_results();

		// --------------------------------------
		// Get the events
		// --------------------------------------

		if ( ! ($events = $this->_get_event_entries()))
		{
			$this->_log('No events found, returning no results');
			return $this->EE->TMPL->no_results();
		}

		// --------------------------------------
		// Loop through events and add them to the months array
		// --------------------------------------

		$months = array();

		foreach ($events AS $event)
		{
			// Create Low Date objects from each date
			$start = new Low_date($event['start_date']);
			$end   = new Low_date($event['end_date']);

			// Set both dates to the first of the month
			// and return the month url: YYYY-MM
			$start_month = $start->first_of_month()->month_url();
			$end_month   = $end->first_of_month()->month_url();

			// If event starts and ends in the same month,
			// simply add it to the months array
			if ($start_month == $end_month)
			{
				$months[$start_month][] = $event['entry_id'];
			}
			// Or else add each spanning month to the months array
			else
			{
				// To do this, increase the start date by a month
				// until it exceeds the end month
				while ($start->month_url() <= $end_month)
				{
					$months[$start->month_url()][] = $event['entry_id'];
					$start->add('month');
				}
			}
		}

		// --------------------------------------
		// Sort the array by month
		// --------------------------------------

		if ($this->EE->TMPL->fetch_param('sort', 'asc') == 'asc')
		{
			ksort($months);
		}
		else
		{
			krsort($months);
		}

		// --------------------------------------
		// Create data array based on month
		// --------------------------------------

		$data = array();

		foreach ($months AS $month => $rows)
		{
			// Initiate new row for data
			$row = array(
				'month_url' => $month,
				'events_in_month' => count($rows)
			);

			// Create new date for this month
			$date = new Low_Date($month);

			// Add each possible format to this row
			foreach ($this->formats AS $fmt => $key)
			{
				$row[$key] = $date->ee_format($fmt);
			}

			// Then add the row to the data array
			$data[] = $row;
		}

		return $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $data);

	}

	// --------------------------------------------------------------------
	// PRIVATE METHODS
	// --------------------------------------------------------------------

	/**
	 * Check for {if low_events_no_results}
	 *
	 * @access      private
	 * @return      void
	 */
	private function _prep_no_results()
	{
		// Shortcut to tagdata
		$td =& $this->EE->TMPL->tagdata;
		$open = 'if '.LOW_EVENTS_PACKAGE.'_no_results';
		$close = '/if';

		// Check if there is a custom no_results conditional
		if (strpos($td, $open) !== FALSE && preg_match('#'.LD.$open.RD.'(.*?)'.LD.$close.RD.'#s', $td, $match))
		{
			$this->_log("Prepping {$open} conditional");

			// Check if there are conditionals inside of that
			if (stristr($match[1], LD.'if'))
			{
				$match[0] = $this->EE->functions->full_tag($match[0], $td, LD.'if', LD.'\/if'.RD);
			}

			// Set template's no_results data to found chunk
			$this->EE->TMPL->no_results = substr($match[0], strlen(LD.$open.RD), -strlen(LD.$close.RD));

			// Remove no_results conditional from tagdata
			$td = str_replace($match[0], '', $td);
		}
	}

	/**
	 * Get channel fields from API
	 *
	 *
	 * @access      private
	 * @return      void
	 */
	private function _get_channel_fields()
	{
		if ( ! ($this->fields = low_get_cache('channel', 'custom_channel_fields')))
		{
			$this->_log('Fetching channel fields from API');

			$this->EE->load->library('api');
			$this->EE->api->instantiate('channel_fields');

			$fields = $this->EE->api_channel_fields->fetch_custom_channel_fields();

			foreach ($fields AS $key => $val)
			{
				low_set_cache('channel', $key, $val);
			}

			$this->fields = $fields['custom_channel_fields'];
		}
	}

	/**
	 * Call the native channel:entries method
	 *
	 * @access     private
	 * @return     string
	 */
	private function _channel_entries()
	{
		// --------------------------------------
		// Unset custom parameters
		// --------------------------------------

		foreach ($this->params AS $param)
		{
			unset($this->EE->TMPL->tagparams[$param]);
		}

		$this->_log('Calling the channel module');

		// --------------------------------------
		// Take care of related entries
		// --------------------------------------

		// We must do this, 'cause the template engine only does it for
		// channel:entries or events:events_results. The bastard.
		$this->EE->TMPL->tagdata = $this->EE->TMPL->assign_relationship_data($this->EE->TMPL->tagdata);

		// Add related markers to single vars to trigger replacement
		foreach ($this->EE->TMPL->related_markers AS $var)
		{
			$this->EE->TMPL->var_single[$var] = $var;
		}

		// --------------------------------------
		// Include channel module
		// --------------------------------------

		if ( ! class_exists('channel'))
		{
			require_once PATH_MOD.'channel/mod.channel'.EXT;
		}

		// --------------------------------------
		// Create new Channel instance
		// --------------------------------------

		$channel = new Channel();

		// --------------------------------------
		// Let the Channel module do all the heavy lifting
		// --------------------------------------

		return $channel->entries();
	}

	/**
	 * Events based on parameters present
	 *
	 * @access     private
	 * @return     array
	 */
	private function _get_event_entries()
	{
		// --------------------------------------
		// Default status to open
		// --------------------------------------

		if ( ! $this->EE->TMPL->fetch_param('status'))
		{
			$this->EE->TMPL->tagparams['status'] = 'open';
		}

		// --------------------------------------
		// Start composing query
		// --------------------------------------

		$this->EE->db->select($this->model->entry_attributes('e.'))
		             ->from($this->model->table(). ' e')
		             ->join('channel_titles t', 'e.entry_id = t.entry_id')
		             ->where_in('t.site_id', $this->EE->TMPL->site_ids);

		// --------------------------------------
		// Apply simple filters
		// --------------------------------------

		$filters = array(
			'entry_id'   => 't.entry_id',
			'url_title'  => 't.url_title',
			'channel_id' => 't.channel_id',
			'author_id'  => 't.author_id',
			'status'     => 't.status'
		);

		foreach ($filters AS $param => $attr)
		{
			$this->_simple_filter($param, $attr);
		}

		// --------------------------------------
		// Filter by events field, prefixed
		// --------------------------------------

		$this->_event_field_filter('e');

		// --------------------------------------
		// Are we getting all events or just upcoming
		// --------------------------------------

		if ($this->EE->TMPL->fetch_param('show_passed') == 'no')
		{
			$this->EE->db->where('e.end_date >=', $this->today);
		}

		// --------------------------------------
		// Filter by channel name
		// --------------------------------------

		if ($channel = $this->EE->TMPL->fetch_param('channel'))
		{
			// Determine which channels to filter by
			list($channel, $in) = low_explode_param($channel);

			// Adjust query accordingly
			$this->EE->db->join('channels c', 'c.channel_id = t.channel_id');
			$this->EE->db->{($in ? 'where_in' : 'where_not_in')}('c.channel_name', $channel);
		}

		// --------------------------------------
		// Filter by category
		// --------------------------------------

		if ($categories_param = $this->EE->TMPL->fetch_param('category'))
		{
			// Determine which categories to filter by
			list($categories, $in) = low_explode_param($categories_param);

			// Allow for inclusive list: category="1&2&3"
			if (strpos($categories_param, '&'))
			{
				// Execute query the old-fashioned way, so we don't interfere with active record
				// Get the entry ids that have all given categories assigned
				$query = $this->EE->db->query(
					"SELECT entry_id, COUNT(*) AS num
					FROM exp_category_posts
					WHERE cat_id IN (".implode(',', $categories).")
					GROUP BY entry_id HAVING num = ". count($categories));

				// If no entries are found, make sure we limit the query accordingly
				if ( ! ($entry_ids = low_flatten_results($query->result_array(), 'entry_id')))
				{
					$entry_ids = array(0);
				}

				$this->EE->db->{($in ? 'where_in' : 'where_not_in')}('t.entry_id', $entry_ids);
			}
			else
			{
				// Join category table
				$this->EE->db->join('category_posts cp', 'cp.entry_id = t.entry_id');
				$this->EE->db->{($in ? 'where_in' : 'where_not_in')}('cp.cat_id', $categories);
			}
		}

		// --------------------------------------
		// Hide expired entries
		// --------------------------------------

		if ($this->EE->TMPL->fetch_param('show_expired', 'no') != 'yes')
		{
			$this->EE->db->where('(t.expiration_date = 0 OR t.expiration_date >= '.$this->date->now().')');
		}

		// --------------------------------------
		// Hide future entries
		// --------------------------------------

		if ($this->EE->TMPL->fetch_param('show_future_entries', 'no') != 'yes')
		{
			$this->EE->db->where('t.entry_date <=', $this->date->now());
		}

		// --------------------------------------
		// Handle search fields
		// --------------------------------------

		if ($search_fields = $this->_search_where($this->EE->TMPL->search_fields, 'd.'))
		{
			// Join exp_channel_data table
			$this->EE->db->join('channel_data d', 't.entry_id = d.entry_id');
			$this->EE->db->where(implode(' AND ', $search_fields), NULL, FALSE);
		}

		// --------------------------------------
		// Return the results
		// --------------------------------------

		return $this->EE->db->get()->result_array();
	}

	/**
	 * Add simple filter to current query
	 *
	 * @access     private
	 * @param      string    template parameter to look for
	 * @param      string    attribute to apply filter to
	 * @return     void
	 */
	private function _simple_filter($param, $attr)
	{
		if ($param = $this->EE->TMPL->fetch_param($param))
		{
			// Determine which channels to filter by
			list($param, $in) = low_explode_param($param);

			// Adjust query accordingly
			$this->EE->db->{($in ? 'where_in' : 'where_not_in')}($attr, $param);
		}
	}

	/**
	 * Add event field filter to current query
	 *
	 * @access     private
	 * @param      string    optional prefix
	 * @return     void
	 */
	private function _event_field_filter($prefix = '')
	{
		// Apply prefix to field if given
		$field_name = ($prefix ? $prefix.'.' : '') . 'field_id';

		// Check parameter
		if ($event_field = $this->EE->TMPL->fetch_param('events_field'))
		{
			// Get fields from parameter
			list($fields, $in) = low_explode_param($event_field);

			// Get id for each field
			foreach ($fields AS &$field)
			{
				$field = $this->_get_field_id($field);
			}

			$this->EE->db->{($in ? 'where_in' : 'where_not_in')}($field_name, $fields);
		}
	}

	/**
	 * Get field id for given field short name
	 *
	 * @access     private
	 * @param      string
	 * @return     int
	 */
	private function _get_field_id($str)
	{
		return (int) @$this->fields[$this->site_id][$str];
	}

	/**
	 * Create a list of where-clauses for given search parameters
	 *
	 * @access     private
	 * @param      array
	 * @param      string
	 * @return     array
	 */
	private function _search_where($search = array(), $prefix = '')
	{
		// --------------------------------------
		// Initiate where array
		// --------------------------------------

		$where = array();

		// --------------------------------------
		// Loop through search filters and create where clause accordingly
		// --------------------------------------

		foreach ($search AS $key => $val)
		{
			// Skip non-existent fields
			if ( ! ($field_id = $this->_get_field_id($key))) continue;

			// Initiate some vars
			$exact = $all = FALSE;
			$field = $prefix.'field_id_'.$field_id;

			// Exact matches
			if (substr($val, 0, 1) == '=')
			{
				$val   = substr($terms, 1);
				$exact = TRUE;
			}

			// All items? -> && instead of |
			if (strpos($val, '&&') !== FALSE)
			{
				$all = TRUE;
				$val = str_replace('&&', '|', $val);
			}

			// Convert parameter to bool and array
			list($items, $in) = low_explode_param($val);

			// Init sql for where clause
			$sql = array();

			// Loop through each sub-item of the filter an create sub-clause
			foreach ($items AS $item)
			{
				// Convert IS_EMPTY constant to empty string
				$empty = ($item == 'IS_EMPTY');
				$item  = str_replace('IS_EMPTY', '', $item);

				// whole word? Regexp search
				if (substr($item, -2) == '\W')
				{
					$operand = $in ? 'REGEXP' : 'NOT REGEXP';
					$item    = '[[:<:]]'.preg_quote(substr($item, 0, -2)).'[[:>:]]';
				}
				else
				{
					// Not a whole word
					if ($exact || $empty)
					{
						// Use exact operand if empty or = was the first char in param
						$operand = $in ? '=' : '!=';
						$item = "'".$this->EE->db->escape_str($item)."'";
					}
					else
					{
						// Use like operand in all other cases
						$operand = $in ? 'LIKE' : 'NOT LIKE';
						$item = "'%".$this->EE->db->escape_str($item)."%'";
					}
				}

				// Add sub-clause to this statement
				$sql[] = sprintf("(%s %s %s)", $field, $operand, $item);
			}

			// Inclusive or exclusive
			$andor = $all ? ' AND ' : ' OR ';

			// Add complete clause to where array
			$where[] = (count($sql) == 1) ? $sql[0] : '('.implode($andor, $sql).')';
		}

		// --------------------------------------
		// Where now contains a list of clauses
		// --------------------------------------

		return $where;
	}

	// --------------------------------------------------------------------

	/**
	 * Initiate date by param or given fallback
	 *
	 * @access     private
	 * @param      string
	 * @return     void
	 */
	private function _init_date()
	{
		$this->date->init($this->EE->TMPL->fetch_param('date'));
		$this->_log(sprintf("Working date set to %s %s", $this->date->date(), $this->date->time()));
	}

	/**
	 * Log message to Template Logger
	 *
	 * @access     private
	 * @param      string
	 * @return     void
	 */
	private function _log($msg)
	{
		$this->EE->TMPL->log_item("Low Events: {$msg}");
	}

} // End Class

/* End of file mod.low_events.php */