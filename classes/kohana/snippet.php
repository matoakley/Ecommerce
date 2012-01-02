<?php defined('SYSPATH') or die('No direct script access.');

/**
 *  Snippet helper.
 *
 * @author     Matt Oakley http://creativeintent.co.uk/
 */
class Kohana_Snippet
{
	protected static $_instance;
	
	public static function instance()
	{
		if ( ! isset(Snippet::$_instance))
		{
			Snippet::$_instance = new Snippet();
		}
		
		return Snippet::$_instance;
	}
	
	public function get($snippet_name)
	{	
		$snippet = Model_Snippet::show($snippet_name);
		return $snippet->loaded() ? $snippet->content : '';
	}
}