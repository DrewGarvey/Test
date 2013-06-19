<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include base class
if ( ! class_exists('Low_variables_base'))
{
	require_once(PATH_THIRD.'low_variables/base.low_variables.php');
}

/**
 * Low Variables Module Class - CP
 *
 * @package        low_variables
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-variables
 * @copyright      Copyright (c) 2009-2012, Low
 */
class Low_variables_mcp extends Low_variables_base
{
	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------

	/**
	 * Holder for error messages
	 *
	 * @var        string
	 * @access     private
	 */
	private $error_msg = '';

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	 * Legacy Constructor
	 *
	 * @see        __construct()
	 */
	public function Low_variables_mcp()
	{
		$this->__construct();
	}

	/**
	 * Constructor
	 *
	 * @access     public
	 * @return     void
	 */
	public function __construct()
	{
		// -------------------------------------
		//  Call parent constructor
		// -------------------------------------

		parent::__construct();

		// -------------------------------------
		//  Get settings from extension, cache or DB
		// -------------------------------------

		if ($this->get_settings())
		{
			$this->data['settings'] = $this->settings;
		}
		else
		{
			$this->error_msg = lang('settings_not_found');
			return;
		}

		// -------------------------------------
		//  Define base url for module
		// -------------------------------------

		$this->set_base_url();

		// -------------------------------------
		//  License check.
		//  Removing this makes baby Jesus cry
		// -------------------------------------

		if ( ! $this->_license()) return;

		// -------------------------------------
		//  Include variable types
		// -------------------------------------

		$this->_include_types();

		// -------------------------------------
		//  Sync tables
		// -------------------------------------

		$this->_sync();

		// -------------------------------------
		//  Define URLS
		// -------------------------------------

		$this->data['show_group_url'] = $this->base_url.AMP.'group_id=%s';
		$this->data['edit_var_url']   = $this->base_url.AMP.'method=manage&amp;id=%s&amp;from=%s';
		$this->data['edit_group_url'] = $this->base_url.AMP.'method=edit_group&amp;id=%s&amp;from=%s';
		$this->data['del_group_url']  = $this->base_url.AMP.'method=del_group_conf&amp;id=%s';
	}

	// --------------------------------------------------------------------
	//  EDIT VARIABLE DATA
	// --------------------------------------------------------------------

	/**
	 * Home screen
	 *
	 * @access     public
	 * @return     string
	 */
	public function index()
	{
		// -------------------------------------
		//  Add title to this page
		// -------------------------------------

		$this->EE->cp->set_variable('cp_page_title', lang('low_variables_module_name'));

		// -------------------------------------
		//  Display error message if any
		// -------------------------------------

		if ($this->error_msg != '')
		{
			return $this->error_msg;
		}

		// -------------------------------------
		//  Check for skipped items
		// -------------------------------------

		$skipped = $this->EE->session->flashdata('skipped');

		// -------------------------------------
		//  Get variable groups
		// -------------------------------------

		$query = $this->EE->db->select('group_id, group_label, group_notes')
		       ->from('low_variable_groups')
		       ->where('site_id', $this->site_id)
		       ->order_by('group_order', 'asc')
		       ->get();

		$groups = low_associate_results($query->result_array(), 'group_id');

		// -------------------------------------
		//  Get variable counts for groups, including ungrouped
		// -------------------------------------

		$this->EE->db->select('low.group_id, COUNT(low.variable_id) AS var_count')
		             ->from('low_variables AS low')
		             ->join('global_variables AS ee', 'low.variable_id = ee.variable_id')
		             ->where('ee.site_id', $this->site_id)
		             ->group_by('low.group_id');

		// Exclude hidden vars for non-managers
		if ( ! $this->is_manager())
		{
			$this->EE->db->where('low.is_hidden', 'n');
		}

		$query = $this->EE->db->get();
		$counts = low_flatten_results($query->result_array(), 'var_count', 'group_id');

		// Add counts to groups
		foreach ($groups AS $group_id => &$group)
		{
			$group['var_count']   = isset($counts[$group_id]) ? $counts[$group_id] : 0;
			$group['group_label'] = htmlspecialchars($group['group_label']);

			// Forget empty groups if not a manager
			if ( ! $this->is_manager() && $group['var_count'] == 0)
			{
				unset($groups[$group_id]);
			}
		}

		// -------------------------------------
		//  Add 'ungrouped' group
		// -------------------------------------

		if (isset($counts['0']))
		{
			$groups['0'] = array(
				'group_id'    => '0',
				'group_label' => lang('ungrouped'),
				'group_notes' => '',
				'var_count'   => $counts['0']
			);
		}

		// -------------------------------------
		//  Get group id, fallback to first in $groups with a var count
		// -------------------------------------

		if (($group_id = $this->EE->input->get('group_id')) === FALSE || (is_numeric($group_id) && ! isset($groups[$group_id])))
		{
			foreach ($groups AS $gid => $row)
			{
				if ($row['var_count'])
				{
					$group_id = $gid;
					break;
				}
			}
		}

		// -------------------------------------
		//  Get variables
		// -------------------------------------

		// Filter out hidden vars
		if ( ! $this->is_manager())
		{
			$this->EE->db->where('is_hidden', 'n');
		}

		// Show only given group
		if (is_numeric($group_id))
		{
			$this->EE->db->where('group_id', $group_id);
		}

		// Build vars query
		$query = $this->EE->db->select('*')
		       ->from('global_variables AS ee')
		       ->join('low_variables AS low', 'ee.variable_id = low.variable_id')
		       ->where('ee.site_id', $this->site_id)
		       ->order_by('low.group_id', 'asc')
		       ->order_by('low.variable_order', 'asc')
		       ->order_by('ee.variable_name', 'asc')
		       ->get();

		$vars  = $query->result_array();
		$alert = array();

		// -------------------------------------
		//  Loop thru vars and add custom data
		// -------------------------------------

		foreach ($vars AS $var)
		{
			// Fallback to default var type if type is not known
			if ( ! array_key_exists($var['variable_type'], $this->types))
			{
				$var['variable_type'] = LOW_VAR_DEFAULT_TYPE;
			}

			// Create shortcut
			$OBJ = $this->types[$var['variable_type']];

			// Set var settings to empty array if not properly decoded
			if ( ! ($var['variable_settings'] = low_array_decode($var['variable_settings'])))
			{
				$var['variable_settings'] = $OBJ->default_settings;
			}

			// Get input from var type
			$var['variable_input'] = $OBJ->display_input($var['variable_id'], $var['variable_data'], $var['variable_settings']);

			// Load CSS and JS
			$OBJ->load_assets();

			// Fallback to variable name if no label
			$var['variable_name'] = ($var['variable_label']) ? $var['variable_label'] : $var['variable_name'];

			// Add to alert array if skipped
			if (is_array($skipped) && isset($skipped[$var['variable_id']]))
			{
				$var['error_msg'] = $skipped[$var['variable_id']];
				$alert[] = $var;
			}

			// Group by group id
			$this->data['vars'][$var['group_id']][] = $var;
		}

		// -------------------------------------
		//  Populate data for view
		// -------------------------------------

		$this->data['groups']      = $groups;
		$this->data['group_id']    = $group_id;
		$this->data['group_ids']   = is_numeric($group_id) ? array($group_id) : array_keys($groups);
		$this->data['show_groups'] = ! ( ! $this->is_manager() && count($this->data['groups']) <= 1);
		$this->data['is_manager']  = $this->is_manager();
		$this->data['all_ids']     = implode('|', low_flatten_results($vars, 'variable_id'));
		$this->data['skipped']     = $alert;

		// -------------------------------------
		//  Title and Crumbs
		// -------------------------------------

		return $this->view('mcp_index');
	}

