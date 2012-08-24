<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Country extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('countries')
			->fields(array(
				'id' => new Field_Primary,
				'name' => new Field_String,
				'iso_code' => new Field_Integer,
				'iso_3_code' => new Field_String,
				'created' =>  new Field_Timestamp(array(
					'auto_now_create' => TRUE,
					'format' => 'Y-m-d H:i:s',
					'pretty_format' => 'd/m/Y H:i',
				)),
				'modified' => new Field_Timestamp(array(
					'auto_now_update' => TRUE,
					'format' => 'Y-m-d H:i:s',
				)),
				'deleted' => new Field_Timestamp(array(
					'format' => 'Y-m-d H:i:s',
				)),
			));
	}
	
	// This should only return countries which are set as active
	// within the system, however there is no management for 
	// this yet and so it is simply a placeholder at present
	public static function list_active()
	{
		return Jelly::select('country')->order_by('name', 'ASC')->execute();
	}
}