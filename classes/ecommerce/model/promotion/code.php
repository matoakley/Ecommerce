<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Promotion_Code extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('promotion_codes')
			->fields(array(
				'id' => new Field_Primary,
				'code' => new Field_String(array(
					'rules' => array(
						'not_empty' => NULL,
						'min_length' => array(4),
						'max_length' => array(20),
					),
				)),
				'description' => new Field_String,
				'max_redemptions' => new Field_Integer,
				'redeemed' => new Field_Integer(array(
					'default' => 0,
				)),
				'run_indefinitely' => new Field_Boolean(array(
					'default' => TRUE,
				)),
				'start_date' => new Field_Timestamp(array(
					'format' => 'Y-m-d H:i',
				)),
				'end_date' => new Field_Timestamp(array(
					'format' => 'Y-m-d H:i',
				)),
				'basket_minimum_value' => new Field_Float(array(
					'places' => 4,
				)),
				'discount_on' => new Field_String,
				'discount_amount' => new Field_Float(array(
					'places' => 4,
				)),
				'discount_unit' => new Field_String(array(
					'default' => 'pounds',
				)),
				'products' => new Field_ManyToMany,
				'status' => new Field_String,
				'rewards' => new Field_HasMany(array(
					'foreign' => 'promotion_code_reward.promotion_code_id',
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
		'active', 'disabled',
	);
	
	public static $types = array(
		'sales_order',
		'sales_order_item',
	);
	
	public static function generate_unique_code()
	{
		$length = Kohana::config('ecommerce.default_promotion_code_length');
	
		$code = FALSE;
		
		while ( ! $code OR Jelly::select('promotion_code')->where('code', '=', $code)->count() > 0)
		{
			$code = Text::random('distinct', $length);
		}
	
		return $code;
	}
	
	// This function checks that the given code exists and is valid.
	public static function retrieve_for_use($code)
	{
		$code = strtoupper($code);
	
		$promotion_code = Jelly::select('promotion_code')->where('code', '=', $code)->where('status', '=', 'active')->limit(1)->execute();
		
		return $promotion_code->is_valid();
	}
	
	// Check that a promotion code is valid based on it's type and rules
	public function is_valid()
	{
		// STEP 1: Do we have a code loaded?
		if ( ! $this->loaded())
		{
			throw new Kohana_Exception('Unrecognised Promotion Code.');
		}
		
		// STEP 2: Is it date valid?
		$now = time();
		if ( ! is_null($this->start_date) AND $now < $this->start_date)   
		{
			throw new Kohana_Exception('Promotion has not begun.');
		}
		elseif ( ! is_null($this->end_date) AND $now > $this->end_date)
		{
			throw new Kohana_Exception('Promotion has expired.');
		}
		
		// STEP 3: Has it reached max redemptions?
		if ( ! is_null($this->max_redemptions) AND $this->max_redemptions > 0)
		{
			if ($this->redeemed >= $this->max_redemptions)
			{
				throw new Kohana_Exception('Maximum redemptions reached.');
			}
		}
		
		// STEP 4: Does the basket meet the minimum value?
		if ( ! is_null($this->basket_minimum_value) AND $this->basket_minimum_value > 0)
		{
			$basket = Model_Basket::instance();
			
			if ($basket->calculate_subtotal() <= $this->basket_minimum_value)
			{
				throw new Kohana_Exception('Basket value is too low.');
			}
		}
		
		return $this;
	}
	
	public function update($data)
	{
		$this->code = $data['code'];
		$this->description = $data['description'];
		$this->status = $data['status'];
		$this->run_indefinitely = ! isset($data['run_indefinitely']);
		if ( ! $this->run_indefinitely)
		{
			$this->start_date = $data['start_date'];
			$this->end_date = $data['end_date'];
		}
		$this->max_redemptions = $data['max_redemptions'];
		$this->discount_on = $data['discount_on'];
		
		// Clear down and save products.
		$this->remove('products', $this->products);
		
		if ($this->discount_on == 'sales_order_item')
		{
			if (isset($data['products']))
			{
				$this->add('products', $data['products']);
			}
		}
	
		return $this->save();
	}
	
	public function redeem()
	{
		$this->redeemed++;
		$this->save();
	}
	
	public function calculate_most_suitable_reward($basket)
	{
		$reward = $this->get('rewards')->where('basket_minimum_value', '<', $basket->calculate_subtotal())->order_by('basket_minimum_value', 'DESC')->limit(1)->execute();
		return $reward->loaded() ? $reward : NULL;
	}
}