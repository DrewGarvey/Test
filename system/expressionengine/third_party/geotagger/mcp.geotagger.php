<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Geotagger_mcp
{
	var $version = '2.1.2';
	
	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function Geotagger_mcp()
	{
		$this->EE =& get_instance();
	}
	
	/**
	 * Redirect to Extension settings
	 *
	 * @access	public
	 * @return	void
	 */
	function index()
	{
		$this->EE->functions->redirect(BASE.AMP.'C=addons_extensions'.AMP.'M=extension_settings'.AMP.'file=geotagger');		
	}
}

/* End of file mcp.geotagger.php */
/* Location: ./system/expressionengine/third_party/modules/geotagger/mcp.geotagger.php */