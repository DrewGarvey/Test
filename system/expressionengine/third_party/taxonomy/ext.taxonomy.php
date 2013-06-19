<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
// ------------------------------------------------------------------------

/**
 * Taxonomy Extension
 *
 * HOOKS USED:
 * sessions_end: Loads each taxonomy tree array into the users session for performance
 * entry_submission_end: Rebuilds each taxonomy tree array
 * update_multi_entries_loop: Rebuilds each taxonomy tree array
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		Iain Urquhart
 * @link		http://iain.co.nz
 * @copyright 	Copyright (c) 2011 Iain Urquhart
 * @license   	Commercial, All Rights Reserved: http://devot-ee.com/add-ons/license/taxonomy/
 */

// ------------------------------------------------------------------------

require_once PATH_THIRD.'taxonomy/config'.EXT;

class Taxonomy_ext {
	
	public $name = TAXONOMY_NAME;
	public $version = TAXONOMY_VERSION;
	public $description = TAXONOMY_DESCRIPTION;
	public $docs_url = TAXONOMY_URL;
	public $settings_exist = 'n';
	public $settings = array();
	
	private $EE;
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		$this->EE =& get_instance();
		$this->settings = $settings;
		$this->site_id	= $this->EE->config->item('site_id');
	}// ----------------------------------------------------------------------
	
	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		// Setup custom settings in this array.
		$this->settings = array();
		
		// sessions_end
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'sessions_end',
			'hook'		=> 'sessions_end',
			'settings'	=> serialize($this->settings),
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);

		$this->EE->db->insert('extensions', $data);	
		
		// entry_submission_end
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'entry_submission_end',
			'hook'		=> 'entry_submission_end',
			'settings'	=> serialize($this->settings),
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);

		$this->EE->db->insert('extensions', $data);

		// update_multi_entries_loop
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'update_multi_entries_loop',
			'hook'		=> 'update_multi_entries_loop',
			'settings'	=> serialize($this->settings),
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);

		$this->EE->db->insert('extensions', $data);
		
		// cp_menu_array
		
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'cp_menu_array',
			'hook'		=> 'cp_menu_array',
			'settings'	=> serialize($this->settings),
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);

		$this->EE->db->insert('extensions', $data);
		
		
	}	

	// ----------------------------------------------------------------------
	
	/**
	 * adds tree arrays to the users session
	 *
	 * @param 
	 * @return 
	 */
	public function sessions_end($session)
	{
			
		// if you are loading a lot of trees, and you're noticing a performance drain from
		// unserializing all the tree arrays, feel free to comment all the code out in this method
		// to evaluate the performance. Taxonomy will query and load what it needs if it hasn't been loaded here.
		$this->EE->db->select('id, site_id, label, tree_array');
		$trees = $this->EE->db->get_where('exp_taxonomy_trees',array('site_id' => $this->site_id))->result_array();
		
		if(count($trees))
		{
			// loop through our trees and add them to session cache
			foreach ($trees as $tree)
			{
				$session->cache['taxonomy']['tree'][ $tree['id'] ]['label'] = $tree['label'];
				$session->cache['taxonomy']['tree'][ $tree['id'] ]['tree_array'] = $this->unserialize($tree['tree_array']);
				
			}
		}

	}

	// ----------------------------------------------------------------------
	
	/**
	 * updates tree_array in each exp_taxonomy_trees as node statuses and dates can & might be changed
	 *
	 * @param 
	 * @return 
	 */
	public function entry_submission_end($submitted_entry_id, $submitted_meta, $submitted_data)
	{
				
		$trees = $this->EE->db->get_where('exp_taxonomy_trees',array('site_id' => $this->site_id))->result_array();
		
		if(count($trees))
		{
			$this->EE->load->library('Ttree');
			
			// loop through our trees
			foreach ($trees as $tree)
			{
				// save a bit of overhead, only rebuild tree arrays if this entry is from a channel used in a Taxonomy tree
				$channel_preferences = ($tree['channel_preferences'] != '') ? explode('|', $tree['channel_preferences']) : array();

				if( in_array($submitted_meta['channel_id'], $channel_preferences) )
				{
					$this->EE->ttree->set_table($tree['id']);
					$this->EE->ttree->rebuild_tree_array($tree['id']);
				}
			}
		}
  
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * updates tree_array in each exp_taxonomy_trees as node statuses and dates can & might be changed
	 * essentially the same as entry_submission_end except we don't have channel_id as readily available
	 * given this function isn't called so much, we can get away with just rebuilding all the tree arrays
	 *
	 * @param 
	 * @return 
	 */
	public function update_multi_entries_loop($submitted_id, $submitted_data)
	{

		$trees = $this->EE->db->get_where('exp_taxonomy_trees',array('site_id' => $this->site_id))->result_array();
		
		if(count($trees))
		{
			$this->EE->load->library('Ttree');
			
			// loop through our trees
			foreach ($trees as $tree)
			{
				$this->EE->ttree->set_table($tree['id']);
				$this->EE->ttree->rebuild_tree_array($tree['id']);
			}
		}

	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Adds our Taxonomy menu to the main CP nav (replaces accessory)
	 *
	 * @param 
	 * @return 
	 */
	
	public function cp_menu_array($menu)
	{
		// play nice with anyone elses extensions here.
		if ($this->EE->extensions->last_call !== FALSE)
		{
			$menu = $this->EE->extensions->last_call;
		}
		
		$this->EE->lang->loadfile('taxonomy');
		$this->EE->load->helper('taxonomy');
		
		$this->base = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=taxonomy';
		$this->edit_tree_base = $this->base.AMP.'method=edit_nodes'.AMP.'tree_id=';
		$this->module_label = lang('taxonomy_nav_label');
		
		$menu[$this->module_label] = array();
		
		// fetch our trees
		$query = $this->EE->db->get_where('exp_taxonomy_trees',array('site_id' => $this->EE->config->item('site_id')));
			
		if ($query->num_rows() > 0)
		{
			foreach($query->result_array() as $row)
			{
				// check permissions for each tree
				if( has_access_to_tree($this->EE->session->userdata['group_id'], $row['permissions']) )
				{	
					// how irritating is the 'nav_' prefix? Very farking irritating.
					$this->EE->lang->language['nav_taxonomy_'.$row['label']] = $row['label'];
					$menu[$this->module_label] += array('taxonomy_'.$row['label'] => $this->edit_tree_base.$row['id']);
				}
			}
		}
		
		// seperator
		$menu[$this->module_label][0] = '----';
		
		// overview item
		$menu[$this->module_label] += array('overview' => $this->base);

		return $menu;
	}

	// ----------------------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
	}	
	
	// ----------------------------------------------------------------------
	
	/**
	 * unserialize
	 * 
	 * @access	public
	 * @param	mixed $data
	 * @return	void
	 */
	protected function unserialize($data)
	{
		$data = @unserialize(base64_decode($data));
		
		return (is_array($data)) ? $data : array();
	}
}

/* End of file ext.taxonomy.php */
/* Location: /system/expressionengine/third_party/taxonomy/ext.taxonomy.php */