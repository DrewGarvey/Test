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
 * BSD Votes Plugin
 *
 * @package     ExpressionEngine
 * @subpackage  Addons
 * @category    Plugin
 * @author      Blue State Digital
 * @link        
 */

$plugin_info = array(
    'pi_name'       => 'BSD Votes',
    'pi_version'    => '2.0',
    'pi_author'     => 'Blue State Digital',
    'pi_author_url' => 'http://www.bluestatedigital.com',
    'pi_description'=> 'Grab voting data from BSD Tools signup forms',
    'pi_usage'      => Bsd_votes::usage()
);


class Bsd_votes {

    public $return_data;
    private $_host;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->EE =& get_instance();
        $this->EE->load->library('QueryString');
        $this->_siteUrl = $this->EE->config->item('site_url');
        require_once 'libraries/Vote_Results.php';
    }
    
    // ----------------------------------------------------------------
    private function endpointUrl($slug, QueryString $queryString){
        return $this->_siteUrl . "/page/signup_vote_results/" . $slug . $queryString->getQueryString();
        
    }
    public function ids(){
        require_once 'libraries/Vote_Params.php';
        require_once 'libraries/VoteIds_Results.php';
        $params = new Vote_Params($this->EE->TMPL);
        $queryString = new QueryString();
        $queryString->addParam("top", $params->getLimit());
        $resultsUrl = $this->endpointUrl($params->getSlug(), $queryString);
        $results = new VoteIds_Results($resultsUrl);
        return $this->EE->TMPL->parse_variables_row($params->getTagData(), $results->getVariables());
    }
    public function votes(){
        require_once 'libraries/Vote_Params.php';
        require_once 'libraries/VoteCount_Results.php';
        $votesParams = new Vote_Params($this->EE->TMPL);
        $queryString = new QueryString();
        $queryString->addParam("tally", $votesParams->getEntryId());
        $queryString->addParam("slug", $votesParams->getSlug());
        $resultsUrl = $this->endpointUrl($votesParams->getSlug(), $queryString);
        $voteCount = new VoteCount_Results($resultsUrl);
        return $voteCount->getVoteTotal();
    }
    public function total_votes(){
        require_once 'libraries/Vote_Params.php';
        require_once 'libraries/VoteCount_Results.php';
        $votesParams = new Vote_Params($this->EE->TMPL);
        $queryString = new QueryString();
        $queryString->addParam("slug", $votesParams->getSlug());
        $resultsUrl = $this->endpointUrl($votesParams->getSlug(), $queryString);
        $voteCount = new VoteCount_Results($resultsUrl);
        return $voteCount->getAllVotesTotal();
    }
    public function rank(){
        require_once 'libraries/Vote_Params.php';
        require_once 'libraries/VoteRank_Results.php';
        $rankParams = new Vote_Params($this->EE->TMPL);
        $queryString = new QueryString();
        $queryString->addParam("top", $rankParams->getLimit());
        $resultsUrl = $this->endpointUrl($rankParams->getSlug(), $queryString);
        $voteRank = new VoteRank_Results($resultsUrl);
        $voteRank->setRanks();
        return $voteRank->getRankByEntryId($rankParams->getEntryId());
        
    }
    /**
     * Plugin Usage
     */
    public static function usage()
    {
        ob_start();
?>
    
    ===================
    The Tags
    ===================
    
    
    {exp:bsd_votes:ids}
    ===============
    Returns a pipe-separated list of entry_ids, ordered by number of votes received. Use with parse="inward"
    
    Sample tag: {exp:bsd_votes:ids slug="voting-form" limit="5" parse="inward"}
        
        Required parameters: 
            "slug" - the slug of the voting form
            "parse" - "inward" — must be set in order for this to behave as expected
            
        Optional Parameter:
            "limit" - limit the number of entries returned. Default value: 10
            
        Returns: 
            A pipe separated list of entry_ids to be used with "fixed_order"
            
            
    {exp:bsd_votes:votes} (was {exp:bsd_votes:score})
    =======================================
    Returns the number of votes received for a particular entry_id.
    
    Sample Tag: {exp:bsd_votes:votes entry_id="430" slug="voting-form"}
    Required parameter:
        "entry_id" : the entry id to return votes for
        "slug" - the slug of the voting form
        
    
    {exp:bsd_votes:rank}
    =================
    Returns the ranking for an entry, specified by entry_id.
    
    Sample Tag: {exp:bsd_votes:rank entry_id="430"}
    
        Required parameters: 
            "entry_id" - the entry_id for which to return a ranking
            "slug" - the slug of the signup form
        
        Optional parameter:
            "limit" - limit the number of entries searched. Default value: 10.
        
<?php
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }
}


/* End of file pi.bsd_votes.php */
/* Location: /system/expressionengine/third_party/bsd_votes/pi.bsd_votes.php */