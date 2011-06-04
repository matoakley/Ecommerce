<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Product_Option extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('product_options')
			->fields(array(
				'id' => new Field_Primary,
				'product' => new Field_BelongsTo(array(
					'on_copy' => 'copy',
				)),
				'key' => new Field_String(array(
					'on_copy' => 'copy',
				)),
				'value' => new Field_String(array(
					'on_copy' => 'copy',
				)),
				'status' => new Field_String(array(
					'on_copy' => 'copy',
				)),
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
	
	public static function add_option($product_id, $key, $value, $status)
	{
		$product_option = Jelly::factory('product_option');
		$product_option->product = $product_id;
		$product_option->key = $key;
		$product_option->value = $value;
		$product_option->status = $status;
		return $product_option->save();
	}
}