	/**
	 * Saves variable data from home screen
	 *
	 * @access     public
	 * @return     void
	 */
	public function save()
	{
		// -------------------------------------
		//  Determine return URL
		// -------------------------------------

		$return = $this->base_url;

		if (($group_id = $this->EE->input->post('group_id')) !== FALSE)
		{
			$return .= AMP.'group_id='.$group_id;
		}

		// -------------------------------------
		//  Get posted variables
		// -------------------------------------

		if ( ! ($vars = $this->EE->input->post('var')) )
		{
			$vars = array();
		}

		// -------------------------------------
		//  Get all ids, bail out if empty
		// -------------------------------------

		if ($all_ids = $this->EE->input->post('all_ids'))
		{
			$all_ids = explode('|', $all_ids);
		}
		else
		{
			$this->EE->functions->redirect($return);
		}

		// -------------------------------------
		//  Get types and settings for all ids
		// -------------------------------------

		// init types array
		$types = array();

		// get types and settings from DB
		$query = $this->EE->db->select('ee.variable_name, low.variable_id, low.variable_type, low.variable_settings, low.save_as_file')
		       ->from('global_variables AS ee')
		       ->join('low_variables AS low', 'ee.variable_id = low.variable_id')
		       ->where_in('low.variable_id', $all_ids)
		       ->get();

		// -------------------------------------
		//  Loop thru results
		// -------------------------------------

		foreach ($query->result_array() AS $row)
		{
			// Set type to default if not found
			if (! in_array($row['variable_type'], $this->settings['enabled_types']))
			{
				$row['variable_type'] = LOW_VAR_DEFAULT_TYPE;
			}

			// populate the types + settings array
			$types[$row['variable_id']] = array(
				'name' => $row['variable_name'],
				'type' => $row['variable_type'],
				'file' => ($row['save_as_file'] == 'y'),
				'settings' => low_array_decode($row['variable_settings'])
			);
		}

		// -------------------------------------
		//  Get ids that weren't posted, set to empty
		// -------------------------------------

		foreach (array_diff($all_ids, array_keys($vars)) AS $missing_id)
		{
			$vars[$missing_id] = '';
		}

		$skipped = array();

		// -------------------------------------
		//  Loop through posted vars and save new values
		// -------------------------------------

		foreach ($vars AS $var_id => $var_data)
		{
			// Check if type is known
			if ( ! isset($types[$var_id]) )
			{
				$types[$var_id] = array(
					'type'     => LOW_VAR_DEFAULT_TYPE,
					'settings' => array()
				);
			}

			// -------------------------------------
			//  Does type require action?
			// -------------------------------------

			$var_type     = $types[$var_id]['type'];
			$var_settings = $types[$var_id]['settings'];

			if (method_exists($this->types[$var_type], 'save_input'))
			{
				// Set error message to empty string
				$this->types[$var_type]->error_msg = '';

				// if FALSE is returned, skip this var
				if (($var_data = $this->types[$var_type]->save_input($var_id, $var_data, $var_settings)) === FALSE)
				{
					$skipped[$var_id] = $this->types[$var_type]->error_msg;
					continue;
				}
			}

			// -------------------------------------
			//  Update record
			// -------------------------------------

			$this->EE->db->update('global_variables', array('variable_data' => $var_data), "variable_id = '{$var_id}'");
			$this->EE->db->update('low_variables', array('edit_date' => time()), "variable_id = '{$var_id}'");

			// -------------------------------------
			//  Call post_save_input
			// -------------------------------------

			if (method_exists($this->types[$var_type], 'post_save_input'))
			{
				$this->types[$var_type]->post_save_input($var_id, $var_data, $var_settings);
			}

			// -------------------------------------
			//  Add feedback to return  url
			// -------------------------------------

			$this->EE->session->set_flashdata('msg', 'low_variables_saved');

			if ( ! empty($skipped))
			{
				$this->EE->session->set_flashdata('skipped', $skipped);
			}
		}

		// -------------------------------------
		// 'low_variables_post_save' hook.
		//  - Do something after Low Variables are saved
		// -------------------------------------

		if ($this->EE->extensions->active_hook('low_variables_post_save') === TRUE)
		{
			$this->EE->extensions->call('low_variables_post_save', array_keys($vars), $skipped);
		}

		// -------------------------------------
		//  Go home
		// -------------------------------------

		$this->EE->functions->redirect($return);
	}

	// --------------------------------------------------------------------
	//  MANAGE VARIABLES - CRUD
	// --------------------------------------------------------------------

	/**
	 * Manage variables, either _list_vars() or _edit_var()
	 *
	 * @access     public
	 * @return     string
	 */
	public function manage()
	{
		// Redirect if this is not a var manager
		if ( ! $this->is_manager())
		{
			$this->EE->functions->redirect($this->base_url);
		}

		//  Display error message if any
		if ($this->error_msg != '')
		{
			return $this->error_msg;
		}

		// Check if there's an ID to edit
		$method = $this->EE->input->get('id') ? '_edit_var' : '_list_vars';

		// Call method
		return $this->$method();
	}

