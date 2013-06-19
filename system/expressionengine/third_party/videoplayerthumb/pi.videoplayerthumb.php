<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Videoplayerthumb Class
 *
 * @package			ExpressionEngine
 * @category		Plugin
 * @author			Bryan Dion with most of code from Benjamin David Video Player Plugin
 * @copyright		Copyright (c) Two December
 * @link			http://twodecember.com/
 */

$plugin_info = array(
                        'pi_name'        => 'Video Player Thumbnail',
                        'pi_version'     => '1.0',
                        'pi_author'      => 'Bryan Dion with most of code from Benjamin David',
                        'pi_author_url'  => 'http://click2jumpstart.com/',
                        'pi_description' => 'Get the thumbnail for Youtube, Vimeo, Dailymotion, Veoh, MySpace, Metacafe and Revver videos',
                        'pi_usage'       => Videoplayerthumb::usage()
                    );



class Videoplayerthumb {
	
    function thumb() {
		$this->EE =& get_instance();
		
		/* set up plugin parameters */
		$url = trim($this->EE->TMPL->fetch_param('src'));
		$error = $this->EE->TMPL->fetch_param('error');
		$url_only = $this->EE->TMPL->fetch_param('url_only');

		/* check if slashes are encoded and decodes html if so */
		if(preg_match("/&#47;/", $url)) {
			$url = html_entity_decode($url);
		}
		
		/* 
			Configuration of each video service :
		
			- regexp : regular expression for identifying the video service and extracting the video ID
			- img : pattern of the thumbnail/XML URL to call from an embed tag (%s : video id)
			
		*/
		
		$services = array();
		
		$services['youtube']['img'] = "http://img.youtube.com/vi/%s/default.jpg";
		$services['youtube']['regexp'] = array('/^https?:\/\/(www\.)?youtube\.com.*\/watch\?v=(.*)/', 2);
		
		$services['vimeo']['img'] = "http://vimeo.com/api/v2/video/%s.xml";
		$services['vimeo']['regexp'] = array('/^https?:\/\/(www\.)?vimeo\.com\/([0-9]*)/', 2);
				
		$services['dailymotion']['img'] = "http://www.dailymotion.com/thumbnail/video/%s";
		$services['dailymotion']['regexp'] = array('/^https?:\/\/(www\.)?dailymotion\.com\/video\/([^\/]*)/', 2);
		
		$services['veoh']['img'] = "http://www.veoh.com/rest/video/%s/details";
		$services['veoh']['regexp'] = array('/^https?:\/\/(www\.)?veoh\.com.*\/watch\/([^\/]*)/', 2);
		
		$services['myspace']['img'] = "http://mediaservices.myspace.com/services/rss.ashx?type=video&videoID=%s";
		$services['myspace']['regexp'] = array('/^https?:\/\/vids\.myspace\.com.*videoid=([0-9]*)/', 1);
		
		$services['metacafe']['img'] = "http://www.metacafe.com/thumb/%s.jpg";
		$services['metacafe']['regexp'] = array('/^https?:\/\/(www\.)?metacafe\.com.*\/watch\/([0-9]*)\/.*/', 2);
		
		$services['revver']['img'] = 'http://frame.revver.com/frame/320x240/%s.jpg';
		$services['revver']['regexp'] = array('/^https?:\/\/(www\.)?revver\.com\/video\/([0-9]*)\/.*/', 2);
		

		/* convert a public URL into an appropriate video tag */
		
		foreach($services as $service => $s) {
			
			
			if(preg_match($s['regexp'][0], $url, $matches, PREG_OFFSET_CAPTURE) > 0) {
				//print_r($matches);
				$match_key = $s['regexp'][1];
				$video_id = $matches[$match_key][0];
				
				if(isset($s['img'])) {
					
					if($service == 'vimeo'){
						$img_url = sprintf($s['img'], $video_id);
						$xml = simplexml_load_string(file_get_contents($img_url));
						$img_url = $xml->video->thumbnail_large;
					}elseif($service == 'veoh'){
						$img_url = sprintf($s['img'], $video_id);
						$xml = simplexml_load_string(file_get_contents($img_url));
						$img_url = $xml->video['fullMedResImagePath'];
					}elseif($service == 'myspace'){
						$img_url = sprintf($s['img'], $video_id);
						$xml = simplexml_load_string(str_replace('media:thumbnail','mediathumbnail',file_get_contents($img_url)));
						$img_url = $xml->channel->item->mediathumbnail['url'];
					}else{
						/* Video packed in an embed tag */
						$img_url = sprintf($s['img'], $video_id);
						
						
					}
					
					if($url_only == "yes"){
						$out = $img_url;
					}else{
						$out = '<img src="'.$img_url.'" />';
					}
					
				} 
			}
		}
		$tagdata = $this->EE->TMPL->tagdata;
		if ($tagdata)
		{
			foreach ($this->EE->TMPL->var_single as $key => $val)
			 {
				if ($val == "videothumb_url")
				{
					$tagdata = $this->EE->TMPL->swap_var_single($val, $img_url, $tagdata);
				}
			}
			$out = $tagdata;	
		}
		
		if(!isset($out)) {
			$out = $error;
		}
		
		return $out;
	}
	

	// --------------------------------------------------------------------
	
	/**
	 * Usage
	 *
	 * Plugin Usage
	 *
	 * @access	public
	 * @return	string
	 */
	function usage()
	{
		ob_start(); 
		?>
        
		The Video Player Thumbnail plugin lets you easily get the embed player code for Youtube, Vimeo, Dailymotion, Veoh, MySpace, Metacafe and Revver, from the video URL.

		=============================
		The Tag
		=============================

		{exp:videoplayerthumb src="http://www.youtube.com/watch?v=h1qYN3YtPfU"}


		==============
		TAG PARAMETERS
		==============

        src=
            [REQUIRED]
            The URL of the Youtube, Vimeo or Daily motion video you want to embed in the player.
            http://vimeo.com/658158  or  http://www.youtube.com/watch?v=h1qYN3YtPfU
          -------------------


        error=
            [OPTIONAL]
            the error message displayed if the video service can't be found. The default value is an empty string.
            ------------------- 

		

		
		<?php
		$buffer = ob_get_contents();

		ob_end_clean(); 

		return $buffer;
	}

	// --------------------------------------------------------------------
	
}

// END CLASS