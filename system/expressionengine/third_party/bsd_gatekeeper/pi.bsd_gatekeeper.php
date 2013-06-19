<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package     ExpressionEngine
 * @author      ExpressionEngine Dev Team
 * @copyright   Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license     http://expressionengine.com/user_guide/license.html
 * @link        http://expressionengine.com
 * @since       Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * BSD GatekEEper Plugin
 *
 * @package     ExpressionEngine
 * @subpackage  Addons
 * @category    Plugin
 * @author      Blue State Digital
 * @link        
 */

$plugin_info = array(
    'pi_name'       => 'BSD FB GatekEEper',
    'pi_version'    => '0.1',
    'pi_author'     => 'Blue State Digital',
    'pi_author_url' => 'http://www.bluestatedigital.com',
    'pi_description'=> 'Displays conditional content depending on whether a visitor has "Liked" your Facebook page',
    'pi_usage'      => Bsd_gatekeeper::usage()
);


class Bsd_gatekeeper {

    public $return_data;
    private $_signedRequest;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->EE =& get_instance();
		isset($_REQUEST['signed_request']) ? $this->_signedRequest = $_REQUEST['signed_request'] : $this->_signedRequest = FALSE;
        
    }
    
    // ----------------------------------------------------------------
    
    public function gate(){
        $tmplParams = array('eeInstance' => $this->EE);
        $gateParams = array('signed_request' => $this->_signedRequest);
        $this->EE->load->library("likeGateTemplateParams", $tmplParams);
        $this->EE->load->library("likegate", $gateParams);
        $this->EE->likegate->setAppId($this->EE->likegatetemplateparams->getAppId());
        $this->EE->likegate->setAppSecret($this->EE->likegatetemplateparams->getAppSecret());
        return $this->EE->TMPL->parse_variables_row($this->EE->likegatetemplateparams->getTagData(), $this->EE->likegate->getGate());
    }
    
    /**
     * Plugin Usage
     */
    public static function usage()
    {
        ob_start();
?>

    BSD GatekEEper lets you display different content on a Facebook page tab depending on whether a Facebook user has "liked" your page. 

    It uses a very simple syntax:

        {exp:bsd_gatekeeper:gate}

        {if liked}
        <h2>You like this page</h2>
        {/if}

        {if !liked}
        </h2>You don't like this page.</h2>
        {/if}

        {/exp:bsd_gatekeeper:gate}


    Between the {exp:bsd_gatekeeper:gate} tags, one variable exists: {liked}, which is either true or false. 

    {liked} is a boolean. In other words, this syntax will not work:

        {if liked == "true"}

    F
    
    For more information about iframe tabs for Facebook pages, read their blog post: https://developers.facebook.com/blog/post/462/.
    
    Facebook's Page Tab Tutorial: https://developers.facebook.com/docs/appsonfacebook/pagetabs/
    
<?php
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }
}


/* End of file pi.bsd_likee_gatee.php */
/* Location: /system/expressionengine/third_party/bsd_likee_gatee/pi.bsd_likee_gatee.php */