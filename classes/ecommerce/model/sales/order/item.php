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
				'product' => new Field_BelongsTo(array(
				    'foreign' => 'product.id',
	                'column' => 'product_id',
				)),
				'quantity' => new Field_Integer,
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
		$item->product = $basket_item->product;
		$item->quantity = $basket_item->quantity;
		$item->vat_rate = Kohana::config('ecommerce.vat_rate');
		$item->total_price = $basket_item->product->retail_price() * $basket_item->quantity;
		
		return $item->save();
	}
}