	/**
	 * Return an on/off link
	 *
	 * @access     private
	 * @param      string
	 * @param      string
	 * @return     string
	 */
	private function _onoff($type, $status)
	{
		// On/Off template
		$tmpl  = '<a href="#%s" class="onoff%s">%s</a>';
		$onoff = ($status == 'y') ? ' on' : '';
		$yesno = ($status == 'y') ? lang('yes') : lang('no');

		return sprintf($tmpl, $type, $onoff, $yesno);
	}

	public function ajax_update()
	{
		$var_id = $this->EE->input->post('var_id');
		$type   = $this->EE->input->post('type');
		$status = $this->EE->input->post('status');

		if (in_array($type, array('is_hidden', 'early_parsing', 'save_as_file')) && is_numeric($var_id))
		{
			$this->EE->db->where('variable_id', $var_id);
			$this->EE->db->update('low_variables', array($type => $status));
		}

		die($this->EE->db->affected_rows());
	}

	/**
	 * Show table of all variables
	 *
	 * @access     public
	 * @return     string
	 */
	private function _list_vars()
	{
		// -------------------------------------
		//  Title and Crumbs
		// -------------------------------------

		$this->EE->cp->set_variable('cp_page_title', lang('manage_variables'));
		$this->EE->cp->set_breadcrumb($this->base_url, lang('low_variables_module_name'));

		// -------------------------------------
		//  Compose query and execute
		// -------------------------------------

		$select = array(
			'ee.variable_id',
			'ee.variable_name',
			'low.group_id',
			'low.variable_label',
			'low.variable_type',
			'low.early_parsing',
			'low.is_hidden',
			'low.save_as_file'
		);

		$query = $this->EE->db->select($select)
		       ->from('global_variables AS ee')
		       ->join('low_variables AS low', 'ee.variable_id = low.variable_id')
		       ->join('low_variable_groups AS vg', 'low.group_id = vg.group_id', 'left')
		       ->where('ee.site_id', $this->site_id)
		       ->order_by('vg.group_order ASC, vg.group_label ASC, variable_order ASC, ee.variable_name ASC')
		       ->get();

		// -------------------------------------
		//  Initiate rows
		// -------------------------------------

		if ($rows = $query->result_array())
		{
			// Arrays to keep track of grouped and ungrouped vars
			$grouped = $ungrouped = array();

			foreach ($rows AS $row)
			{
				// Variable type value
				if (array_key_exists($row['variable_type'], $this->types))
				{
					$row['variable_type'] = $this->types[$row['variable_type']]->info['name'];
				}
				else
				{
					$row['variable_type'] = lang('unknown_type');
				}

				// Early parsing value
				if ($this->settings['register_globals'] == 'y')
				{
					$row['early_parsing'] = $this->_onoff('early_parsing', $row['early_parsing']);
				}
				else
				{
					$row['early_parsing'] = '--';
				}

				// Save as file value
				if ($this->settings['save_as_files'] == 'y')
				{
					$row['save_as_file'] = $this->_onoff('save_as_file', $row['save_as_file']);
				}
				else
				{
					$row['save_as_file'] = '--';
				}

				// Is hidden value
				$row['is_hidden'] = $this->_onoff('is_hidden', $row['is_hidden']);

				// Group id
				if ($row['group_id'])
				{
					$grouped[] = $row;
				}
				else
				{
					$ungrouped[] = $row;
				}
			}

			// Add vars to view data
			$this->data['variables'] = array_merge($grouped, $ungrouped);
			$this->data['types']     = $this->types;
			$this->data['groups']    = $this->_get_variable_groups() + array(0 => lang('ungrouped'));
		}
		else
		{
			$this->data['variables'] = array();
		}

		// -------------------------------------
		//  Return list view
		// -------------------------------------

		return $this->view('mcp_list_vars');

	}

	/**
	 * Saves variable list
	 *
	 * @access     public
	 * @return     mixed     [void|string]
	 */
	public function save_list()
	{
		// -------------------------------------
		//  Get vars from POST
		// -------------------------------------

		if ($vars = $this->EE->input->post('toggle'))
		{
			// -------------------------------------
			//  Get action to perform with list
			// -------------------------------------

			$action = $this->EE->input->post('action');
			$data   = array();

			if ($action == 'delete')
			{
				// Show delete confirmation
				return $this->_delete_conf($vars);
			}
			elseif (in_array($action, array_keys($this->types)))
			{
				$data['variable_type'] = $action;
			}
			elseif ($action == 'show')
			{
				$data['is_hidden'] = 'n';
			}
			elseif ($action == 'hide')
			{
				$data['is_hidden'] = 'y';
			}
			elseif ($action == 'enable_early_parsing')
			{
				$data['early_parsing'] = 'y';
			}
			elseif ($action == 'disable_early_parsing')
			{
				$data['early_parsing'] = 'n';
			}
			elseif ($action == 'enable_save_as_file')
			{
				$data['save_as_file'] = 'y';
			}
			elseif ($action == 'disable_save_as_file')
			{
				$data['save_as_file'] = 'n';
			}
			elseif (is_numeric($action))
			{
				$data['group_id'] = $action;
			}

			// Batch update the vars if data is given
			if ($data)
			{
				$this->EE->db->where_in('variable_id', $vars);
				$this->EE->db->update('low_variables', $data);
				$this->EE->session->set_flashdata('msg', 'low_variables_saved');
			}
		}

		$this->EE->functions->redirect($this->base_url.AMP.'method=manage');
	}

