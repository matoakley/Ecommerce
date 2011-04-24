<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Basket_Item extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('basket_items')
			->fields(array(
				'id' => new Field_Primary,
				'basket_id' => new Field_Integer,
				'product' => new Field_BelongsTo,
				'product_options' => new Field_Serialized,
				'quantity' => new Field_Integer,
				'created' => new Field_Timestamp(array(
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
	
	public function update_quantity($quantity)
	{
		if ($quantity <= 0)
		{
			$this->delete();
			return 0;
		}
		else
		{
			return $this->set(array(
				'quantity' => $quantity,
			))->save();
		}
	}
}