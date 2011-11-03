<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Sales_Order_Item extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('sales_order_items')
			->fields(array(
				'id' => new Field_Primary,
				'sales_order' => new Field_BelongsTo(array(
					'foreign' => 'sales_order.id',
					'column' => 'sales_order_id',
				)),
				'sku' => new Field_BelongsTo(array(
					'foreign' => 'sku.id',
				)),
				'product' => new Field_BelongsTo(array(  // Legacy Field, should not be used after v1.1.3
					'foreign' => 'product.id',
					'column' => 'product_id',
				)),
				'product_name' => new Field_String,
				'product_options' => new Field_Serialized,  // Legacy Field, should not be used after v1.1.3
				'quantity' => new Field_Integer,
				'unit_price' => new Field_Float(array(
					'places' => 2,
				)),
				'total_price' => new Field_Float(array(
					'places' => 2,
				)),
				'vat_rate' => new Field_Float(array(
					'places' => 2,
				)),
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

	public static function create_from_basket($sales_order, $basket_item)
	{
		$item = Jelly::factory('sales_order_item');
		
		$item->sales_order = $sales_order;
		$item->sku = $basket_item->sku;
		
		// Build product name including product options added onto end.
		$product_name = $basket_item->sku->product->name;
		if (count($basket_item->sku->product_options) > 0)
		{
			$product_name .= ' (';
			$i = 0;
			foreach ($basket_item->sku->product_options as $option)
			{
				if ($i++ > 0)
				{
					$product_name .= ', ';
				}
				$product_name .= ucwords($option->key) . ': "' . $option->value . '"';
			}
			$product_name .= ')';
		}
		$item->product_name = $product_name;
		
		$item->quantity = $basket_item->quantity;
		$item->unit_price = $basket_item->sku->retail_price();
		$item->vat_rate = Kohana::config('ecommerce.vat_rate');
		$item->total_price = $basket_item->sku->retail_price() * $basket_item->quantity;
		
		return $item->save();
	}
}