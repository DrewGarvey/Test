<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Vote_Params {
    private $_eeTemplate;
    
    public function __construct(EE_Template $eeTemplate) {
        $this->_eeTemplate = $eeTemplate;
    }
    function getEntryId() {
        return $this->_eeTemplate->fetch_param('entry_id');
    }
    function getSlug() {
        return $this->_eeTemplate->fetch_param('slug');
    }
    function getLimit() {
        return $this->_eeTemplate->fetch_param('limit', '10');
    }
    function getTagData(){
        return $this->_eeTemplate->tagdata;
    }
}

/* End of file libraries/Vote_Params.php */