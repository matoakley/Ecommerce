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
				'discount_amount' => new Field_String,
				'product_options' => new Field_Serialized,  // Legacy Field, should not be used after v1.1.3
				'quantity' => new Field_Integer,
				'unit_price' => new Field_Float(array(
					'places' => 4,
				)),
				'total_price' => new Field_Float(array(
					'places' => 4,
				)),
				'net_unit_price' => new Field_Float(array(
					'places' => 4,
				)),
				'net_total_price' => new Field_Float(array(
					'places' => 4,
				)),
				'vat_rate' => new Field_Float(array(
					'places' => 4,
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
		$item->net_unit_price = $basket_item->sku->price;
		$item->unit_price = $basket_item->sku->retail_price();
		$item->vat_rate = $basket_item->sku->vat_rate();
		$item->net_total_price = $basket_item->sku->price * $basket_item->quantity;
		$item->total_price = $basket_item->sku->retail_price() * $basket_item->quantity;
		
		$basket = $sales_order->basket;

		if ($sales_order->basket->promotion_code_reward->loaded() AND $sales_order->basket->promotion_code_reward->reward_type == 'discount')
		{
		  $reward = $sales_order->basket->promotion_code_reward;
		  
		  // Does the promotion code affect this item
		  if ($reward->promotion_code->discount_on == 'sales_order')
  	  {
  	  	if ($reward->discount_unit == 'pounds')
  	  	{
  	  		$qty = 0;
  	  		foreach ($basket->items as $all_item)
  	  		{
	  	  		$qty += $all_item->quantity;
  	  		}
  	  	
  	  		$item->discount_amount = ($sales_order->discount_amount / $qty) * $item->quantity;
  	  	}
  	  	else
  	  	{
	  	  	$item->discount_amount = $item->total_price / $reward->discount_amount;
  	  	}
  	  }
  	  else
  	  {
  	  	// If the sales order item discount relates to this line...
  		  if ($sales_order->basket->promotion_code->get('products')->where('id', '=', $basket_item->sku->product->id)->count())
  		  {
  		  	if ($reward->discount_unit == 'pounds')
  		  	{
	  		  	$item->discount_amount = $sales_order->discount_amount;
  		  	}
    		  else
    		  {
	    		  $item->discount_amount = $item->total_price / $reward->discount_amount;
    		  }
  		  }
  		}
    }
		
		return $item->save();
	}
	
	public static function create_commercial_sales_order_item($sales_order, $sku)
	{
		$item = Jelly::factory('sales_order_item');

		$sku_object = Model_Sku::load($sku['id']);
		
		$item->sales_order = $sales_order;
		$item->sku = $sku_object;
		$item->product_name = $sku_object->name();
		$item->quantity = $sku['quantity'];
		$item->net_unit_price = $sku['price'] ;
		$item->net_total_price = $sku['price'] * $sku['quantity'];
		$item->vat_rate = $sku_object->vat_rate();
		$item->unit_price = $item->net_unit_price * (($item->vat_rate + 100) / 100);
		$item->total_price = $item->net_total_price * (($item->vat_rate + 100) / 100);
		
		return $item->save();
		
	}
	
	
	public static function create_from_promotion_code_reward($sales_order, $promotion_code_reward)
	{
		$item = Jelly::factory('sales_order_item');
		
		$item->sales_order = $sales_order;
		$item->sku = $promotion_code_reward->sku;
		
		$item->product_name = 'Promotional Item: '.$promotion_code_reward->sku->name();
		$item->quantity = 1;
		$item->unit_price = $promotion_code_reward->sku_reward_retail_price();
		$item->vat_rate = Kohana::config('ecommerce.vat_rate');
		$item->total_price = $promotion_code_reward->sku_reward_retail_price(); 
		
		
		return $item->save();
	}
	
	public function vat()
	{
		return $this->total_price - $this->net_total_price;
	}
}