<?php

/**
 * Low Variables config file
 *
 * @package        low_variables
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-variables
 * @copyright      Copyright (c) 2009-2012, Low
 */

if ( ! defined('LOW_VAR_NAME'))
{
	define('LOW_VAR_NAME',         'Low Variables');
	define('LOW_VAR_CLASS_NAME',   'Low_variables');
	define('LOW_VAR_VERSION',      '2.3.1');
	define('LOW_VAR_DEFAULT_TYPE', 'low_textarea');
	define('LOW_VAR_DOCS',         'http://gotolow.com/addons/low-variables');
}

$config['name']    = LOW_VAR_NAME;
$config['version'] = LOW_VAR_VERSION;
$config['nsm_addon_updater']['versions_xml'] = LOW_VAR_DOCS.'/feed';
