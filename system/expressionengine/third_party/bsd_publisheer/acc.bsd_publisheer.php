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
 * BSD Content Type Search Accessory
 *
 * @package     ExpressionEngine
 * @subpackage  Addons
 * @category    Accessory
 * @author      Blue State Digital
 * @link        http://www.bluestatedigital.com
 */
 
class Bsd_publisheer_acc {
    
    public $name            = 'BSD PublishEEr';
    public $id              = 'bsd_publisheer';
    public $version         = '1.0';
    public $description     = 'Adds a searchable publish menu to the Control Panel';
    public $sections        = array();
    
    /**
     * Set Sections
     */
    public function set_sections()
    {
        $this->EE =& get_instance();
        $this->sections[] = "<script>$('#accessoryTabs a.bsd_publisheer').parent().remove();</script>";
        $this->EE->cp->load_package_js('list.min');
        $this->EE->cp->add_to_head('<style type="text/css">' . file_get_contents(PATH_THIRD . 'bsd_publisheer/css/publisheer.css') . '</style>');
        $this->EE->cp->load_package_js('fix-menu');
    }
    
    // ----------------------------------------------------------------
    
}
 
/* End of file acc.bsd_content_type_search.php */
/* Location: /system/expressionengine/third_party/bsd_content_type_search/acc.bsd_content_type_search.php */