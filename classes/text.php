<?php defined('SYSPATH') or die('No direct script access.');

class Text extends Kohana_Text
{
	/**
	 * Take a numerical representation of a month (e.g. '04') and return it as a human readable string (e.g. 'April')
	 *
	 * @param   mixed   numerical representation of month
	 * @return  string
	 */
	public static function slugify($text)
	{
		// replace non letter or digits by -
    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
 
    // trim
    $text = trim($text, '-');
 
    // transliterate
    if (function_exists('iconv'))
    {
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    }
 
    // lowercase
    $text = strtolower($text);
 
    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);
 
    if (empty($text))
    {
        return 'n-a';
    }
 
    return $text;
	}
}