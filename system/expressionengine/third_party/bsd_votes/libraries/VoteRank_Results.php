<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class VoteRank_Results extends Vote_Results {
    private $_ranking;
    private $_restructuredResults = array();
    
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
    private function restructureResults(){ // Go through the array and make votes and rank properties of the entry_id
        if (empty($this->_restructredResults)){
            foreach ($this->_ranking->result as $entry){
                $this->_restructuredResults[$entry->value]->votes = $entry->votes;
                $this->_restructuredResults[$entry->value]->rank = $entry->rank;
            }
        }
        return $this->_restructuredResults;
    }
    
    public function setRanks(){
        $this->_ranking = $this->getResultsObj(); // Need to modify the results object, so creating a private copy
        $prevRank = 1; // cache a copy of the previous object's rank
        $length = count($this->_ranking->result); // array length
        for ($i = 0; $i < $length; $i++ ){
            if ($i > 0){
                if ($this->_ranking->result[$i - 1]->votes ===  $this->_ranking->result[$i]->votes ){
                    $this->_ranking->result[$i]->rank = $prevRank;
                    
                } else {
                    $this->_ranking->result[$i]->rank = $prevRank = $i + 1;
                    
                }
            } else {
                $this->_ranking->result[$i]->rank = 1;
            }
        }
        return $this->_ranking->result;
    }
    public function getRankByEntryId($entry_id){
        if (empty($this->_restructuredResults)){
            $this->restructureResults();
        }
        return $this->_restructuredResults[$entry_id]->rank;
    }
    
}

/* End of file VoteRank_Results.php */