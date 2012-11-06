<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Provide helper methods at the very top level of the CaffeineApps stack.
 *
 * @package    CaffeineApps
 * @author     Matt Oakley
 * @copyright  (c) 2010 - 2012 Creative Intent Ltd
 */
abstract class Caffeine_Core
{
	// Property to denote if request is via domain for ecommerce trade area
	public static $is_trade = FALSE;

	/**
	 * Provides either a boolean response to whether or not the given module
	 * is enabled or if no module is specifed, it will return a list of all 
	 * modules and their status.
	 *
	 * @param   string  module name to check
	 * @return  mixed
	 */
	public static function modules($module = NULL)
	{
		if ($module)
		{
			return Kohana::config('ecommerce.modules.'.$module);	
		}
		else
		{
			return Kohana::config('ecommerce.modules');
		}
	}
	
	/**
	 * Shortcut method for accessing config. If the config key is set then 
	 * value is returned otherwise returns NULL.
	 *
	 * @param   string  config key to get
	 * @return  mixed
	 */
	public static function config($path = NULL)
	{
	  $config = Kohana::config('ecommerce'); 
  	return Arr::path($config, $path);
	}
	
	/**
	 * In order to bust the JS cache on production sites, we merge the 
	 * software version for the module and the js_buster application value
	 * to create a unique version number to append within RequireJS.
	 *
	 * @return  string
	 */
	public static function js_buster()
	{
  	return self::config('software_version').'.'.self::config('js_buster');
	}
}