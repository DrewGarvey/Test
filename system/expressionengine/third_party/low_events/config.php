<?php

/**
 * Low Events config file
 *
 * @package        low_events
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-events
 * @copyright      Copyright (c) 2012, Low
 */

if ( ! defined('LOW_EVENTS_NAME'))
{
	define('LOW_EVENTS_NAME',    'Low Events');
	define('LOW_EVENTS_PACKAGE', 'low_events');
	define('LOW_EVENTS_VERSION', '1.0.3');
	define('LOW_EVENTS_DOCS',    'http://gotolow.com/addons/low-events');
	define('LOW_EVENTS_DEBUG',   FALSE);
}

$config['name']    = LOW_EVENTS_NAME;
$config['version'] = LOW_EVENTS_VERSION;

$config['nsm_addon_updater']['versions_xml'] = LOW_EVENTS_DOCS.'/feed';
