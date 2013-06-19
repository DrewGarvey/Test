<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Geotagger_tab
{
	var $settings = array();
	
	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function Geotagger_tab()
	{
		$this->EE =& get_instance();
	}

	function publish_tabs($channel_id, $entry_id = '')
	{
		$this->settings = $this->get_settings();
		
		//don't show tab if no channel settings exist
		// if (! isset($this->settings['channels'][$channel_id]))
		// {
		// 	return array();
		// }		
		
		//don't show tab if channel not set to show
		// if (isset($this->settings['channels'][$channel_id]['display_tab']) && $this->settings['channels'][$channel_id]['display_tab'] != 'y')
		// {
		// 	return array();
		// }
		
		$this->EE->lang->loadfile('geotagger');
		$this->EE->load->library('javascript');
		
		//load google maps api
		$this->EE->cp->add_to_head('<script type="text/javascript" src="http://maps.google.com/maps/api/js?v=3.1&sensor=false"></script>');
		
		//add style
		$this->EE->cp->add_to_head('
			<style type="text/css">
				#geo_wrap { clear:both; overflow:hidden; width:100%; margin-bottom: 20px; margin-top: 10px; }
				#geo_map_canvas { width: 50%; height:300px; float: left; margin-left: 20px; margin-top: 66px; }
				#geo_details { width: 45%; float: left; }
				#geo_details p { padding: 1em 0; margin-right: 20px; margin-bottom: 10px }
				#geo_details p#geo_messages { background:#ffffcc; border:1px solid #cccc99; padding:1em; margin-right:0px; margin-left: 0px; line-height: 16px; }
				#geo_details p#geo_messages.geo_error { background:#ffeeee; border:1px solid #ffbbbb; color: #900; }
				#geo_details p#geo_messages.geo_success { background:#eeffee; border:1px solid #bbffbb; color: #090; }
				#geo_details a.btn { background: #142129; color: #ffffff; border-radius: 14px; -moz-border-radius: 14px; -webkit-border-radius: 11px; padding: 6px 14px; text-decoration: none; margin-left: 1em; }
				#geo_details a:hover.btn { background: #1D7FC6 }
				#geo_form { margin-bottom: 10px; clear: both; overflow: hidden; }
				#geo_form .publish_field { margin-left: 0; }
			</style>
		');
		
		//load up javascript vars
		$this->EE->cp->add_to_head('
			<script type="text/javascript">
				var NLGEO = {
					"settings_exist" : '.( ! empty($this->settings['channels'][$channel_id]['display_tab']) ? "\"".$this->settings['channels'][$channel_id]['display_tab']."\"" : 0).',
					"address_field" : '.( ! empty($this->settings['channels'][$channel_id]['address']) ? $this->settings['channels'][$channel_id]['address'] : 0).',
					"city_field" : '.( ! empty($this->settings['channels'][$channel_id]['city']) ? $this->settings['channels'][$channel_id]['city'] : 0).',
					"state_field" : '.( ! empty($this->settings['channels'][$channel_id]['state']) ? $this->settings['channels'][$channel_id]['state'] : 0).',
					"zip_field" : '.( ! empty($this->settings['channels'][$channel_id]['zip']) ? $this->settings['channels'][$channel_id]['zip'] : 0).',
					"lat_field" : '.( ! empty($this->settings['channels'][$channel_id]['latitude']) ? $this->settings['channels'][$channel_id]['latitude'] : 0).',
					"lng_field" : '.( ! empty($this->settings['channels'][$channel_id]['longitude']) ? $this->settings['channels'][$channel_id]['longitude'] : 0).',
					"zoom_field" : '.( ! empty($this->settings['channels'][$channel_id]['zoom']) ? $this->settings['channels'][$channel_id]['zoom'] : 0).',
					"default_zoom" : '.( ! empty($this->settings['channels'][$channel_id]['zoom_level']) ? $this->settings['channels'][$channel_id]['zoom_level'] : 13).',
					"inline_fields" : '.((isset($this->settings['channels'][$channel_id]['show_fields_in_geo']) && $this->settings['channels'][$channel_id]['show_fields_in_geo'] == 'y') ? 1 : 0).',
					"message_bg" : "#ffffcc",
					"msg_lat"  : "'.$this->EE->lang->line('msg_lat_updated').'",
					"msg_lng"  : "'.$this->EE->lang->line('msg_lng_updated').'",
					"msg_geo_error" : "'.$this->EE->lang->line('msg_geo_error').'",
					"label_btn_geo" : "'.(($entry_id) ? $this->EE->lang->line('btn_geo_update') : $this->EE->lang->line('btn_geo')).'",
					"label_msg_geo" : "'.(($entry_id) ? $this->EE->lang->line('msg_existing_geo') : $this->EE->lang->line('msg_before_geo')).'",
					"existing_entry" : '.(($entry_id) ? 1 : 0).',
					"msg_no_settings" : "'.$this->EE->lang->line('msg_no_settings').'"		
				}
			</script>
			');		
		
		//load geotagger.js
		$orig_view_path = $this->EE->load->_ci_view_path;
		$this->EE->load->_ci_view_path = PATH_THIRD.'geotagger/views';
		$this->EE->cp->load_package_js('geotagger');

		$this->EE->load->_ci_view_path = $orig_view_path;

		//return dummy field array() for publish tab api
		$field_settings[] = array(
			'field_id'		=> 'geotagger_field_ids',
			'field_label'		=> $this->EE->lang->line('geotagger_module_name'),
			'field_required' 	=> 'n',
			'field_data'		=> '',
			'field_list_items'	=> '',
			'field_fmt'		=> '',
			'field_instructions' 	=> '',
			'field_show_fmt'	=> 'n',
			'field_fmt_options'	=> array(),
			'field_pre_populate'	=> 'n',
			'field_text_direction'	=> 'ltr',
			'field_type' 		=> 'text',
			'field_maxl'		=> ''
		);
		
		return $field_settings;

	}

	function validate_publish($params)
	{
		return FALSE;
	}
	
	function publish_data_db($params)
	{
	}

	function publish_data_delete_db($params)
	{
	}
	
	function get_settings($refresh = FALSE, $return_all = FALSE)
	{
		$settings = FALSE;

		// Get the settings for the extension
		if(isset($this->EE->session->cache['nlogic_geotagger']['settings']) === FALSE || $refresh === TRUE)
		{
			// check the db for extension settings
			$this->EE->db->select('settings');
			$this->EE->db->where('enabled', 'y');
			$this->EE->db->where('class', 'Geotagger_ext');
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
		
		return $settings;
	}
}
/* END Class */

/* End of file tab.geotagger.php */
/* Location: ./system/expressionengine/third_party/geotagger/tab.geotagger.php */