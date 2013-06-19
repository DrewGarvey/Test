<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Vote_Results {
    protected $_data;
    protected $_resultsUrl;
    protected $_dataObj;
    protected $_variables = array();
    
    public function __construct($resultsUrl){
        $this->_resultsUrl = $resultsUrl;
    }

    public function getResultsUrl(){
        return $this->_resultsUrl;
    }

    public function getResultsObj(){
        $this->loadData();
        return $this->_data[0];

    }
    
    public function getAllResultsObjects(){
        $this->loadData();
        return $this->_data;

    }

    public function loadData(){
        $this->_data = file_get_contents($this->_resultsUrl);
        $this->_data = json_decode($this->_data);
    }

}

/* End of file VoteIds_Results.php */