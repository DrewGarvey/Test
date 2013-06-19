<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


require_once PATH_THIRD.'playa/config.php';


/**
 * Playa Extension Class for ExpressionEngine 2
 *
 * @package   Playa
 * @author    Brandon Kelly <brandon@pixelandtonic.com>
 * @copyright Copyright (c) 2011 Pixel & Tonic, Inc
 */
class Playa_ext {

	var $name = PLAYA_NAME;
	var $version = PLAYA_VER;
	var $description = PLAYA_DESC;
	var $settings_exist = 'n';
	var $docs_url = PLAYA_DOCS;

	/**
	 * Extension Constructor
	 */
	function __construct()
	{
		$this->EE =& get_instance();

		// -------------------------------------------
		//  Prepare Cache
		// -------------------------------------------

		if (! isset($this->EE->session->cache['playa']))
		{
			$this->EE->session->cache['playa'] = array();
		}

		$this->cache =& $this->EE->session->cache['playa'];

		// -------------------------------------------
		//  Load the helper
		// -------------------------------------------

		if (! class_exists('Playa_Helper'))
		{
			require_once PATH_THIRD.'playa/helper.php';
		}

		$this->helper = new Playa_Helper();
	}

	// --------------------------------------------------------------------

	/**
	 * Activate Extension
	 */
	function activate_extension()
	{
		$this->EE->db->insert('extensions', array(
			'class'    => 'Playa_ext',
			'method'   => 'channel_entries_tagdata',
			'hook'     => 'channel_entries_tagdata',
			'settings' => '',
			'priority' => 9,
			'version'  => $this->version,
			'enabled'  => 'y'
		));
	}

	/**
	 * Update Extension
	 */
	function update_extension($current = FALSE)
	{
		if (! $current || $current == $this->version)
		{
			return FALSE;
		}

		if (version_compare($current, '3.0.4', '<'))
		{
			$this->EE->db->where('class', 'Playa_ext');
			$this->EE->db->where('hook', 'channel_entries_tagdata');
			$this->EE->db->update('extensions', array('priority' => 9));
		}

		$this->EE->db->where('class', 'Playa_ext');
		$this->EE->db->update('extensions', array('version' => $this->version));
	}

	/**
	 * Disable Extension
	 */
	function disable_extension()
	{
		$this->EE->db->query('DELETE FROM exp_extensions WHERE class = "Playa_ext"');
	}

	// --------------------------------------------------------------------

	/**
	 * Get Site Fields
	 */
	private function _get_site_fields($site_id)
	{
		if (! isset($this->cache['site_fields'][$site_id]))
		{
			$this->EE->db->select('field_id, field_name');
			$this->EE->db->where('field_type', 'playa');
			if ($site_id) $this->EE->db->where('site_id', $site_id);

			$fields = $this->EE->db->get('channel_fields')
			                       ->result();

			if ($fields)
			{
				foreach ($fields as $field)
				{
					$this->cache['site_fields'][$site_id][$field->field_id] = $field->field_name;
				}
			}
			else
			{
				$this->cache['site_fields'][$site_id] = array();
			}
		}

		return $this->cache['site_fields'][$site_id];
	}

	/**
	 * channel_entries_tagdata hook
	 */
	function channel_entries_tagdata($tagdata, $row, &$Channel)
	{
		// has this hook already been called?
		if ($this->EE->extensions->last_call)
		{
			$tagdata = $this->EE->extensions->last_call;
		}

		// cache the row data
		if (! isset($this->cache['entry_rows'][$row['entry_id']]))
		{
			$this->cache['entry_rows'][$row['entry_id']] =& $row;
		}

		$this->row =& $row;

		// -------------------------------------------
		//  Parse module tags
		// -------------------------------------------

		// any {exp:playa:xyz} tags?
		if (strstr($tagdata, LD.'exp:playa:') !== FALSE)
		{
			$tagdata = preg_replace_callback('/\{exp:playa:([a-z_]+)(.*?)\}((.*?)\{\/exp:playa:\1\})?/s', array(&$this, '_prep_mod_tag'), $tagdata);
		}

		// -------------------------------------------
		//  Parse fieldtype tags
		// -------------------------------------------

		// ignore if disable="custom_fields" set
		$disable = explode('|', $this->EE->TMPL->fetch_param('disable'));

		if (! in_array('custom_fields', $disable))
		{
			$site_id = isset($row['entry_site_id']) ? $row['entry_site_id'] : 0;

			// iterate through each Playa field
			foreach ($this->_get_site_fields($site_id) as $field_id => $field_name)
			{
				$this->field_id = $field_id;
				$tagdata = preg_replace_callback("/\{({$field_name}(\s+.*?)?)\}(.*?)\{\/{$field_name}\}/s", array(&$this, '_prep_ft_tag_pair'), $tagdata);
				unset($this->field_id);
			}
		}

		unset($this->row);

		return $tagdata;
	}

	/**
	 * Prep Module Tag
	 */
	private function _prep_mod_tag($m)
	{
		$func = $m[1];

		$params = isset($m[2]) ? $m[2] : '';
		$tagdata = isset($m[4]) ? $m[4] : '';

		// add entry_id= to params
		$params = ' entry_id="'.$this->row['entry_id'].'"'.$params;

		return $this->helper->mod_tag_alias($params, $tagdata, $func);
	}

	/**
	 * Prep Fieldtype Tag Pair
	 */
	private function _prep_ft_tag_pair($m)
	{
		// prevent {exp:channel:entries} from parsing this tag
		unset($this->EE->TMPL->var_pair[$m[1]]);

		$params = isset($m[2]) ? $m[2] : '';
		$tagdata = isset($m[3]) ? $m[3] : '';

		// add entry_id= and field_id= to params
		$params = ' entry_id="'.$this->row['entry_id'].'" field_id="'.$this->field_id.'"'.$params;

		return $this->helper->mod_tag_alias($params, $tagdata, 'children');
	}

}
