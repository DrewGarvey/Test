<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class VoteIds_Results extends Vote_Results {
    public function getVariables(){
        $_entryList = array();
        foreach ($this->getResultsObj()->result as $entry){
            $_entryList[] = $entry->value;
        }
        $this->_variables = array(
                    'ids' => implode("|", $_entryList) 
                    );

        return $this->_variables;
        
    }
}

/* End of file VoteIds_Results.php */