	/**
	 * Show edit form to edit single variable
	 *
	 * @access     public
	 * @return     string
	 */
	private function _edit_var()
	{
		// -------------------------------------
		//  Title and Crumbs
		// -------------------------------------

		$this->EE->cp->set_variable('cp_page_title', lang('edit_variable'));
		$this->EE->cp->set_breadcrumb($this->base_url, lang('low_variables_module_name'));

		// -------------------------------------
		//  Do we have errors in flashdata?
		// -------------------------------------

		$this->data['errors'] = $this->EE->session->flashdata('errors');
		$this->data['from']   = $this->EE->input->get_post('from');

		// -------------------------------------
		//  Get variable groups
		// -------------------------------------

		$this->data['variable_groups'] = array('0' => '--') + $this->_get_variable_groups();

		// -------------------------------------
		//  Create new, clone or edit?
		// -------------------------------------

		$var_id   = $this->EE->input->get('id');
		$clone_id = $this->EE->input->get('clone');

		if ($var_id == 'new')
		{
			// -------------------------------------
			//  Init new array if var is new
			// -------------------------------------

			$this->data = array_merge($this->data, array(
				'variable_id'	=> 'new',
				'group_id'		=> '0',
				'variable_name'	=> '',
				'variable_label'=> '',
				'variable_notes'=> '',
				'variable_type'	=> LOW_VAR_DEFAULT_TYPE,
				'variable_settings' => array(),
				'variable_order'=> '0',
				'early_parsing'	=> 'n',
				'is_hidden'		=> 'n',
				'save_as_file'	=> 'n'
			));
		}

		// -------------------------------------
		//  Get var to edit or clone
		// -------------------------------------

		if ( ($var_id != 'new') || is_numeric($clone_id) )
		{
			// -------------------------------------
			//  Default selection
			// -------------------------------------

			$select = array(
				'low.variable_type',
				'low.group_id',
				'low.variable_label',
				'low.variable_notes',
				'low.variable_settings',
				'low.early_parsing',
				'low.is_hidden',
				'low.save_as_file'
			);

			// -------------------------------------
			//  Select more when editing variable
			// -------------------------------------

			if ($var_id != 'new')
			{
				$select = array_merge($select, array(
					'low.variable_order',
					'ee.variable_id',
					'ee.variable_name'
				));

				$sql_var_id = $var_id;
			}
			else
			{
				$sql_var_id = $clone_id;
			}

			// -------------------------------------
			//  Get existing var: compose query and execute
			// -------------------------------------

			$query = $this->EE->db->select($select)
			       ->from('global_variables AS ee')
			       ->from('low_variables AS low')
			       ->where('ee.variable_id = low.variable_id')
			       ->where('ee.site_id', $this->site_id)
			       ->where('ee.variable_id', $sql_var_id)
			       ->get();

			// -------------------------------------
			//  Exit if no var was found
			// -------------------------------------

			if ($query->num_rows() == 0)
			{
				$this->EE->functions->redirect($this->base_url.AMP.'P=manage&amp;message=var_not_found');
				exit;
			}

			// -------------------------------------
			//  Check if var type is an enabled type
			// -------------------------------------

			$data = $query->row_array();

			if ( ! array_key_exists($data['variable_type'], $this->types))
			{
				$data['variable_type'] = LOW_VAR_DEFAULT_TYPE;
			}

			// -------------------------------------
			//  Check if var type is an enabled type
			// -------------------------------------

			$this->data = array_merge($this->data, $data);
			$this->data['variable_settings'] = low_array_decode($this->data['variable_settings']);
		}

		// -------------------------------------
		//  Get type settings
		// -------------------------------------

		foreach ($this->types AS $type => $obj)
		{
			// Set settings to default for each type
			$settings = $obj->default_settings;

			// Override settings if current type has existing settings
			if ($type == $this->data['variable_type'] && $this->data['variable_settings'])
			{
				$settings = $this->data['variable_settings'];
			}

			// Call 'display_settings'
			$display = method_exists($obj, 'display_settings') ? $obj->display_settings($this->data['variable_id'], $settings) : array();

			$this->data['types'][$type] = array(
				'name' => $obj->info['name'],
				'settings' => $display
			);
		}

		// -------------------------------------
		//  Load view
		// -------------------------------------

		return $this->view('mcp_edit_var');
	}

