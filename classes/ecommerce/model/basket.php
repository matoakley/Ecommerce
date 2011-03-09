<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Basket extends Model_Application
{	
	public static function initialize(Jelly_Meta $meta)
  {
  	$meta->table('baskets')
    	->fields(array(
      	'id' => new Field_Primary,
				'items' => new Field_HasMany(array(
					'foreign' => 'basket_item.basket_id',
				)),
				'delivery_option' => new Field_BelongsTo,
				'sales_order' => new Field_BelongsTo,
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

	protected static $_instance;
	
	public static function instance()
	{		
		if ( ! isset(Model_Basket::$_instance) AND Session::instance()->get('basket_id'))
		{
			// Create a new session instance
			Model_Basket::$_instance = Jelly::select('basket', Session::instance()->get('basket_id'));
		}
		else 
		{
			Model_Basket::$_instance = new Model_Basket();
		}
		
		return Model_Basket::$_instance;
	}
	
	public function __construct($values = array())
	{
		parent::__construct($values);
		
		$this->delivery_option = Kohana::config('ecommerce.default_delivery_option');
	}
	
	public function save($key = NULL)
	{	
		parent::save($key);
		
		if ( ! Session::instance()->get('basket_id'))
		{
			Session::instance()->set('basket_id', $this->id);
		}
	}
	
	/**
	* A fairly simple function that returns the total number of items in the basket.
	**/
	public function count_items()
	{
		$count = 0;
		
		foreach ($this->items as $item)
		{
			$count += $item->quantity;
		}
	
		return $count;
	}
	
	public function calculate_subtotal()
	{
		$subtotal = 0;
		
		foreach ($this->items as $item)
		{
			$subtotal += $item->product->retail_price() * $item->quantity;
		}
		
		return number_format($subtotal, 2);
	}
	
	public function calculate_total()
    {
		$total = $this->calculate_subtotal();
		
		$total += $this->delivery_option->retail_price();
		
		return number_format($total, 2);
	}
	
	public function update_delivery_option($delivery_option_id)
	{
		$this->delivery_option = $delivery_option_id;
		$this->save();
		return $this->delivery_option->retail_price();
	}
	
	public function add_item($product_id, $quantity)
	{
		// If we've not created a saved basket yet then we'd best create one now!
		if ( ! $this->loaded())
		{
			$this->save();
		}
		
		// See if this product already exists as a basket item!
		$basket_item = $this->get('items')->where('product_id', '=', $product_id)->limit(1)->execute();
		
		if ( ! $basket_item->loaded())
		{
			$basket_item->basket_id = $this->id;
			$basket_item->product = $product_id;
		}
		
		return $basket_item->update_quantity($basket_item->quantity + $quantity);
	}
	
}