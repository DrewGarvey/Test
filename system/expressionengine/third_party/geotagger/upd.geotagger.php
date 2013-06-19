<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Geotagger_upd
{
	var $version = '2.1.2';
	
	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function Geotagger_upd()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
	}
	
	function tabs()
	{
		$tabs['geotagger'] = array(
			'geotagger_field_ids'		=> array(
								'visible'		=> 'true',
								'collapse'		=> 'false',
								'htmlbuttons'	=> 'true',
								'width'			=> '100%'
								)
				);	
				
		return $tabs;	
	}

	/**
	 * Module Installer
	 *
	 * @access	public
	 * @return	bool
	 */	
	function install()
	{
		$this->EE->db->insert('modules', array(
			'module_name' => 'Geotagger',
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'y'
		));

		//settings stored in extension settings
		/*
		$this->EE->load->dbforge();
		
		$fields = array(
			'site_id' => array('type' => 'int', 'contraint' => '4', 'unsigned' => TRUE),
			'channel_id' => array('type' => 'int', 'contraint' => '6', 'unsigned' => TRUE),
			'google_maps_api_key' => array('type' => 'varchar', 'constraint' => '100', 'null' => TRUE),
			'enable' => array('type' => 'char', 'contraint' => '1', 'default' => 'n'),
			'display_tab' => array('type' => 'char', 'contraint' => '1', 'default' => 'n'),
			'zoom_level' => array('type' => 'varchar', 'constraint' => '3', 'null' => TRUE),
			'show_fields_in_geo' => array('type' => 'char', 'contraint' => '1', 'default' => 'n'),
			'address' => array('type' => 'varchar', 'constraint' => '3', 'null' => TRUE),
			'city' => array('type' => 'varchar', 'constraint' => '3', 'null' => TRUE),
			'state' => array('type' => 'varchar', 'constraint' => '3', 'null' => TRUE),
			'zip' => array('type' => 'varchar', 'constraint' => '3', 'null' => TRUE),
			'latitude' => array('type' => 'varchar', 'constraint' => '3', 'null' => TRUE),
			'longitude' => array('type' => 'varchar', 'constraint' => '3', 'null' => TRUE),
			'zoom' => array('type' => 'varchar', 'constraint' => '3', 'null' => TRUE)
		);

		$this->EE->dbforge->add_field($fields);

		$this->EE->dbforge->create_table('geotagger');
		*/

		$this->EE->cp->add_layout_tabs($this->tabs(), 'geotagger');

		return TRUE;
	}
	
	/**
	 * Module Uninstaller
	 *
	 * @access	public
	 * @return	bool
	 */
	function uninstall()
	{
		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => 'Geotagger'));

		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');

		$this->EE->db->where('module_name', 'Geotagger');
		$this->EE->db->delete('modules');
		
		/*
		$this->EE->load->dbforge();
		$this->EE->dbforge->drop_table('geotagger');		
		*/
		
		$this->EE->cp->delete_layout_tabs($this->tabs(), 'geotagger');

		return TRUE;
	}



	/**
	 * Module Updater
	 *
	 * @access	public
	 * @return	bool
	 */
	function update($current='')
	{
		return TRUE;
	}
	
}
/* END Class */

/* End of file upd.geotagger.php */
/* Location: ./system/expressionengine/third_party/modules/geotagger/upd.geotagger.php */