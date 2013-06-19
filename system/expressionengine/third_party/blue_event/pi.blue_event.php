<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


$plugin_info = array(
  'pi_name' => 'BSD Events RSS Parser',
  'pi_version' =>'1.2.1',
  'pi_author' =>'Lowell Kitchen',
  'pi_author_url' => 'http://www.bluestatedigital.com/',
  'pi_description' => 'Retrieves and parses a BSD events RSS feed',
  'pi_usage' => Blue_event::usage()
  );

class Blue_event{

    var $rss_url;
    var $limit;
    var $rss_obj;
    var $template;
    var $return_data = "";
    var $template_data = array();
    var $channel_elements = array("feed_title" => "title", "feed_url" => "link", "feed_description" => "description");
    var $item_elements = array("title" => "title", "link" => "link", "description" => "description", "pubDate" => "pubDate","eventtype" => "eventType", "abstract" => "abstract", "rsvp_url" => "rsvp", "host" => "host", "venue_name" => "venue_name", "attendee_count" => "attendee_count", "guest_total" => "guest_total", "street" => "street", "city" => "city", "state" => "state", "country" => "country", "zip" => "zipcode", "county" => "county", "datetime" => "dateTime", "length" => "length");

    function Blue_event(){
        //global $TMPL, $FNS;
        $this->EE =& get_instance();
       // $this->rss_url = (!$this->EE->TMPL->fetch_param('url')) ? '' : str_replace('&#47;', '/',trim($TMPL->fetch_param('url')));
        $this->rss_url = $this->EE->TMPL->fetch_param('url');

        $this->rss_obj = @simplexml_load_file($this->rss_url);

        if(!$this->rss_obj instanceof SimpleXMLElement){
            return;
        }

        $this->template = $this->EE->TMPL->tagdata;
        $this->limit = sprintf('%d',$this->EE->TMPL->fetch_param('limit'));

        // process all the channel elements
        foreach($this->EE->TMPL->var_single AS $key => $val){
            @$channel_el = $this->channel_elements[$key];
            if($channel_el){
                    $this->template = $this->EE->TMPL->swap_var_single($val,$this->rss_obj->channel->$channel_el, $this->template);
            }
        }

        // process all the item elements
        if(preg_match("/".LD."items".RD."(.*?)".LD.'\/items'.RD."/s", $this->EE->TMPL->tagdata, $matches)){

            if(preg_match_all("/".LD."(.*?)".RD."/", $matches[1], $item_matches)){
                $template_str = "";
                $template_array = array();
                $counter = 0;
                if($this->rss_obj && $this->rss_obj->channel && $this->rss_obj->channel->children()){
                    foreach($this->rss_obj->channel->children()->item AS $item){
                        $counter++;
                        $template_str = $matches[1];
                        $this->template_data = array();

                        // prepare the data
                        foreach($item_matches[1] AS $item_str){
                            $item_str_ary = explode(" ", $item_str);
                            if($item_str_ary[0]){
                                $item_str = $item_str_ary[0];
                                $full_item_str = implode(" ",$item_str_ary);
                            } else{
                                $item_str = NULL;
                                $full_item_str = NULL;
                            }

                            @$item_el = $this->item_elements[$item_str];
                            $item_val = $item->$item_el;

                            if(!$item_val){
                                $item_xml = $item->asXML();
                                $item_xml = "<rss xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:db=\"http://www.w3.org\" version=\"2.0\">".$item_xml."</rss>";
                                $doc = simplexml_load_string($item_xml, NULL, LIBXML_NOCDATA);
                                $doc->registerXPathNamespace('db','http://www.w3.org');
                                @$result = $doc->xpath("//db:$item_el");
                                $item_val = (!empty($result) && $result[0][0]) ? $result[0][0] : "";
                                $item_el = $full_item_str;
                            }


                            if (stristr($item_el, 'if') === false)
                                    $this->template_data[$item_el] = (string)$item_val;

                        }

                        // process conditionals
                        $template_str = $this->EE->functions->prep_conditionals($template_str, $this->template_data);

                        // process variables/data
                        foreach($this->template_data AS $key => $val){
                            $date_vars = $this->EE->functions->fetch_date_variables($key);
                            if(stripos($key,"exp:") === FALSE){ // make sure we don't parse any nested exp tags
                                if(!empty($date_vars)){
                                    $date_vars = str_replace('%','',$date_vars);
                                    $date_key = explode(" ", $key);

                                    if($date_key[0]){
                                        $date_key = $date_key[0];
                                    }

                                    $date_val = date($date_vars,strtotime($val));
                                    $template_str = $this->EE->TMPL->swap_var_single("{$key}",$date_val, $template_str);
                                } else{
                                    $template_str = $this->EE->TMPL->swap_var_single("{$key}",$val, $template_str);
                                }
                            }
                        }

                        $template_array[] = $template_str;

                        // stop if we've hit the limit
                        if($counter == $this->limit) { break; }
                    }
                }
                $this->template = str_replace($matches[0],implode("\n",$template_array), $this->template);
                unset($template_array, $this->template_data);
            }
        }

        $this->return_data = &$this->template;
    }


    function usage(){
	ob_start();
?>

STEP ONE:
Insert plugin tag into your template.  Set parameters and variables.

PARAMETERS:
The tag has two parameters:

1. url - The URL of the BSD events RSS feed.
2. limit - Number of items to display from feed.


Example opening tag:  {exp:blue_event url="http://www.pmachine.com/news.rss" limit="8"}

SINGLE VARIABLES:

feed_title - The title of the feed pulled from the channel element.
feed_url - The URL of the feed pulled from the channel element.
feed_description - The description of the feed pulled from the channel element.

PAIR VARIABLES:

Only one pair variable, {items}, is available, and it is for the entries/items in the RSS feed. This pair
variable allows many different other single variables to be contained within it.  The following single variables
are available inside the {items}{/items} tag:

<?php

        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }

}
/* End of file pi.blue_event.php */
/* Location: /system/expressionengine/third_party/blue_event/pi.blue_event.php */