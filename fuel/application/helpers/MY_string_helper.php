<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * FUEL CMS
 * http://www.getfuelcms.com
 *
 * An open source Content Management System based on the 
 * Codeigniter framework (http://codeigniter.com)
 *
 * @package		FUEL CMS
 * @author		David McReynolds @ Daylight Studio
 * @copyright	Copyright (c) 2010, Run for Daylight LLC.
 * @license		http://www.getfuelcms.com/user_guide/general/license
 * @link		http://www.getfuelcms.com
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * FUEL String Helper
 *
 * This helper extends CI's string helper
 * 
 *
 * @package		FUEL CMS
 * @subpackage	Helpers
 * @category	Helpers
 * @author		David McReynolds @ Daylight Studio
 * @link		http://www.getfuelcms.com/user_guide/helpers/string_helper
 */

// --------------------------------------------------------------------

/**
 * Evaluates a strings PHP code. Used especially for outputing FUEL page data
 *
 * @param 	string 	string to evaluate
 * @param 	mixed 	variables to pass to the string
 * @return	string
 */
function eval_string($str, $vars = array())
{
	$CI =& get_instance();
	extract($CI->load->_ci_cached_vars); // extract cached variables
	extract($vars);

	// fix XML
	$str = str_replace('<?xml', '<@xml', $str);

	ob_start();
	if ((bool) @ini_get('short_open_tag') === FALSE AND $CI->config->item('rewrite_short_tags') == TRUE)
	{
		$str = eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', $str)).'<?php ');
	}
	else
	{
		$str = eval('?>'.$str.'<?php ');
	}
	$str = ob_get_clean();
	
	// change XML back
	$str = str_replace('<@xml', '<?xml', $str);
	return $str;
}

// --------------------------------------------------------------------

/**
 * Get translated local strings with arguments
 *
 * @param 	string
 * @param 	mixed
 * @return	string
 */
// 
function lang($key, $args = NULL)
{
	// must test for this first because we may load a config 
	// file that uses this function before get_instance is loaded
	if (function_exists('get_instance'))
	{
		$CI =& get_instance();
		if (!is_array($args))
		{
			$args = func_get_args();
			$args[0] = $CI->lang->line($key);
		}
		return call_user_func_array('sprintf', $args);
	}
}

// --------------------------------------------------------------------

/**
 * Add an s to the end of s string based on the number 
 *
 * @param 	int 	number to compare against to determine if it needs to be plural
 * @param 	string 	string to evaluate
 * @param 	string 	plural value to add
 * @return	string
 */
// 
function pluralize($num, $str = '', $plural = 's')
{
	if ($num != 1)
	{
		$str .= $plural;
	}
	return $str;
}

// --------------------------------------------------------------------

/**
 * Strips extra whitespace from a string
 *
 * @param 	string
 * @return	string
 */
function strip_whitespace($str)
{
	return trim(preg_replace('/\s\s+|\n/m', '', $str));
}

// --------------------------------------------------------------------

/**
 * Converts words to title case and allows for exceptions
 *
 * @param 	string 	string to evaluate
 * @param 	mixed 	variables to pass to the string
 * @return	string
 */
function smart_ucwords($str, $exceptions = array('of', 'the'))
{
	$out = "";
	$i = 0;
	foreach (explode(" ", $str) as $word)
	{
		$out .= (!in_array($word, $exceptions) OR $i == 0) ? strtoupper($word{0}) . substr($word, 1) . " " : $word . " ";
		$i++;
	}
	return rtrim($out);
}

// --------------------------------------------------------------------

/**
 * Safely converts a string's entities without encoding HTML tags and quotes
 *
 * @param 	string 	string to evaluate
 * @return	string
 */
function safe_htmlentities($str)
{
	// setup temp markers for existing encoded tag brackets existing 
	$find = array('&lt;','&gt;');
	$replace = array('__TEMP_LT__','__TEMP_GT__');
	$str = str_replace($find,$replace, $str);

	// safely translate now
	$str = htmlentities($str, ENT_NOQUOTES, 'UTF-8', FALSE);
	
	// translate everything back
	$str = str_replace($find, array('<','>'), $str);
	$str = str_replace($replace, $find, $str);
	return $str;
}

// --------------------------------------------------------------------

/**
 * Convert PHP syntax to Dwoo templating syntax
 *
 * @param 	string 	string to evaluate
 * @return	string
 */
function php_to_template_syntax($str)
{
	// order matters!!!
	$find = array('<?php endforeach', '<?php endif', '<!--', '-->', '<?php echo ', '<?php ', '<?=', );
	$replace = array('{/foreach', '{/if', '{*', '*}', '{', '{', '{');

	// close ending php
	$str = preg_replace('#([:|;])?\s*\?>#U', '}$3', $str);

	
	$str = str_replace($find, $replace, $str);

	// foreach cleanup
	$str = preg_replace('#{\s*foreach\s*\((\$\w+)\s+as\s+\$(\w+)\s*(=>\s*\$(\w+))?\)\s*}#U', '{foreach $1 $2 $4}', $str); // with and without keys

	// remove !empty
	$callback = create_function('$matches', '
		if (!empty($matches[2]))
		{
			return "{".$matches[1].$matches[3];
		}
		else
		{
			return "{".$matches[1]."!".$matches[3];
		}');
	
	$str = preg_replace_callback('#{(.+)(!)?empty\((.+)\)#U', $callback, $str);

	// fix arrays
	$callback = create_function('$matches', '
		return $matches[1].str_replace("=>", "=", $matches[2]).$matches[3];
		');
	
	$str = preg_replace_callback('#(array\()(.+)(\))#', $callback, $str);
	return $str;
}

/* End of file MY_string_helper.php */
/* Location: ./application/helpers/MY_string_helper.php */