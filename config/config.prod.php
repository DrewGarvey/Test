<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Production config overrides & db credentials
 * 
 * Our database credentials and any environment-specific overrides
 * 
 * @package    Focus Lab Master Config
 * @version    1.1.1
 * @author     Focus Lab, LLC <dev@focuslabllc.com>
 */

// DB settings if you need to specify creds

$env_db['hostname'] = '';
$env_db['username'] = '';
$env_db['password'] = '';
$env_db['database'] = '';

// PHPfog uses environmental variables
// http://docs.phpfog.com/getting-started/env-vars/

$env_db['hostname'] = getenv('MYSQL_DB_HOST');
$env_db['username'] = getenv('MYSQL_USERNAME');
$env_db['password'] = getenv('MYSQL_PASSWORD');
$env_db['database'] = getenv('MYSQL_DB_NAME');


// Sample global variable for Production only
// Can be used in templates like "{global:google_analytics}"
$env_global['global:google_analytics'] = 'UA-XXXXXXX-XX';

/* End of file config.prod.php */
/* Location: ./config/config.prod.php */