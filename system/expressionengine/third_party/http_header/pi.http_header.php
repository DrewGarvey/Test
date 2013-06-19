<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* modified by Cameron to have an ability to set a cache header */
$plugin_info = array(
	'pi_name' => 'HTTP Header',
	'pi_version' => '1.0.0',
	'pi_author' => 'Rob Sanchez',
	'pi_author_url' => 'http://github.com/rsanchez',
	'pi_description' => 'Set the HTTP Headers for your template.',
	'pi_usage' => Http_header::usage()
);

class Http_header
{
    private $_contentTypeSet = FALSE;
	public $return_data = '';

	public function Http_header()
	{
		$this->EE = get_instance();

		foreach ($this->EE->TMPL->tagparams as $key => $value)
		{
			$method = 'set_'.$key;

			if (method_exists($this, $method))
			{
				$this->{$method}($value);
			}
		}

        if (!$this->_contentTypeSet) {
            $charset = $this->EE->config->config['charset'];

            switch ($this->EE->TMPL->template_type) {
            case 'js':
                $this->set_content_type("text/javascript; charset=$charset");
                break;
            case 'css':
                $this->set_content_type("text/css; charset=$charset");
                break;
            default:
                $this->set_content_type("text/html; charset=$charset");
            }
        }

		if ($this->EE->TMPL->fetch_param('terminate') === 'yes')
		{
			exit;
		}

		//this tricks the output class into NOT sending its own headers
		$this->EE->TMPL->template_type = 'cp_asset';

		return $this->EE->TMPL->tagdata;
	}

	protected function parse_path($path)
	{
		if ( ! $path)
		{
			return '';
		}

		if (strpos($path, '{site_url}') !== FALSE)
		{
			$path = str_replace('{site_url}', get_instance()->functions->fetch_site_index(1), $path);
		}

		if (strpos($path, LD.'path=') !== FALSE)
		{
			$path = preg_replace_callback('/'.LD.'path=[\042\047]?(.*?)[\042\047]?'.RD.'/', array($this->EE->functions, 'create_url'), $path);
		}

		if ( ! preg_match('#^/|[a-z]+://#', $path))
		{
			$path = get_instance()->functions->create_url($path);
		}

		return $path;
	}

	protected function set_status($code)
	{
		$this->EE->output->set_status_header($code);
	}

	protected function set_location($location)
	{
		$this->EE->output->set_header('Location: '.$this->parse_path($location));
	}

	protected function set_content_type($content_type)
	{
		$this->EE->output->set_header('Content-Type: '.$content_type);
        $this->_contentTypeSet = TRUE;
	}

	protected function set_cache_control($cache_control)
	{
		$this->EE->output->set_header('Cache-Control: '.$cache_control);
	}

	public static function usage()
	{
		ob_start();
?>
# HTTP Header #

Set the HTTP Headers for your template.

## Parameters

* status - input an HTTP Status code
* location - set a location for redirection
* content_type - set a Content-Type header
* cache_control = set a Cache-Control header
* terminate - set to "yes" to prevent any other output from the template

## Examples

Do a 301 redirect
	{exp:http_header status="302" location="{path=site/something}" terminate="yes"}

Set a 404 Status header
	{exp:http_header status="404"}

Set the Content-Type header to application/json
	{exp:http_header content_type="application/json"}

Disable caching
	{exp:http_header cache_control="no-cache"}

<?php
		$buffer = ob_get_contents();

		ob_end_clean();

		return $buffer;
	}
}
/* End of file pi.http_header.php */
/* Location: ./system/expressionengine/third_party/http_header/pi.http_header.php */
