<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class VoteCount_Results extends Vote_Results{

    public function getVoteTotal(){
        return $this->getResultsObj()->result[0]->votes;
    }

    public function getAllVotesTotal() {
        $total = 0;
        foreach ($this->getAllResultsObjects() as $obj) {
            $total += $obj->result[0]->votes;
        }
        return $total;
    }

}

/* End of file VoteCount_Results.php */