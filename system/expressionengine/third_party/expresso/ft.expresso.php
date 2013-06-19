<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine Expresso Fieldtype Class
 *
 * @package		Expresso
 * @category	Fieldtypes
 * @author		Ben Croker
 * @link		http://www.putyourlightson.net/expresso
 */
 

// get config
require_once PATH_THIRD.'expresso/config'.EXT;
		
		
class Expresso_ft extends EE_Fieldtype {

	var $info = array(
		'name'		=> EXPRESSO_NAME,
		'version'	=> EXPRESSO_VERSION
	);
	
	
	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();
		
		// get settings
		require PATH_THIRD.'expresso/settings'.EXT;

		$this->theme_folder_url = $this->EE->config->slash_item('theme_folder_url').'third_party/expresso/';
		
		// setup cache 
		if (!isset($this->EE->session->cache['expresso']))
		{
			$this->EE->session->cache['expresso'] = array();
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Install
	  */
	function install()
	{
		$settings = array();
		
		// default global settings
		$settings['global'] = $this->default_settings;
			
		return $settings;
	}
		
	// --------------------------------------------------------------------
	
	/**
	  *  Display Field
	  */
	function display_field($data)
	{
		$file_uploads = $this->_file_uploads();
		
		$this->_include_scripts($file_uploads);
		
		$js = $this->_get_config($this->field_name);
		$js .= NL.'$(function() {'.NL.'expresso("'.$this->field_name.'", '.$file_uploads.', expresso_config_'.$this->field_name.');'.NL.'});';
		$this->EE->cp->add_to_foot('<script type="text/javascript">'.NL.$js.NL.'</script>');
		
		return '<textarea id="'.$this->field_name.'" name="'.$this->field_name.'" class="expresso">'.$data.'</textarea>';
	}
	
	// --------------------------------------------------------------------

	/**
	  *  Display Matrix Cell
	  */
	function display_cell($data)
	{
		$file_uploads = $this->_file_uploads();
		
		$this->_include_scripts($file_uploads);
	
		$local_settings = array(
			'height' => '150px',
			'contentsCss' => ''
		);
		
		$this->settings = array_merge($this->settings, $local_settings);
	
		// add matrix script if not already added
		if (!isset($this->EE->session->cache['expresso']['added_matrix']))
		{
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="'.$this->theme_folder_url.'javascript/matrix.js"></script>');
			
			$this->EE->session->cache['expresso']['added_matrix'] = TRUE;
		}
	
		// add config js if not already added
		if (!isset($this->EE->session->cache['expresso']['matrix_columns'][$this->col_id]))
		{
			$js = $this->_get_config('matrix_col_id_'.$this->col_id);
			$js .= NL.'var expresso_file_upload_matrix_col_id_'.$this->col_id.' = 1;';
			$this->EE->cp->add_to_foot('<script type="text/javascript">'.NL.$js.NL.'</script>');
			
			$this->EE->session->cache['expresso']['matrix_columns'][$this->col_id] = TRUE;
		}
		
		return '<textarea name="'.$this->cell_name.'" class="expresso">'.$data.'</textarea>';
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Display Low Variables Fieldtype
	  */
    function display_var_field($data)
    {
		$file_uploads = $this->_file_uploads();
		
		$this->_include_scripts($file_uploads);
		
		// get global variables manually - hopefully Low will add a fix to his add-on so that this is not necessary anymore
		$this->EE->db->select('settings');
		$this->EE->db->where('name', 'expresso');
		$query = $this->EE->db->get('fieldtypes');
		$settings = $query->row('settings');
		$global_settings = is_array($settings) ? $settings : unserialize(base64_decode($settings));

		$this->settings = array_merge($global_settings, $this->settings);
		
		$field_id = str_replace(array('[', ']'), array('_', ''), $this->field_name);
		
		$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->theme_folder_url.'skins/expresso2/expresso.css" />');
				
		$js = $this->_get_config($field_id);
		$js .= NL.'$(function() {'.NL.'expresso("'.$field_id.'", '.$file_uploads.', expresso_config_'.$field_id.');'.NL.'});';
		$this->EE->cp->add_to_foot('<script type="text/javascript">'.NL.$js.NL.'</script>');
		
		return '<textarea id="'.$field_id.'" name="'.$this->field_name.'" class="expresso">'.$data.'</textarea>';
    }
    
	// --------------------------------------------------------------------
    
	/**
	  *  Check if file uploads allowed
	  */
	private function _file_uploads()
	{		
		$return = 0;
		
		// if we are on publish page
		if ($this->EE->input->get('C') == 'content_publish')
		{
			// check that there is at least one upload destination
			$upload_preferences = array();
		
			if (version_compare(APP_VER, '2.4', '>='))
			{
				$this->EE->load->model('File_upload_preferences_model');	
				$upload_preferences = $this->EE->File_upload_preferences_model->get_file_upload_preferences();
			}
			else
			{
				$this->EE->load->model('tools_model');	
				$upload_preferences = $this->EE->tools_model->get_upload_preferences()->result_array();
			}
		
			if (count($upload_preferences))		
			{
				$return = 1;
			}
		}
		
		return $return;
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Include Scripts
	  */
	private function _include_scripts($file_uploads)
	{		
		// include scripts if not already included
		if (!isset($this->EE->session->cache['expresso']['included_scripts']))
		{
			// add ckeditor scripts
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="'.$this->theme_folder_url.'ckeditor/ckeditor.js"></script>');
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="'.$this->theme_folder_url.'ckeditor/adapters/jquery.js"></script>');
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="'.$this->theme_folder_url.'javascript/expresso.js"></script>');			
			
			// add global variables
			$js = 'var theme_folder_url = "'.$this->theme_folder_url.'";';
			$js .= NL.'var ee22 = '.(version_compare(APP_VER, '2.2', '>=') ? 1 : 0).';';
			
			// check if there are extra links for link dialog
			$extra_links = $this->_get_extra_links();
			$js .= NL.'var extra_links = '.(count($extra_links) ? $this->EE->javascript->generate_json($extra_links, true) : 'false').';';
			
			// customise_dialogs
			$js .= NL.'customise_dialogs('.$file_uploads.');';
			
			$this->EE->cp->add_to_foot('<script type="text/javascript">'.NL.$js.NL.'</script>');
			
			$this->EE->session->cache['expresso']['included_scripts'] = TRUE;
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Get Config
	  */
	private function _get_config($id)
	{		
		// toolbar
		$settings['toolbar'] = $this->settings['toolbar'];
		
		// check if height is set in local settings
		$settings['height'] = $this->settings['height'] ? $this->settings['height'] : $this->settings['global']['height'];
		
		// merge global and local css and remove line breaks
		$settings['contentsCss'] = str_replace(array("\n", "\t", "\""), array(" ", " ", "\'"), $this->settings['global']['contentsCss'].' '.$this->settings['contentsCss']);
		
		// get clean settings	
		$settings = array_merge($this->_clean_settings($this->settings['global']), $this->_clean_settings($settings));
		
			
		$js = 'var expresso_config_'.$id.' = {';
	
		// loop through field settings
		foreach ($this->field_settings as $key)
		{
			if (is_array($settings[$key]) || trim($settings[$key]))
			{
				$js .= $key.': "'.$settings[$key].'", ';
			}
		}
				
		
		// custom toolbar
		if ($settings['toolbar'] == 'custom' && isset($settings['custom_toolbar']))
		{
			$js .= 'toolbar: ['.$settings['custom_toolbar'].']';
		}

		// BSD custom config to set defaults for full toolbar
		elseif ($settings['toolbar'] == 'full')
		{
			$js .= 'toolbar: ['.$this->bsd_default.']';
			// $js .= 'toolbar: ['.$this->toolbar_options.']';
		}
		
		// non custom toolbars
		else 
		{		
			$js .= 'toolbar: [["Bold","Italic","Underline","Strike"]';	
			
			if ($settings['toolbar'] != 'light')
			{	
				$block = array();
				
				if (in_array('List Block', $settings['toolbar_icons']))
				{
					$block[] = '"NumberedList"';
					$block[] = '"BulletedList"';
				}
				
				if (in_array('Indent', $settings['toolbar_icons']) )
				{
					$block[] = '"Outdent"';
					$block[] = '"Indent"';
				}
								
				if (in_array('Blockquote', $settings['toolbar_icons']) )
				{
					$block[] = '"Blockquote"';
				}
				
				$js .= empty($block) ? '' : ',['.implode(',', $block).']';
				
				$js .= in_array('Justify Block', $settings['toolbar_icons']) ? ',["JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock"]' : '';
				
				$js .= in_array('PasteFromWord', $settings['toolbar_icons']) ? ',["PasteFromWord"]' : '';
			}
			
			if ($settings['toolbar'] == 'full')
			{
				$headers = '';
				
				for ($i = 1; $i <= 6; $i++)
				{
					if (in_array('h'.$i, $settings['headers']))
					{
						$headers .= '"h'.$i.'",';
					}
				}				
				
				$js .= $headers ? ',['.$headers.'"RemoveFormat"]' : '';
				$js .= (!$headers && in_array('RemoveFormat', $settings['toolbar_icons'])) ? ',["RemoveFormat"]' : '';
				$js .= (isset($settings['styles']) AND $settings['styles']) ? ',["Styles"]' : '';
				$js .= ',["Link","Unlink"';
				$js .= in_array('Anchor', $settings['toolbar_icons']) ? ',"Anchor"' : '';
				$js .= ',"Image"';
				$js .= in_array('Video', $settings['toolbar_icons']) ? ',"Video"' : '';
				$js .= in_array('Flash', $settings['toolbar_icons']) ? ',"Flash"' : '';
				$js .= in_array('Table', $settings['toolbar_icons']) ? ',"Table"' : '';
				$js .= in_array('Iframe', $settings['toolbar_icons']) ? ',"Iframe"' : '';
				$js .= ']';
			}
			
			else if ($settings['toolbar'] == 'simple')
			{
				$js .= ',["Link","Unlink"]';
			}
			
			$js .= in_array('Maximize', $settings['toolbar_icons']) ? ',["Maximize"]' : '';
				
			if ($settings['toolbar'] != 'light')
			{
				$js .= in_array('ShowBlocks', $settings['toolbar_icons']) ? ',["ShowBlocks"]' : '';
				$js .= in_array('Source', $settings['toolbar_icons']) ? ',["Source"]' : '';	
			}
			
			$js .= ']';
		}
		
		
		// styles
		if (isset($settings['styles']) AND $settings['styles'])
		{
			$js .= ', stylesSet: ['.$settings['styles'].']';
		}
		
		
		$js .= '};';
		
		return $js;
	}	

	// --------------------------------------------------------------------
	
	/**
	  *  Get extra links from Structure / NavEE / Pages
	  */
	private function _get_extra_links()
	{
		$links = array();
	
		// can't order by specific values with active record so create sql manually
		$fields = implode(', ', array_map(create_function('$a', 'return "\'$a\'";'), $this->extra_link_modules));
		
		// check which if any of the modules are installed
		$sql = 'SELECT module_name FROM exp_modules WHERE module_name IN ('.$fields.') ORDER BY FIELD(module_name, '.$fields.')';
		
		$query = $this->EE->db->query($sql);	
		
		if ($query AND $query->num_rows)
		{
			// include expresso library
			require PATH_THIRD.'expresso/libraries/expresso_lib.php';
			$expresso_lib = new Expresso_lib();
			
			foreach ($query->result() as $row) 
			{
				$links[] = array('name' => $row->module_name, 'links' => $expresso_lib->get_page_links($row->module_name));
			}
		}
		
		return $links;
	}
	
	// --------------------------------------------------------------------

	/**
	 *  Replace Tag
	 */
	function replace_tag($data, $params = array())
	{
		$data = $this->EE->typography->parse_type(
			$this->EE->functions->encode_ee_tags($data),
			array(
				'text_format'   => 'none',
				'html_format'   => 'all',
				'auto_links'    => $this->row['channel_auto_link_urls'],
				'allow_img_url' => $this->row['channel_allow_img_urls']
			)
		);
		
		
		// loop through parameters - order is important!
		foreach ($params as $key => $val) 
		{
			if ($key == 'paragraph_limit')
			{				
				if (substr_count($data, '</p>') > $val)
				{
					$pos = $this->_strnposr($data, '</p>', $val);
					$data = substr($data, 0, ($pos + 4));
				}
			}
			
			else if ($key == 'strip_tags' && $val == 'true')
			{
				$allowable_tags = isset($params['allowable_tags']) ? $params['allowable_tags'] : '';
				$data = strip_tags($data, $allowable_tags);
			}
			
			else if ($key == 'word_limit')
			{
				$data = $this->EE->functions->word_limiter($data, $val);
			}
			
			else if ($key == 'character_limit')
			{
				$data = $this->EE->functions->char_limiter($data, $val);
			}			
		}
		
		
		// add videojs to html5 video tags
		$data = str_ireplace('<video ', '<video class="video-js vjs-default-skin" data-setup="{}" ', $data);
		
		
		return $data;
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Find the position of the nth occurrence of a substring in a string
	  */
	private function _strnposr($haystack, $needle, $occurrence, $pos=0) 
	{
		if ($occurrence <= 1)
		{
			return strpos($haystack, $needle, $pos);
		}
		
		$pos = strpos($haystack, $needle, $pos) + 1;
		$occurrence--;
		
    	return $this->_strnposr($haystack, $needle, $occurrence, $pos);
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Display Global Settings
	  */
	function display_global_settings()
	{
		$this->EE->load->library('table');
		$this->EE->lang->loadfile('expresso');
		
		$this->EE->cp->set_right_nav(array(
				'export_settings' => '#export_settings', 
				'import_settings' => '#import_settings'
		));
		
		$this->theme_folder_url = $this->EE->config->slash_item('theme_folder_url').'third_party/expresso/';
			
		$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->theme_folder_url.'css/expresso.css" />');		
		$this->EE->cp->load_package_js('global_settings');
		
		// export settings
		$export_settings = array();
		
		// get clean settings
		$settings = $this->_clean_settings($this->settings['global']);
		
		$vars['settings'] = array();
		
		// loop through default settings (in case new ones were added)
		foreach ($this->default_settings as $key => $val)
		{		
			$val = isset($settings[$key]) ? $settings[$key] : $val;
			
			$export_settings[$key] = $val;
			
			switch ($key) 
			{
				case 'license_number':
					$vars['settings'][$key] = form_input(array(
						'name' => $key, 
						'value' => $val,
						'style' => ($this->_valid_license($val) ? '' : 'border-color: #CE0000;')
					));
					$vars['license'] = $this->_valid_license($val) ? base64_decode($this->license_type) : 'invalid_license';
					break;
				case 'contentsCss':
				case 'custom_toolbar':
				case 'styles':
					$vars['settings'][$key] = form_textarea($key, $val, 'class="'.$key.'"');
					break;
				case 'toolbar_icons':
					$vars['settings'][$key] = '<div class="toolbar_icons">';
					foreach ($this->all_toolbar_icons as $icon)
					{
						$vars['settings'][$key] .= '<div class="icon_set">'.form_checkbox('toolbar_icons[]', $icon, in_array($icon, $val)).' <img src="'.$this->theme_folder_url.'icons2/'.str_replace(' ', '_', strtolower($icon)).'.png" alt="'.$icon.'" title="'.$icon.'" /></div>';
					}
					$vars['settings'][$key] .= '</div>';
					break;
				case 'headers':
					$vars['settings'][$key] = '<div class="headers">';
					for ($i = 1; $i <= 6; $i++)
					{
						$vars['settings'][$key] .= '<div class="icon_set">'.form_checkbox('headers[]', 'h'.$i, in_array('h'.$i, $val)).'  <img src="'.$this->theme_folder_url.'icons2/h'.$i.'.png" alt="H'.$i.'" title="H'.$i.'" /></div>';						
					}
					$vars['settings'][$key] .= '</div>';
					break;
				default:
					$vars['settings'][$key] = form_input($key, $val);
					break;
			}
		}				
		
		
		// encode and serialize export settings
		$vars['export_settings'] = base64_encode(serialize(array('global' => $export_settings)));
		
		
		return $this->EE->load->view('global_settings', $vars, TRUE);
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Save Global Settings
	  */	
	function save_global_settings()
	{
		if ($this->EE->input->post('import'))
		{
			if (!$import_settings = unserialize(base64_decode($this->EE->input->post('import_settings'))))
			{
				$this->EE->lang->loadfile('expresso');
				$this->EE->output->show_user_error('submission', lang('import_settings_error'));
			}
			
			return $import_settings;
		}
	
		$settings = array();
				
		// loop through default settings (in case new ones were added)
		foreach ($this->default_settings as $key => $val)
		{
			$settings[$key] = $this->EE->input->post($key) ? $this->EE->input->post($key) : $val;
			
			// ensure empty values are allowed in arrays
			if (($key == 'headers' || $key == 'toolbar_icons') && !$this->EE->input->post($key))
			{
				$settings[$key] = array();
			}
		}
		
		return array('global' => $settings);
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Display Settings
	  */
	function display_settings($settings)
	{
		$this->EE->lang->loadfile('expresso');
		
		// get clean settings
		$settings = $this->_clean_settings($settings);
		
		// toolbar type
		$toolbar = isset($settings['toolbar']) ? $settings['toolbar'] : 'full';		
		
		$this->EE->table->add_row(
			lang('toolbar', 'toolbar'),
			form_dropdown('toolbar', $this->toolbar_options, $toolbar)
		);
				
		$options = array(1 => 'Yes', 0 => 'No');
		
		// height
		$height = isset($settings['height']) ? $settings['height'] : $this->settings['global']['height'];
			
		$this->EE->table->add_row(
			lang('height', 'height'),
			form_input(array('name' => 'height', 'value' => $height))
		);
		
		// css
		$contentsCss = isset($settings['contentsCss']) ? $settings['contentsCss'] : '';
			
		$this->EE->table->add_row(
			lang('contentsCss', 'contentsCss'),
			form_textarea(array('name' => 'contentsCss', 'value' => $contentsCss))
		);
	}

	// --------------------------------------------------------------------

	/**
	  *  Save settings
	  */
	function save_settings()
	{		
		$settings = array(
			'toolbar' => $this->EE->input->post('toolbar'),
			'height' => $this->EE->input->post('height'),
			'contentsCss' => $this->EE->input->post('contentsCss')
		);
		
		return $settings;		
	}
		
	// --------------------------------------------------------------------

	/**
	  *  Display Matrix Cell Settings
	  */
	function display_cell_settings($settings)
	{
		$this->EE->lang->loadfile('expresso');
		
		$settings['toolbar'] = isset($settings['toolbar']) ? $settings['toolbar'] : 'simple';		
		
		return array(
			array(str_replace(' ', '&nbsp;', lang('toolbar')), form_dropdown('toolbar', $this->toolbar_options, $settings['toolbar']))
		);
	}
	
	// --------------------------------------------------------------------

	/**
	  *  Display Low Variable Field Settings
	  */
	function display_var_settings($settings)
	{
		$this->EE->lang->loadfile('expresso');
		
		$settings['toolbar'] = isset($settings['toolbar']) ? $settings['toolbar'] : 'simple';
		
		return array(
			array(str_replace(' ', '&nbsp;', lang('toolbar')), form_dropdown('toolbar', $this->toolbar_options, $settings['toolbar']))
		);
	}
	
	// --------------------------------------------------------------------

	/**
	  *  Save Low Variable Field Settings
	  */
	function save_var_settings()
	{
		return $this->save_settings();	
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Return Clean Settings
	  */
	private function _clean_settings($settings)
	{
		foreach ($settings as $key => $val)
		{			
			switch ($key) 
			{
				case 'uiColor':
					$settings[$key] = (substr($val, 0, 1) != '#') ? '#'.$val : $val;
					break;			 
			 	case 'height':
			 		$settings[$key] = (substr($val, -2) != 'px') ? $val.'px' : $val;
			 		break;	
		 		case 'toolbar':
		 			$settings[$key] = array_key_exists($val, $this->toolbar_options) ? $val : 'full';
		 			break;
			}
		}
		
		// version 1.1 - use default toolbar icon settings if this is global settings (use license number to check)
		if (isset($settings['license_number']))
		{
			$settings['toolbar_icons'] = isset($settings['toolbar_icons']) ? $settings['toolbar_icons'] : $this->default_settings['toolbar_icons'];
		}
		
		return $settings;
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Checks if is valid license
	  */
	private function _valid_license($string)
	{
		return preg_match("/^([a-z0-9]{8})-([a-z0-9]{4})-([a-z0-9]{4})-([a-z0-9]{4})-([a-z0-9]{12})$/", $string);
	}	
		
}

// END CLASS

/* End of file ft.expresso.php */
/* Location: ./system/expressionengine/third_party/expresso/ft.expresso.php */