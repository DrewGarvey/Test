<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class LikeGate {
    
    private $_appId;
    private $_appSecret;
    private $_request;
    private $_fbData;
    private $_encodedSig;
    private $_load;
    private $_variables = array();
    
    public function __construct($params){
        
        $this->_request = $params['signed_request'];
        $this->setGate();
    }
    private function setGate(){
        if ($this->_request){
            list($this->_encoded_sig, $this->_load) = explode('.', $this->_request, 2);
            $this->_fbData = json_decode(base64_decode(strtr($this->_load, '-_', '+/')), true);
            if (! empty($this->_fbData["page"]["liked"])){
                $this->_variables['liked'] = TRUE;
            } else {
                $this->_variables['liked'] = FALSE;
            }
        } else {
            $this->_variables['liked'] = FALSE;
        }
    }
    public function setAppId($appId){
        $this->_appId = $appId;
    }
    public function setAppSecret($appSecret){
        $this->_appSecret = $appSecret;
    }
    public function getGate(){
        return $this->_variables;
    }
    
}
/* End of file libraries/Likegate.php */
/* Location: /system/expressionengine/third_party/bsd_likee_gatee/libraries/Likegate.php */