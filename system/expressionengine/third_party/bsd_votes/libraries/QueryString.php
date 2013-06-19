<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class QueryString {
    private $_params = array();

    public function addParam($name, $value) {
        if ($value) {
            $this->_params[$name] = $value;
        }
    }
    public function getValue($key){
        return $this->_params[$key];
    }
    public function getQueryString() {
        return "?" . http_build_query($this->_params);
    }
}

/* End of file QueryString.php */