	/**
	 * Saves variable data
	 *
	 * @access     public
	 * @return     void
	 */
	public function save_var()
	{
		// -------------------------------------
		//  Where are we coming from?
		// -------------------------------------

		$from = $this->EE->input->post('from');

		// -------------------------------------
		//  Get variable_id
		// -------------------------------------

		if ( ! ($variable_id = $this->EE->input->post('variable_id')) )
		{
			// No id found, exit!
			$this->EE->functions->redirect($this->base_url);
			exit;
		}

		// -------------------------------------
		//  Get data from POST
		// -------------------------------------

		$ee_vars = $low_vars = $all_vars = $errors = array();

		// -------------------------------------
		//  Check variable name
		// -------------------------------------

		if (($var_name = $this->EE->input->post('variable_name')) && preg_match('/^[a-z0-9\-_:]+$/i', $var_name))
		{
			$ee_vars['variable_name'] = $var_name;
		}
		else
		{
			$errors[] = 'invalid_variable_name';
		}

		// -------------------------------------
		//  Check if var already exists
		// -------------------------------------

		// Check if suffix was posted
		$suffix = trim($this->EE->input->post('variable_suffix'));

		if ( ! $suffix)
		{
			// Count possible existing var
			$existing = $this->EE->db->where('site_id', $this->site_id)
			          ->where('variable_name', $var_name)
			          ->where('variable_id !=', $variable_id)
			          ->count_all_results('global_variables');

			if ($existing)
			{
				$errors[] = 'variable_name_already_exists';
			}
		}

		// -------------------------------------
		//  Check for errors
		// -------------------------------------

		if ( ! empty($errors))
		{
			$msg = array();

			foreach ($errors AS $line)
			{
				$msg[] = lang($line);
			}

			$this->EE->session->set_flashdata('errors', $msg);
			$this->EE->functions->redirect(sprintf($this->data['edit_var_url'], $variable_id, $from));
			exit;
		}

		// -------------------------------------
		//  Check variable data
		// -------------------------------------

		if ($variable_id == 'new' && ($var_data = $this->EE->input->post('variable_data')))
		{
			$ee_vars['variable_data'] = $var_data;
		}

		// -------------------------------------
		//  Check boolean values
		// -------------------------------------

		foreach (array('early_parsing', 'is_hidden', 'save_as_file') AS $var)
		{
			$low_vars[$var] = ($value = $this->EE->input->post($var)) ? 'y' : 'n';
		}

		// -------------------------------------
		//  Check other regular vars
		// -------------------------------------

		foreach (array('group_id', 'variable_label', 'variable_notes', 'variable_type', 'variable_order') AS $var)
		{
			$low_vars[$var] = ($value = $this->EE->input->post($var)) ? $value : '';
		}

		// -------------------------------------
		//  Get variable settings
		// -------------------------------------

		// All settings
		$var_settings = $this->EE->input->post('variable_settings');

		// Focus on this type's settings
		$var_settings = array_key_exists($low_vars['variable_type'], $var_settings)
		              ? $var_settings[$low_vars['variable_type']]
		              : array();

		// -------------------------------------
		//  Call save_settings from API, fallback to default handling
		// -------------------------------------

		if (is_object($this->types[$low_vars['variable_type']]))
		{
			// Shortcut to type object
			$OBJ = $this->types[$low_vars['variable_type']];

			if (method_exists($OBJ, 'save_settings'))
			{
				// Settings?
				$settings = empty($var_settings) ? $OBJ->default_settings : $var_settings;

				// Call API for custom handling of settings
				$var_settings = $OBJ->save_settings($variable_id, $settings);
			}
			else
			{
				// Default handling of settings: set missing settings to empty string
				foreach (array_keys($OBJ->default_settings) AS $setting)
				{
					if ( ! isset($var_settings[$setting]))
					{
						$var_settings[$setting] = '';
					}
				}
			}
		}

		// Overwrite posted data
		$low_vars['variable_settings'] = low_array_encode($var_settings);

		// -------------------------------------
		//  Check for suffixes
		// -------------------------------------

		// init vars
		$suffixes = $suffixed = array();

		if ($variable_id == 'new' && $suffix)
		{
			// Get existing var names to check against
			$query = $this->EE->db->select('variable_name')
			       ->from('global_variables')
			       ->where('site_id', $this->site_id)
			       ->get();

			$existing = low_flatten_results($query->result_array(), 'variable_name');

			foreach (explode(' ', $suffix) AS $sfx)
			{
				// Skip illegal ones
				if ( ! preg_match('/^[a-zA-Z0-9\-_]+$/', $sfx)) continue;

				// Remove underscore if it's there
				if (substr($sfx, 0, 1) == '_') $sfx = substr($sfx, 1);

				// Skip suffix if name already exists
				if (in_array($var_name.'_'.$sfx, $existing)) continue;

				$suffixes[] = $sfx;
			}
		}

		// -------------------------------------
		//  Update EE table
		// -------------------------------------

		if ( ! empty($ee_vars))
		{
			if ($variable_id == 'new')
			{
				// -------------------------------------
				//  Add site id to array, INSERT new var
				//  Get inserted id
				// -------------------------------------

				$ee_vars['site_id'] = $this->site_id;

				if ($suffixes)
				{
					foreach ($suffixes AS $sfx)
					{
						// Add suffix to name
						$data = $ee_vars;
						$data['variable_name'] = $ee_vars['variable_name'] . '_' . $sfx;

						// Insert row
						$this->EE->db->insert('global_variables', $data);

						$vid = $this->EE->db->insert_id();

						// Keep track of inserted rows
						$suffixed[$vid] = $sfx;

						// Keep track of all vars
						$all_vars[$vid] = $data;
					}
				}
				else
				{
					$this->EE->db->insert('global_variables', $ee_vars);
					$variable_id = $this->EE->db->insert_id();
					$all_vars[$variable_id] = $ee_vars;
				}
			}
			else
			{
				$this->EE->db->update('global_variables', $ee_vars, "variable_id = '{$variable_id}'");
				$all_vars[$variable_id] = $ee_vars;
			}
		}

		// -------------------------------------
		//  Update low_variables table
		// -------------------------------------

		if ( ! empty($low_vars))
		{
			$query = $this->EE->db->select('variable_id')->from('low_variables')->get();
			$update = low_flatten_results($query->result_array(), 'variable_id');

			// -------------------------------------
			//  Get default value for new sort order
			// -------------------------------------

			if ($low_vars['variable_order'] == 0)
			{
				$query = $this->EE->db->query("SELECT COUNT(*) AS max FROM exp_low_variables WHERE group_id = '{$low_vars['group_id']}'");

				if ($query->num_rows())
				{
					$row = $query->row();
					$low_vars['variable_order'] = (int) $row->max + 1;
				}
				else
				{
					$low_vars['variable_order'] = 1;
				}
			}

			if ($suffixed)
			{
				$i = (int) $low_vars['variable_order'];

				foreach ($suffixed AS $var_id => $sfx)
				{
					$row = $low_vars;
					$row['variable_label']
						= (strpos($low_vars['variable_label'], '{suffix}') !== FALSE)
						? str_replace('{suffix}', $sfx, $low_vars['variable_label'])
						: $low_vars['variable_label'] . " ({$sfx})";
					$row['variable_order'] = $i++;
					$rows[$var_id] = $row;
				}
			}
			else
			{
				$rows[$variable_id] = $low_vars;
			}

			// -------------------------------------
			//  INSERT/UPDATE rows
			// -------------------------------------

			foreach ($rows AS $var_id => $data)
			{
				if (in_array($var_id, $update))
				{
					$this->EE->db->update('exp_low_variables', $data, "variable_id = '{$var_id}'");
				}
				else
				{
					$data['variable_id'] = $var_id;
					$this->EE->db->insert('exp_low_variables', $data);
				}
				$all_vars[$var_id] += $data;
			}
		}
		else
		{
			// -------------------------------------
			//  Delete reference if no low_vars were found
			// -------------------------------------

			$this->EE->db->query("DELETE FROM `exp_low_variables` WHERE `variable_id` = '{$variable_id}'");
		}

		// -------------------------------------
		//  Trigger post_save_settings
		// -------------------------------------

		foreach ($all_vars AS $vid => $vdata)
		{
			// Skip if it's not an object
			if ( ! is_object($this->types[$vdata['variable_type']])) continue;

			// Shortcut to type object
			$OBJ = $this->types[$vdata['variable_type']];

			// Skip if necessary method doesn't exist
			if ( ! method_exists($OBJ, 'post_save_settings')) continue;

			// Decode settings
			if ( ! is_array($vdata['variable_settings']))
			{
				$vdata['variable_settings'] = low_array_decode($vdata['variable_settings']);
			}

			// Call the API
			$OBJ->post_save_settings($vid, $vdata['variable_settings']);
		}

		// -------------------------------------
		//  Return url
		// -------------------------------------

		$return_url = $this->base_url;

		if ($from == 'manage')
		{
			$return_url .= AMP.'method=manage';
		}
		elseif (is_numeric($from))
		{
			$return_url = sprintf($this->data['show_group_url'], $from);
		}
		else
		{
			$return_url = sprintf($this->data['show_group_url'], $low_vars['group_id']);
		}

		// -------------------------------------
		//  Return with message
		// -------------------------------------

		$this->EE->session->set_flashdata('msg', 'low_variables_saved');
		$this->EE->functions->redirect($return_url);
	}

