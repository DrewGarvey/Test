<?php

$plugin_info = array(
    'pi_name' => 'Source/Subsource Tracking',
    'pi_version' =>'1.0',
    'pi_author' =>'Chris Hoffman',
    'pi_author_url' => 'http://www.bluestatedigital.com/',
    'pi_description' => 'Inserts source-tracking code',
    'pi_usage' => blue_source_tracking::usage()
);

class blue_source_tracking {
    public $return_data = "";

    function blue_source_tracking() {
        $this->return_data = "<!-- blue_source_tracking is a stub! -->";
    }

    function usage() {
	return "This version of blue_source_tracking is a stub. Don't let it get into production!";
    }
}
