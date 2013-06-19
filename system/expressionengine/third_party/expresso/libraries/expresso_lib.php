<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine Expresso Library Class
 *
 * @package		Expresso
 * @category	Library
 * @author		Ben Croker
 * @link		http://www.putyourlightson.net/expresso
 */
 
class Expresso_lib {
	
	/**
	  *  Constructor
	  */
	function Expresso_lib()
	{
		// make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
		
		$this->site_id = $this->EE->config->item('site_id');
	}

	// --------------------------------------------------------------------

	/**
	  *  Get page links of specified module
	  */
	function get_page_links($module='')
	{
		$links = array();
		$select_text = 'Select a page...';
		
		if ($module == 'Pages' || $module == 'Structure')
		{
			// get site pages
			$this->EE->db->select('site_pages');
			$this->EE->db->where('site_id', $this->site_id);
			$query = $this->EE->db->get('sites');
		
			$site_pages = unserialize(base64_decode($query->row('site_pages')));
			$site_pages = $site_pages[$this->site_id];
		}
		
		if ($module == 'Pages')
		{
			if (isset($site_pages['uris']) && count($site_pages['uris']))
			{
				$entry_ids = array_keys($site_pages['uris']);
				
				$this->EE->db->select(array('entry_id', 'title'));
				$this->EE->db->where_in('entry_id', $entry_ids);
				$query = $this->EE->db->get('channel_titles');
				
				foreach ($query->result() as $row)
				{
					if (isset($site_pages['uris'][$row->entry_id]))
					{
						$title = $row->title;
						$url = $this->EE->functions->create_url($site_pages['uris'][$row->entry_id]);
						$links[] = array($title, $url);
					}
				}
			}			
		}
		
		else if ($module == 'Structure')
		{
			// include structure SQL model
			include_once PATH_THIRD.'structure/sql.structure.php';
			
			// get structure data
			$sql = new Sql_structure();
			$data = $sql->get_data();
	
			foreach ($data as $item)
			{
				if (isset($site_pages['uris'][$item['entry_id']]))
				{
					$title = str_repeat("-", $item['depth']).$item['title'];
					$url = $this->EE->functions->create_url($site_pages['uris'][$item['entry_id']]);
					$links[] = array($title, $url);
				}
			}			
		}
		
		else if ($module == 'Navee')
		{
			// include navee class
			include_once PATH_THIRD.'navee/mod.navee.php';
		
			$navee = new Navee();
			
			// get navee navs
			$this->EE->db->select('navigation_id, nav_name');
			$this->EE->db->where('site_id', $this->site_id);
			$query = $this->EE->db->get('navee_navs');
			$navee_navs = $query->result_array();
			
			// get navee data
			foreach ($navee_navs as $navee_nav)
			{
				$data = $navee->_getNav($navee_nav['navigation_id']);
				array_push($links, array('NavEE Menu '.$navee_nav['nav_name'], ''));
				$links = array_merge($links, $this->_parse_navee_links($data));
			}
			
			$select_text = 'Select a NavEE item...';
		}
		
		if (count($links))
		{
			array_unshift($links, array($select_text, ''));
		}
		
		return $links;
	}
	
	// --------------------------------------------------------------------

	/**
	  *  Parse navee links recursively
	  */
	private function _parse_navee_links($data, $depth=1)
	{
		$links = array();
		
		foreach ($data as $item)
		{
			if (!$item['include'])
			{
				continue;
			}
			
			$title = str_repeat("-", $depth).$item['text'];
			$url = $this->EE->functions->create_url($item['link']);
			$links[] = array($title, $url);
			
			if (count($item['kids']))
			{
				$links = array_merge($links, $this->_parse_navee_links($item['kids'], $depth + 1));
			}
		}
		
		return $links;
	}
			
}

// END CLASS

/* End of file lib.expresso.php */
/* Location: ./system/expressionengine/third_party/expresso/libraries/expresso_lib.php */
?>