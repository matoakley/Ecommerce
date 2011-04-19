<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Product_Option extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('product_options')
			->fields(array(
				'id' => new Field_Primary,
				'product' => new Field_BelongsTo,
				'key' => new Field_String,
				'value' => new Field_String,
				'status' => new Field_String,
				'created' =>  new Field_Timestamp(array(
					'auto_now_create' => TRUE,
					'format' => 'Y-m-d H:i:s',
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
	
	public static $statuses = array(
		'active',
		'disabled',
	);
}