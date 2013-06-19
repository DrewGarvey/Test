<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include config file
include(PATH_THIRD.'low_events/config.php');

/**
 * Low Events Update class
 *
 * @package        low_events
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-events
 * @copyright      Copyright (c) 2012, Low
 */
class Low_events_upd {

	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------

	/**
	 * This version
	 *
	 * @access      public
	 * @var         string
	 */
	public $version = LOW_EVENTS_VERSION;

	/**
	 * EE Superobject
	 *
	 * @access      private
	 * @var         object
	 */
	private $EE;

	/**
	 * Class name
	 *
	 * @access      private
	 * @var         array
	 */
	private $class_name;

	/**
	 * Actions used
	 *
	 * @access      private
	 * @var         array
	 */
	private $actions = array();

	// --------------------------------------------------------------------
	// METHODS
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
		$this->EE->load->library(LOW_EVENTS_PACKAGE.'_model');

		Low_events_model::load_models();

		// --------------------------------------
		// Set class name
		// --------------------------------------

		$this->class_name = ucfirst(LOW_EVENTS_PACKAGE);
	}

	/**
	 * Install the module
	 *
	 * @access      public
	 * @return      bool
	 */
	public function install()
	{
		// --------------------------------------
		// Install tables
		// --------------------------------------

		$this->EE->low_events_event_model->install();

		// --------------------------------------
		// Add row to modules table
		// --------------------------------------

		$this->EE->db->insert('modules', array(
			'module_name'    => $this->class_name,
			'module_version' => $this->version,
			'has_cp_backend' => 'n'
		));

		// --------------------------------------
		// Add rows to action table
		// --------------------------------------

		foreach ($this->actions AS $row)
		{
			list($class, $method) = $row;

			$this->EE->db->insert('actions', array(
				'class'  => $class,
				'method' => $method
			));
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Uninstall the module
	 *
	 * @return	bool
	 */
	public function uninstall()
	{
		// --------------------------------------
		// get module id
		// --------------------------------------

		$query = $this->EE->db->select('module_id')
		       ->from('modules')
		       ->where('module_name', $this->class_name)
		       ->get();

		// --------------------------------------
		// remove references from module_member_groups
		// --------------------------------------

		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');

		// --------------------------------------
		// remove references from modules
		// --------------------------------------

		$this->EE->db->where('module_name', $this->class_name);
		$this->EE->db->delete('modules');

		// --------------------------------------
		// remove references from actions
		// --------------------------------------

		$this->EE->db->where_in('class', array($this->class_name, $this->class_name.'_mcp'));
		$this->EE->db->delete('actions');

		// --------------------------------------
		// Uninstall tables
		// --------------------------------------

		$this->EE->low_events_event_model->uninstall();

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Update the module
	 *
	 * @return	bool
	 */
	public function update($current = '')
	{
		// --------------------------------------
		// Same version? A-okay, daddy-o!
		// --------------------------------------

		if ($current == '' OR version_compare($current, $this->version) === 0)
		{
			return FALSE;
		}

		/*
			// Update to next version
			if (version_compare($current, 'next-version', '<'))
			{
				// ...
			}
		*/

		// Return TRUE to update version number in DB
		return TRUE;
	}

	// --------------------------------------------------------------------

} // End class

/* End of file upd.low_events.php */