<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Product_Option extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('product_options')
			->sorting(array('key' => 'ASC', 'value' => 'DESC'))
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
				'list_order' => new Field_Integer(array(
					'on_copy' => 'copy',
				)),
				'status' => new Field_String(array(  // Legacy Field, should not be used after v1.1.3
					'on_copy' => 'copy',
				)),
				'skus' => new Field_ManyToMany(array(
          'foreign' => 'sku',
          'through' => 'product_options_skus',
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
	
	public static function add_option($product, $key, $value)
	{
		$product_option = Jelly::factory('product_option');
		$product_option->product = $product;
		$product_option->key = $key;
		$product_option->value = $value;
		$product_option->status = 'active';
		return $product_option->save();
	}
	
	public function update($data)
	{	
		$this->value = $data['value'];
		
		if (isset($data['order'])) {
  		$this->list_order = $data['order'];
		}
		
		return $this->save();
	}
	
}