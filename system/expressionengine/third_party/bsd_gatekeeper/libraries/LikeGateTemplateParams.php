<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class LikeGateTemplateParams {
    private $_eeTemplate;
    
    public function __construct($params) {
        $this->_eeTemplate = $params['eeInstance']->TMPL;
    }
    function getAppId() {
        return $this->_eeTemplate->fetch_param('app_id');
    }
    function getAppSecret() {
        return $this->_eeTemplate->fetch_param('app_secret');
    }
    function getTagData(){
        return $this->_eeTemplate->tagdata;
    }
}

/* End of file libraries/LikeGateTemplateParams.php */
/* Location: /system/expressionengine/third_party/bsd_likee_gatee/libraries/LikeGateTemplateParams.php */