	/**
	 * Asks for deletion confirmation
	 *
	 * @access     private
	 * @param      array
	 * @return     string
	 */
	private function _delete_conf($vars = array())
	{
		// -------------------------------------
		//  Title and Crumbs
		// -------------------------------------

		$this->EE->cp->set_variable('cp_page_title', lang('low_variables_delete_confirmation'));
		$this->EE->cp->set_breadcrumb($this->base_url, lang('low_variables_module_name'));
		$this->EE->cp->set_breadcrumb($this->base_url.AMP.'method=manage', lang('manage_variables'));

		// -------------------------------------
		//  Get var names
		// -------------------------------------

		$query = $this->EE->db->select('variable_name')
		       ->from('global_variables')
		       ->where_in('variable_id', $vars)
		       ->order_by('variable_name', 'asc')
		       ->get();

		foreach ($query->result_array() AS $row)
		{
			$this->data['variable_names'][] = LD.$row['variable_name'].RD;
		}

		// -------------------------------------
		//  Show confirm message
		// -------------------------------------

		$this->data['variable_ids'] = implode('|', $vars);
		$this->data['confirm_message'] = lang('low_variables_delete_confirmation_'.(count($vars)==1?'one':'many'));

		return $this->view('mcp_del_vars_conf');
	}

	/**
	 * Deletes variables
	 *
	 * @access     public
	 * @return     void
	 */
	public function delete()
	{
		// -------------------------------------
		//  Get var ids
		// -------------------------------------

		$vars = explode('|', $this->EE->input->post('variable_id'));

		// -------------------------------------
		//  Get var types
		// -------------------------------------

		$query = $this->EE->db->select('variable_id, variable_type')
		       ->from('low_variables')
		       ->where_in('variable_id', $vars)
		       ->get();

		$types = low_flatten_results($query->result_array(), 'variable_type', 'variable_id');

		// -------------------------------------
		//  Call API
		// -------------------------------------

		foreach ($types AS $var_id => $var_type)
		{
			if (is_object($this->types[$var_type]) && method_exists($this->types[$var_type], 'delete'))
			{
				$this->types[$var_type]->delete($var_id);
			}
		}

		// -------------------------------------
		// 'low_variables_delete' hook.
		//  - Do something just before Low Variables are deleted
		// -------------------------------------

		if ($this->EE->extensions->active_hook('low_variables_delete') === TRUE)
		{
			$this->EE->extensions->call('low_variables_delete', $vars);
		}

		// -------------------------------------
		//  Delete from global variables and low variables
		// -------------------------------------

		$this->EE->db->where_in('variable_id', $vars);
		$this->EE->db->delete(array('global_variables', 'low_variables'));

		// -------------------------------------
		//  Go to manage screen and show message
		// -------------------------------------

		$this->EE->session->set_flashdata('msg', 'low_variables_deleted');
		$this->EE->functions->redirect($this->base_url.AMP.'method=manage');
	}

	// --------------------------------------------------------------------
	//  GROUPS
	// --------------------------------------------------------------------

	/**
	 * Show edit group form
	 *
	 * @access     public
	 * @return     string
	 */
	public function edit_group()
	{
		// -------------------------------------
		//  Check permissions and group id
		// -------------------------------------

		if ( ! $this->is_manager() || ($group_id = $this->EE->input->get('id')) === FALSE)
		{
			$this->EE->functions->redirect($this->base_url);
		}

		// -------------------------------------
		//  Display error message if any
		// -------------------------------------

		if ($this->error_msg != '')
		{
			return $this->error_msg;
		}

		// -------------------------------------
		//  Title and Crumbs
		// -------------------------------------

		$this->EE->cp->set_variable('cp_page_title', lang('edit_group'));
		$this->EE->cp->set_breadcrumb($this->base_url, lang('low_variables_module_name'));

		// -------------------------------------
		//  Do we have errors in flashdata?
		// -------------------------------------

		$this->data['errors'] = $this->EE->session->flashdata('errors');
		$this->data['from']   = $this->EE->input->get_post('from');

		// -------------------------------------
		//  Initiate group details
		// -------------------------------------

		$this->data = array_merge($this->data, array(
			'group_id'    => 'new',
			'group_label' => '',
			'group_notes' => '',
			'variables'   => array()
		));

		// -------------------------------------
		//  Get details if group_id is not 'new'
		// -------------------------------------

		if ($group_id != 'new')
		{
			// Account for ungrouped group
			if ($group_id)
			{
				$query = $this->EE->db->get_where('low_variable_groups', array('group_id' => $group_id));
				$this->data = array_merge($this->data, $query->row_array());
			}
			else
			{
				$this->data['group_id']    = $group_id;
				$this->data['group_label'] = lang('ungrouped');
			}

			//  Get variables in group
			$query = $this->EE->db->select(array('ee.variable_id','ee.variable_name', 'low.variable_label'))
			       ->from('global_variables AS ee')
			       ->from('low_variables AS low')
			       ->where('ee.variable_id = low.variable_id')
			       ->where('ee.site_id', $this->site_id)
			       ->where('low.group_id', $group_id)
			       ->order_by('low.variable_order', 'asc')
			       ->order_by('ee.variable_name', 'asc')
			       ->get();

			$this->data['variables'] = $query->result_array();
		}

		// -------------------------------------
		//  Feed to view
		// -------------------------------------

		return $this->view('mcp_edit_group');
	}

