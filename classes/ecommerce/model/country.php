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
}