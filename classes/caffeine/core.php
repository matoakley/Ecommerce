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
}