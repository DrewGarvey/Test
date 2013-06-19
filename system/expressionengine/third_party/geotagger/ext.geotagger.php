<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Geotagger_ext
 * 
 * An ExpressionEngine Extension that geotags addresses.
 *
 * @package		ExpressionEngine
 * @author		Natural Logic - Jason Ferrel, Barrett Newton, Inc- Chris Newton & Rob Sanchez
 * @copyright	Copyright (c) 2009-2010, Natural Logic LLC
 * @link		http://www.natural-logic.com/software/geotagger-for-expression-engine/
 * @since		Version 2
 **/

class Geotagger_ext {

	var $name		= 'Geotagger';
	var $version 		=  '2.1.2';
	var $description	= 'Geotags addresses and returns latitude/longitude points';
	var $settings_exist	= 'y';
	var $docs_url		= 'http://www.natural-logic.com/software/geotagger-for-expression-engine/';
	var $class_name		= 'Geotagger_ext'; 
	var $settings  		= array();

	/**
	 * Constructor PHP 4.0
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 * @since	2.0
	 * @author	Chris Newton
	 * @return 	void
	 */
	function Geotagger_ext($settings='')
	{
		$this->__construct($settings);
	}
	// END
	
	/**
	* PHP 5 Constructor
	*
	* @param	$settings	mixed	Array with settings or FALSE
	* @return	void
	* @author 	Natural Logic
	* @since	1.0
	*/
	function __construct( $settings='' )
	{
		$this->EE =& get_instance();
		$this->settings = $settings;
	}
	// END
	
	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://codeigniter.com/user_guide/database/index.html for more information on the db class.
	 * @todo complete hook registration
	 * @return void
	 */
	function activate_extension()
	{
		$data = array(
			'class'		=> $this->class_name,
			'method'	=> '',
			'hook'		=> '',
			'settings'	=> '',
			'priority'	=> 1,
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);

		$this->EE->db->insert('exp_extensions', $data);
	}
	// END
	
	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 * 
	 * @since 1.0
	 * @return 	mixed		void on update / false if none
	 * @param	$current 	string 
	 */
	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}

		if ($current < '2.0')
		{
			// Update to version 1.0
		}

		$this->EE->db->where('class',$this->class_name);
		$this->EE->db->update('exp_extensions', array('version' => $this->version));
	}
	// END
	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		$this->EE->db->where('class', $this->class_name);
		$this->EE->db->delete('exp_extensions');
	}
	// END
	/**
	 * Settings Form
	 *
	 * @param	Array	Settings
	 * @return 	void
	 */
	function settings_form($current)
	{
		$this->EE->load->helper('form');
		$this->EE->load->helper('html');
		$this->EE->lang->loadfile('geotagger');
		$this->EE->load->library('table');
		
		$this->fetch_channels();
		
		$setting_enabled = isset($current['enabled']) ? $current['enabled'] : 'yes'; 
		
		$vars = array(
			'site_id' => $this->EE->config->item('site_id'),
			'action_url' => 'C=addons_extensions'.AMP.'M=save_extension_settings'.AMP.'file=geotagger',
			'settings' => $this->get_settings(),
			'channels' => $this->channels,
			'version' => $this->version,
		);
		
		return $this->EE->load->view('index', $vars, TRUE);			
	}
	// END
	/**
	 * fetch_channels
	 *
	 * loads channels
	 * 
	 * @return void
	 * @author Rob Sanchez, Chris Newton
	 * @since 2.0
	 */
	function fetch_channels()
	{
		$this->EE->load->model('channel_model');
		
		$this->EE->load->model('field_model');
		
		$channels = $this->EE->channel_model->get_channels();
		
		foreach ($channels->result_array() as $channel)
		{
			$this->channels[$channel['channel_id']] = $channel;
			
			$fields = $this->EE->field_model->get_fields($channel['field_group']);
			
			foreach ($fields->result_array() as $field)
			{
				$this->channels[$channel['channel_id']]['fields'][$field['field_id']] = $field['field_label'];
			}
		}
		
		unset($query);
	}
	// END
	
	function get_settings($refresh = FALSE, $return_all = FALSE)
	{
		$settings = FALSE;

		// Get the settings for the extension
		if(isset($this->EE->session->cache['nlogic_geotagger']['settings']) === FALSE || $refresh === TRUE)
		{
			// check the db for extension settings
			$this->EE->db->select('settings');
			$this->EE->db->where('enabled', 'y');
			$this->EE->db->where('class', __CLASS__);
			$this->EE->db->limit(1);
			
			$query = $this->EE->db->get('extensions');

			// if there is a row and the row has settings
			if ($query->row('settings'))
			{
				// save them to the cache
				$this->EE->session->cache['nlogic_geotagger']['settings'] = unserialize($query->row('settings'));
			}
		}

		// check to see if the session has been set
		if(empty($this->EE->session->cache['nlogic_geotagger']['settings']) !== TRUE)
		{
			$settings = ($return_all === TRUE) ? $this->EE->session->cache['nlogic_geotagger']['settings'] : $this->EE->session->cache['nlogic_geotagger']['settings'][$this->EE->config->item('site_id')];
		}
		
		$this->google_maps_api_key_conf = (isset($this->EE->config->config['nl_go_api'])) ? $this->EE->config->config['nl_go_api'] : FALSE; 
		
		if ($this->google_maps_api_key_conf) 
		{
			$settings['google_maps_api_key'] = $this->google_maps_api_key_conf;	
		}
		
		return $settings;
	}

	/**
	 * Save extension form settings
	 * 
	 */	
	function save_settings()
	{
		unset($_POST['name'], $_POST['submit']);

		foreach ($_POST['channels'] as $key => $value)
		{
			unset($_POST['channels_' . $key]);
		}
		
		$_POST = $this->EE->security->xss_clean(array_merge($this->default_settings(), $_POST));

		$settings = $this->get_settings(TRUE, TRUE);

		$settings[$this->EE->config->item('site_id')] = $_POST;
		
		$this->EE->db->where('class', __CLASS__);
		
		$this->EE->db->update('exp_extensions', array('settings' => serialize($settings)));
	}

	/**
	 * Build base settings
	 * 
	 */	
	function default_settings()
	{
		$default_settings = array(
			'enable' 				=> 'y',
			'weblogs'				=> array(),
			'google_maps_api_key'	=> '',
		);
		
		$this->EE->load->model('channel_model');
		
		$query = $this->EE->channel_model->get_channels();
		
		foreach($query->result_array() as $row)
		{
			$default_settings['channels'][$row['channel_id']] = array(
				'display_tab' 		=> 'n',
				'address'	=> '',
				'city'	=> '',
				'state'	=> '',
				'zip'	=> '',
				'latitude'	=> '',
				'longitude'	=> '',
				'show_fields_in_geo' => 'n',
				'zoom_level' => '13',
				'zoom'	=> ''
			);
		}
		
		return $default_settings;
	}
}
// END CLASS
/* End of file ext.geotagger.php */
/* Location: ./system/expressionengine/third_party/geotagger/ext.geotagger.php */