	/**
	 * Save group
	 *
	 * @access     public
	 * @return     void
	 */
	public function save_group()
	{
		// -------------------------------------
		//  Return url
		// -------------------------------------

		$return_url = $this->base_url;

		// -------------------------------------
		//  Get group_id
		// -------------------------------------

		if (($group_id = $this->EE->input->post('group_id')) === FALSE)
		{
			// No id found, exit!
			$this->EE->functions->redirect($return_url);
			exit;
		}

		// -------------------------------------
		//  Save As New group?
		// -------------------------------------

		if ($save_as_new = ($this->EE->input->post('save_as_new_group') == 'y'))
		{
			$group_id = 'new';
		}

		// -------------------------------------
		//  Quickly validate duplication of vars
		// -------------------------------------

		$duplicate = ($this->EE->input->post('duplicate_variables') == 'y');
		$suffix = $this->EE->input->post('variable_suffix');

		if ($save_as_new && $duplicate && ! $suffix)
		{
			show_error(lang('suffix_required'));
		}

		// Skip the following for Ungrouped
		if ($group_id != '0')
		{
			// -------------------------------------
			//  Get group_label
			// -------------------------------------

			if ( ! ($group_label = trim($this->EE->input->post('group_label'))) )
			{
				// No label found, exit!
				show_error(lang('no_group_label'));
			}

			// -------------------------------------
			//  Insert / update group
			// -------------------------------------

			$data = array(
				'group_label' => $group_label,
				'group_notes' => $this->EE->input->post('group_notes'),
				'site_id'     => $this->site_id
			);
		}

		// -------------------------------------
		//  Get posted vars, if any
		// -------------------------------------

		$vars = $this->EE->input->post('vars');

		// -------------------------------------
		//  Process group insert/update
		// -------------------------------------

		if ($group_id == 'new')
		{
			// Insert new group in DB
			$this->EE->db->insert('low_variable_groups', $data);
			$group_id = $this->EE->db->insert_id();

			// Add new vars in group, if necessary
			if ($save_as_new && ($this->EE->input->post('duplicate_variables') == 'y') && $vars)
			{
				$this->_duplicate_vars_to_group($vars, $group_id);
			}
		}
		else
		{
			// Ungrouped group can only sort, update details for the rest
			if ($group_id > 0)
			{
				$this->EE->db->where('group_id', $group_id)->update('low_variable_groups', $data);
			}

			// Update variable order
			if ($vars)
			{
				foreach ($vars AS $var_order => $var_id)
				{
					// Set new order for variable
					$this->EE->db->where('variable_id', $var_id);
					$this->EE->db->update('low_variables', array('variable_order' => ($var_order + 1)));
				}
			}
		}

		// -------------------------------------
		//  Go back from whence they came
		// -------------------------------------

		if ($this->EE->input->post('from') == 'manage')
		{
			$return_url .= '&amp;method=manage';
		}

		$this->EE->session->set_flashdata('msg', 'group_saved');
		$this->EE->functions->redirect($return_url);
	}

	/**
	 * Duplicates given variable ids into given group id
	 *
	 * @access     private
	 * @param      array
	 * @param      int
	 * @return     void
	 */
	private function _duplicate_vars_to_group($vars = array(), $group_id = FALSE)
	{
		// -------------------------------------
		//  Don't duplicate if no suffix is given
		// -------------------------------------

		if ( ! ($suffix = $this->EE->input->post('variable_suffix')))
		{
			return FALSE;
		}

		// Clean up suffix
		$suffix = trim(preg_replace('/[^a-zA-Z0-9\-_]/', '', $suffix), '_');

		// Still valid?
		if ( ! $suffix)
		{
			return FALSE;
		}

		// -------------------------------------
		//  Do what with suffix?
		// -------------------------------------

		$with_suffix = $this->EE->input->post('with_suffix');

		// -------------------------------------
		//  Get existing var names to check against
		// -------------------------------------

		$query = $this->EE->db->select('variable_name')
		       ->from('global_variables')
		       ->where('site_id', $this->site_id)
		       ->get();

		$existing = low_flatten_results($query->result_array(), 'variable_name');

		// -------------------------------------
		//  Fetch vars to duplicate
		// -------------------------------------

		$query = $this->EE->db->select('*')
		       ->from(array('global_variables ee', 'low_variables low'))
		       ->where('ee.variable_id = low.variable_id')
		       ->where_in('ee.variable_id', $vars)
		       ->get();

		$new_order = array_flip($vars);

		// -------------------------------------
		//  Loop through vars, duplicate into group
		// -------------------------------------

		foreach ($query->result_array() AS $row)
		{
			if ($with_suffix == 'replace' && strpos($row['variable_name'], '_') !== FALSE)
			{
				$new_name = preg_replace('/_[a-zA-Z0-9]+$/', "_{$suffix}", $row['variable_name']);
			}
			else
			{
				$new_name = $row['variable_name'] .'_'. $suffix;
			}

			// Skip existing ones
			if (in_array($new_name, $existing)) continue;

			// First, insert new var in native table
			$this->EE->db->insert('global_variables', array(
				'site_id' => $this->site_id,
				'variable_name' => $new_name,
				'variable_data' => $row['variable_data']
			));

			// Remember old id, set new one
			$old_id = $row['variable_id'];
			$row['variable_id'] = $this->EE->db->insert_id();

			// Get rid of unneeded data for low_variables table
			unset($row['site_id'], $row['variable_name'], $row['variable_data']);

			// Set new values for row and insert
			$row['variable_order'] = $new_order[$old_id];
			$row['group_id'] = $group_id;
			$row['edit_date'] = time();
			$this->EE->db->insert('low_variables', $row);
		}

	}

	/**
	 * Asks for group deletion confirmation
	 *
	 * @access     public
	 * @return     string
	 */
	public function del_group_conf()
	{
		// -------------------------------------
		//  Title and Crumbs
		// -------------------------------------

		$this->EE->cp->set_variable('cp_page_title', lang('low_variables_group_delete_confirmation'));
		$this->EE->cp->set_breadcrumb($this->base_url, lang('low_variables_module_name'));

		// -------------------------------------
		//  Get group name
		// -------------------------------------

		if ( ! ($group_id = $this->EE->input->get('id')))
		{
			$this->EE->functions->redirect($this->base_url);
		}

		$query = $this->EE->db->select('group_label')
		       ->from('low_variable_groups')
		       ->where('site_id', $this->site_id)
		       ->where('group_id', $group_id)
		       ->get();

		// -------------------------------------
		//  Add data to view array and call view
		// -------------------------------------

		$this->data['group_label'] = $query->row('group_label');
		$this->data['group_id']    = $group_id;
		$this->data['confirm_message'] = lang('low_variables_group_delete_confirmation_one');

		return $this->view('mcp_del_group_conf');
	}

