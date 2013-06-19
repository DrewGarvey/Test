<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Videoplayer Class
 *
 * @package			ExpressionEngine
 * @category		Plugin
 * @author			Benjamin David
 * @copyright		Copyright (c) Dukt
 * @link			http://dukt.fr/
 */

$plugin_info = array(
    'pi_name'        => 'Video Player',
    'pi_version'     => '1.4',
    'pi_author'      => 'Benjamin David',
    'pi_author_url'  => 'http://dukt.fr/',
    'pi_description' => 'Get the embed player code for Youtube, Vimeo, Dailymotion, Veoh, MySpace, Metacafe and Revver videos',
    'pi_usage'       => Videoplayer::usage()
);

class Videoplayer {
	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	void
	 */
	function Videoplayer()
	{
		$this->EE =& get_instance();
		
		$this->video = $this->get_video();
		$video = $this->video;
		$out = $video['embed_code'];
				
		if($video['error']) {
			$out = $video['error'];
		}
		
		$this->return_data = $out;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Details
	 *
	 * @access	public
	 * @return	tagdata
	 */
	
	function details() {
	
		$video = $this->video;
		$tagdata = $this->EE->TMPL->tagdata;
		$tagdata = $this->EE->functions->prep_conditionals($tagdata, $this->EE->TMPL->var_single);

		if ($tagdata) {
			foreach ($this->EE->TMPL->var_single as $key => $val)
			 {

				if ($val == "video_id") {
					$tagdata = $this->EE->TMPL->swap_var_single($val, $video['video_id'], $tagdata);
				}
				
				/* deprecated since v1.3 */
				if ($val == "service") {
					$tagdata = $this->EE->TMPL->swap_var_single($val, $video['service_key'], $tagdata);
				}
				
				if ($val == "service_key") {
					$tagdata = $this->EE->TMPL->swap_var_single($val, $video['service_key'], $tagdata);
				}
				
				if ($val == "service_name") {
					$tagdata = $this->EE->TMPL->swap_var_single($val, $video['service_name'], $tagdata);
				}
				
				if ($val == "embed") {
					$tagdata = $this->EE->TMPL->swap_var_single($val, $video['embed_code'], $tagdata);
				}
				
				if ($val == "error") {
					$tagdata = $this->EE->TMPL->swap_var_single($val, $video['error'], $tagdata);
				}
			}
			return $tagdata;	
		}
		
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Get_video
	 *
	 * @access	private
	 * @return	Video Array
	 */
	
	private function get_video() {
	
		$this->parameters();

		$video = array(
			'video_id' => false,
			'service' => false,
			'service_key' => false,
			'service_name' => false,
			'embed' => false,
			'script' => false,
			'embed_code' => false,
			'error' => $this->error
		);
		
		
		
		foreach($this->services as $service => $s) {			
			
			/* Is there a service for this video */
			if(preg_match($s['regexp'][0], $this->url, $matches, PREG_OFFSET_CAPTURE) > 0) {
			
				$match_key = $s['regexp'][1];
				$video_id = $matches[$match_key][0];
				
				$video['video_id'] = $video_id;
				$video['service'] = $service;
				$video['service_key'] = $service;
				$video['service_name'] = $s['name'];
				
				
				if(isset($s['embed'])) {
					$video['embed'] = $s['embed'];
					
					/* Video packed in an embed tag */
					$video_url = sprintf($video['embed'], $video['video_id']);
					
					switch($video['service_key']) {
						case "vimeo":
						$video['embed_code'] = '<iframe src="http://player.vimeo.com/video/'.$video['video_id'].'" width="'.$this->width.'" height="'.$this->height.'" frameborder="0"></iframe>';
						break;
						
						default:
						$video['embed_code'] = '<object type="application/x-shockwave-flash" style="width: '.$this->width.'px; height: '.$this->height.'px;" data="'.$video_url.'"><param name="movie" value="'.$video_url.'"></param><param name="wmode" value="transparent"></param></object>';
					}
				}
				
				if(isset($s['script'])) {
					$video['script'] = $s['script'];
					$video['embed_code'] = sprintf($video['script'], $video['video_id'], $this->width, $this->height);
				}
				
				return $video;
			}
		}
		
		return $video;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Parameters
	 *
	 * @access	private
	 * @return	void
	 */
	
	private function parameters() {
	
		/* set up plugin parameters */
		$this->url = $this->EE->TMPL->fetch_param('url');
		$this->src = $this->EE->TMPL->fetch_param('src'); // deprecated since v1.2
		$this->width = $this->EE->TMPL->fetch_param('width');
		$this->height = $this->EE->TMPL->fetch_param('height');
		$this->error = $this->EE->TMPL->fetch_param('error');
		
		if(!$this->url && $this->src) {
			$this->url = $this->src;
		}
		
		$this->url = trim($this->url);
		
		/* check if slashes are encoded and decodes html if so */
		if(preg_match("/&#47;/", $this->url)) {
			$this->url = html_entity_decode($this->url);
		}
		
		/* default width and height values */
		if($this->width=='') {
			$this->width = 425;
		}
		if($this->height=='') {
			$this->height = 344;
		}

		$this->set_services();
		
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set_services
	 *
	 * @access	private
	 * @return	void
	 */
	private function set_services() {
		/* 
			Configuration of each video service :
		
			- regexp : regular expression for identifying the video service and extracting the video ID
			- embed : pattern of the video URL to call from an embed tag (%s : video id)
			- script : javascript tag for services that don't use an embed tag (%d : video id, %d : width, %d : height)
			
		*/
		
		$this->services = array();
		
		$this->services['youtube']['name'] = "YouTube";
		$this->services['youtube']['embed'] = "http://www.youtube.com/v/%s";
		$this->services['youtube']['regexp'] = array('/^https?:\/\/(www\.)?youtube\.com.*\/watch\?v=(.*)/', 2);
		
		
		$this->services['vimeo']['name'] = "Vimeo";
		$this->services['vimeo']['embed'] = "http://vimeo.com/moogaloop.swf?clip_id=%s&amp;server=vimeo.com";
		$this->services['vimeo']['regexp'] = array('/^https?:\/\/(www\.)?vimeo\.com\/([0-9]*)/', 2);
		
		$this->services['dailymotion']['name'] = "Dailymotion";
		$this->services['dailymotion']['embed'] = "http://www.dailymotion.com/swf/%s";
		$this->services['dailymotion']['regexp'] = array('/^https?:\/\/(www\.)?dailymotion\.com\/video\/([^\/]*)/', 2);
		
		$this->services['veoh']['name'] = "Veoh";
		$this->services['veoh']['embed'] = "http://www.veoh.com/static/swf/webplayer/WebPlayer.swf?&permalinkId=%s&videoAutoPlay=0";
		$this->services['veoh']['regexp'] = array('/^https?:\/\/(www\.)?veoh\.com.*\/watch\/([^\/]*)/', 2);
		
		$this->services['myspace']['name'] = "MySpace";
		$this->services['myspace']['embed'] = "http://mediaservices.myspace.com/services/media/embed.aspx/m=%s,t=1,mt=video";
		$this->services['myspace']['regexp'] = array('/^https?:\/\/vids\.myspace\.com.*videoid=([0-9]*)/', 1);
		
		$this->services['metacafe']['name'] = "Metacafe";
		$this->services['metacafe']['embed'] = "http://www.metacafe.com/fplayer/%s.swf";
		$this->services['metacafe']['regexp'] = array('/^https?:\/\/(www\.)?metacafe\.com.*\/watch\/([0-9]*)\/.*/', 2);
		
		$this->services['revver']['name'] = "Revver";
		$this->services['revver']['script'] = '<script src="http://flash.revver.com/player/1.0/player.js?mediaId:%d;width:%d;height:%d;" type="text/javascript"></script>';
		$this->services['revver']['regexp'] = array('/^https?:\/\/(www\.)?revver\.com\/video\/([0-9]*)\/.*/', 2);
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
        
		The Video Player plugin lets you easily get the embed player code for Youtube, Vimeo, Dailymotion, Veoh, MySpace, Metacafe and Revver, from the video URL.
		
		
		================================
		VideoPlayer : Returns the video embed
		================================
		
		{exp:videoplayer src="http://www.youtube.com/watch?v=h1qYN3YtPfU" width="800" height="600"}


		TAG PARAMETERS
		================
        src=
            [REQUIRED]
            The URL of the Youtube, Vimeo, Dailymotion, Veoh, MySpace, Metacafe or Revver video you want to embed in the player.
            http://vimeo.com/658158  or  http://www.youtube.com/watch?v=h1qYN3YtPfU
          ----------------------------------------------------------------------------
        width= 
            [OPTIONAL]
            the height of the player. The default value is (425).
            --------------------------------------------------------------------------
        height=
            [OPTIONAL]
            the width of the player. The default value is (344).
            --------------------------------------------------------------------------
        error=
            [OPTIONAL]
            the error message displayed if the video service can't be found. The default value is an empty string.
            --------------------------------------------------------------------------
           
            
		================================
		VideoPlayer : Details
		================================
		
		{exp:videoplayer:details src="http://www.youtube.com/watch?v=h1qYN3YtPfU"}
			{video_id}
			{embed}
			{service_name}
			{error}
		{/exp:videoplayer:details}


		TAG Variables
		================
        * video_id : The id of the video
        * embed : The embed code to let you display the video
        * service_key : The key of the video service used (ex : youtube)
        * service_name : The name of the video service used (ex : YouTube)
        * error : Returns the error if you set it in the tag parameters
		
		TAG PARAMETERS
		================
        Takes the same parameters as the first function
		
		
		<?php
		$buffer = ob_get_contents();
		ob_end_clean(); 
		return $buffer;
	}
	// --------------------------------------------------------------------
	
}
// END Videoplayer Class
/* End of file  pi.videoplayer.php */
/* Location: ./system/expressionengine/third_party/videoplayer/pi.videoplayer.php */
