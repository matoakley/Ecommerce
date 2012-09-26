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
				'customer_referral_code' => new Field_String,
				'referral_code' => new Field_Integer,
				'using_reward_points' => new Field_Float,
				'delivery_option' => new Field_BelongsTo,
				'sales_order' => new Field_BelongsTo,
				'promotion_code' => new Field_BelongsTo,
				'promotion_code_reward' => new Field_BelongsTo,
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
		else if (! isset(Model_Basket::$_instance))
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
	
	protected function calculate_weight()
	{
		if ( ! Caffeine::modules('product_weights'))
		{
			throw new Kohana_Exception('Product weights module is not enabled.');
		}
	
		$weight = 0;
		foreach ($this->items as $item)
		{
			$weight += $item->sku->weight * $item->quantity;
		}
		
		return $weight;
	}
	
	public function calculate_shipping()
	{
		return $this; // This is overriden for any special cases, e.g. FREE DELIVERY OVER £10 etc.
	}
	
	public function save($key = NULL)
	{	
		if ($this->promotion_code->loaded())
		{
			$this->promotion_code_reward = $this->promotion_code->calculate_most_suitable_reward($this);
		}
	
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
			$subtotal += round($item->sku->retail_price(), 2) * $item->quantity;
		}
		
		// Are there any special priced items to add due to promotion codes?
		if ($this->promotion_code->loaded() AND $this->promotion_code_reward->reward_type == 'item')
		{
			$subtotal += $this->promotion_code_reward->sku_reward_retail_price();
		}
		
		return $subtotal;
	}
	
	public function calculate_total()
  {
		$total = $this->calculate_subtotal();
		
		// Let's check for any promotional codes that we may need to consider
		// before we add the delivery.
		$total -= $this->calculate_discount();
		
		$total += $this->delivery_option->retail_price();
		
		return $total;
	}
	
	public function calculate_discount()
	{	
		$subtotal = $this->calculate_subtotal();
		
		$discount = 0;
		
		//if the customer is using their reward points then apply the discount
		if ($this->using_reward_points > 0)
		{
  		$discount = number_format($this->using_reward_points , 2);
		}
		
		if ($this->promotion_code->loaded())
		{
			// We should chack the promotion code conditions are still met as
			// we may have changed items in the basket.
			try
			{
				$this->promotion_code->is_valid();
			}
			catch (Kohana_Exception $e)
			{
				$this->remove_promotion_code();
				return 0;
			}

			// Is the discount based upon the sales order or a sales order item?
			if ($this->promotion_code->discount_on == 'sales_order')
			{
				switch ($this->promotion_code_reward->discount_unit)
				{
					case 'pounds':
						$discount = $this->promotion_code_reward->discount_amount;
						break;
					case 'percent':
						$discount = $subtotal * ($this->promotion_code_reward->discount_amount / 100);
						break;
				}
			}
			else
			{
				// Calculate discount based on qualifying products
				$items_on_offer = $this->promotion_code->products->as_array('id', 'id');
				$items_in_basket = array();
				foreach ($this->items as $item)
				{
					$items_in_basket[] = $item->sku->product->id;
				}
				$qualifying_basket_items = array_intersect($items_on_offer, $items_in_basket);
				
				$discount = 0;
				
				foreach ($qualifying_basket_items as $item_id)
				{
					$item = $this->get('items')
														->join('skus')
														->on('skus.id', '=', 'basket_items.sku_id')
														->where('skus.product_id', '=', $item_id)
														->limit(1)
														->execute();
				
					switch ($this->promotion_code_reward->discount_unit)
					{
						case 'pounds':
							$discount += $this->promotion_code_reward->discount_amount;
							break;
						case 'percent':
							$discount += ($item->sku->retail_price() * $item->quantity) * ($this->promotion_code_reward->discount_amount / 100);
							break;
					}
				}
			}
		}
			
		return number_format($discount, 2);
	}
	
	public function update_delivery_option($delivery_option_id)
	{
		$this->delivery_option = $delivery_option_id;
		$this->save();
		return number_format($this->delivery_option->retail_price(), 2);
	}
	
	public function add_item($sku_id, $quantity)
	{
		// If we've not created a saved basket yet then we'd best create one now!
		if ( ! $this->loaded())
		{
			$this->save();
		}
		
		// See if this product already exists as a basket item!
		$basket_item = $this->get('items')
												->where('sku_id', '=', $sku_id)
												->limit(1)->execute();
		
		if ( ! $basket_item->loaded())
		{
			$basket_item->basket_id = $this->id;
			$basket_item->sku = $sku_id;
		}
		
		// If we are removing the item altogether then we want the 
		// inverse of the current quantity
		if ($quantity === 'remove')
		{
			$quantity = ($basket_item->quantity * -1);
		}
		
		return $basket_item->update_quantity($basket_item->quantity + $quantity);
	}
	
	// Promotion code management
	public function add_promotion_code($code)
	{
		// Retrieve promotion code.
		$this->promotion_code = Model_Promotion_Code::retrieve_for_use($code);
		return $this->save();
	}
	
	public function remove_promotion_code()
	{
		$this->promotion_code = NULL;
		$this->promotion_code_reward = NULL;
		return $this->save();
	}
	
	public function create_from_sales_order($sales_order)
	{
		foreach ($sales_order->items as $item)
		{
			if ($item->sku->loaded())
			{
				$this->add_item($item->sku->id, $item->quantity);
			}
		}
		return $this;
	}
	
	public function save_reward_points_discount($reward_points_discount)
	{
	 $this->using_reward_points = $reward_points_discount;
   $this->save();
  }
  
  public function generate_unique_code($customer = NULL)
	{
	   //if the customer already has a referral code show it
	  if (! empty($customer->customer_referral_code))
  	 {
    	 $code = $customer->customer_referral_code;
     }
    //else generate a new one
	  else
	  {
  		$length = Kohana::config('ecommerce.default_customer_referral_code_length');
  		$code = Text::random('distinct', $length);
		}
		
		$this->customer_referral_code = $code;
		$this->save();
		
		return $code;
	}
}