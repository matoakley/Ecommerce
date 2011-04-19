<?php defined('SYSPATH') or die('No direct script access.');

class Date extends Kohana_Date
{
	/**
	 * Take a numerical representation of a month (e.g. '04') and return it as a human readable string (e.g. 'April')
	 *
	 * @param   mixed   numerical representation of month
	 * @return  string
	 */
	public static function month2string($month)
	{
		 return date('F', mktime(0, 0, 0, $month, 1, date('Y')));
	}
}