	/**
	 * Deletes variable group
	 *
	 * @access     public
	 * @return     void
	 */
	public function delete_group()
	{
		// -------------------------------------
		//  Get group id
		// -------------------------------------

		if ($group_id = $this->EE->input->post('group_id'))
		{
			$sql_group_id = $this->EE->db->escape_str($group_id);

			// -------------------------------------
			//  Delete from both table, update vars
			// -------------------------------------

			$this->EE->db->query("DELETE FROM `exp_low_variable_groups` WHERE `group_id` = '{$sql_group_id}'");
			$this->EE->db->query("UPDATE `exp_low_variables` SET `group_id` = '0' WHERE `group_id` = '{$sql_group_id}'");
		}

		// -------------------------------------
		//  Go to manage screen and show message
		// -------------------------------------

		$this->EE->session->set_flashdata('msg', 'low_variable_group_deleted');
		$this->EE->functions->redirect($this->base_url);
		exit;
	}

	/**
	 * Save new sort order for groups
	 *
	 * @access     public
	 * @param      bool
	 * @return     void
	 */
	public function save_group_order($redirect = FALSE)
	{
		// -------------------------------------
		//  Return url
		// -------------------------------------

		$return_url = $this->base_url;

		// -------------------------------------
		//  Get POST variable
		// -------------------------------------

		if ($groups = $this->EE->input->get_post('groups'))
		{
			if ( ! is_array($groups))
			{
				$groups = explode('|', $groups);
			}

			foreach ($groups AS $group_order => $group_id)
			{
				// -------------------------------------
				//  Escape variables
				// -------------------------------------

				$sql_group_id = $this->EE->db->escape_str($group_id);
				$sql_group_order = $this->EE->db->escape_str($group_order + 1);

				// -------------------------------------
				//  Update/Insert record
				// -------------------------------------

				$sql = "UPDATE `exp_low_variable_groups` SET `group_order` = '{$sql_group_order}' WHERE `group_id` = '{$sql_group_id}'";
				$this->EE->db->query($sql);
			}

			// -------------------------------------
			//  Add feedback to return  url
			// -------------------------------------

			// $this->EE->session->set_flashdata('msg', 'low_variable_groups_saved');
			$return_url .= AMP.'method=groups';
		}

		// -------------------------------------
		//  Go home
		// -------------------------------------

		if ($redirect) $this->EE->functions->redirect($return_url);

		die('ok');
	}

	// --------------------------------------------------------------------

	/**
	 * Include Variable Types
	 *
	 * @access	private
	 * @return	null
	 */
	private function _include_types()
	{
		// -------------------------------------
		//  Check extension settings to get which types
		// -------------------------------------

		$which = is_array($this->settings['enabled_types']) ? $this->settings['enabled_types'] : FALSE;

		// -------------------------------------
		//  Get the types using extension function
		// -------------------------------------

		$types = $this->get_types($which);

		// -------------------------------------
		//  Initiate class for each enabled type
		// -------------------------------------

		foreach ($types AS $type => $info)
		{
			if (class_exists($info['class']))
			{
				$this->types[$type] = ($info['is_fieldtype'] === TRUE) ? new Low_fieldtype_bridge($info) : new $info['class'];
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Get variable groups
	 *
	 * @access     private
	 * @param      bool
	 * @return     array
	 */
	private function _get_variable_groups($flat = TRUE)
	{
		$query = $this->EE->db->select(array('group_id', 'group_label', 'group_notes'))
		       ->from('low_variable_groups')
		       ->where('site_id', $this->site_id)
		       ->order_by('group_order', 'asc')
		       ->order_by('group_label', 'asc')
		       ->get();

		return $flat ? low_flatten_results($query->result_array(), 'group_label', 'group_id') : $query->result_array();
	}

	// --------------------------------------------------------------------

	/**
	 * Sync EE vars and Low vars
	 *
	 * Deletes Low Variables that reference to non-existing EE Variables,
	 * Creates default Low Variables that have no reference to existing EE Vars.
	 *
	 * @access     private
	 * @return     void
	 */
	private function _sync()
	{
		// -------------------------------------
		//  Get all native variable ids
		// -------------------------------------

		$query  = $this->EE->db->select('variable_id')->from('global_variables')->get();
		$ee_ids = low_flatten_results($query->result_array(), 'variable_id');

		// -------------------------------------
		//  Sync based on ee ids
		// -------------------------------------

		if ( ! empty($ee_ids))
		{
			// Delete references to non-existing native vars
			$this->EE->db->where_not_in('variable_id', $ee_ids)->delete('low_variables');

			// Get all Low Variables
			$query   = $this->EE->db->select('variable_id')->from('low_variables')->get();
			$low_ids = low_flatten_results($query->result_array(), 'variable_id');

			// Get ids that do not exist in low_var but do exist in ee_var
			if ($diff = array_diff($ee_ids, $low_ids))
			{
				foreach ($diff AS $var_id)
				{
					$this->EE->db->insert('low_variables', array(
						'variable_id'   => $var_id,
						'group_id'      => '0',
						'variable_type' => LOW_VAR_DEFAULT_TYPE,
						'edit_date'     => time()
					));
				}
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Simple license check. Yeah, I know.
	 *
	 * @access     private
	 * @return     bool
	 */
	private function _license()
	{
		$is_valid = FALSE;

		$valid_patterns = array(
			'/^\d{25}$/', // gotolow.com
			'/^[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/' // devot-ee.com
		);

		foreach ($valid_patterns AS $pattern)
		{
			if (preg_match($pattern, $this->settings['license_key']))
			{
				$is_valid = TRUE;
				break;
			}
		}

		if ( ! $is_valid)
		{
			$title = lang('low_variables_module_name');
			$this->EE->cp->set_variable('cp_page_title', $title);
			$this->error_msg
				= "Your license key appears to be invalid. You can get a valid one here: "
				. "<a href=\"{$this->docs_url}\">{$title}</a>. "
				. "Enter your key here: <a href=\"{$this->ext_url}\">{$title} Extension settings</a>";
		}

		return $is_valid;
	}

	// --------------------------------------------------------------------

} // End Class

/* End file mcp.low_variables.php */