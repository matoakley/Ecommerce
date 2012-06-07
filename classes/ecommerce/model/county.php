<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_County extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->sorting(array('name' => 'ASC'))
			->fields(array(
				'id' => new Field_Primary,
				'name' => new Field_String,
				'country' => new Field_String,
			));
	}
}