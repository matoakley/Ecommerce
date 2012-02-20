<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Promotion_Code_Reward extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->sorting(array('basket_minimum_value' => 'ASC'))
			->fields(array(
				'id' => new Field_Primary,
				'promotion_code' => new Field_BelongsTo,
				'reward_type' => new Field_String,
				'basket_minimum_value' => new Field_Float(array(
					'places' => 4,
				)),
				'discount_type' => new Field_String,
				'discount_amount' => new Field_Float(array(
					'places' => 4,
					'callbacks' => array(
						'discount_amount_valid' => array('Model_Promotion_Code_Reward', '_is_discount_amount_valid'),
					),
				)),
				'discount_unit' => new Field_String(array(
					'default' => 'pounds',
					'callbacks' => array(
						'discount_unit_valid' => array('Model_Promotion_Code_Reward', '_is_discount_unit_valid'),
					),
				)),
				'sku' => new Field_BelongsTo(array(
					'callbacks' => array(
						'sku_valid' => array('Model_Promotion_Code_Reward', '_is_sku_valid'),
					),
				)),
				'sku_reward_price' => new Field_Float(array(
					'places' => 4,
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
	
	public static $reward_types = array(
		'discount',
		'item',
	);
	
	/****** Validation Callbacks ******/
	
	public static function _is_discount_amount_valid(Validate $array, $field)
	{
		$valid = TRUE;
		
		if (isset($array['reward_type']) AND $array['reward_type'] == 'discount')
		{
			if ( ! isset($array['discount_amount']) OR $array['discount_amount'] == '')
			{
				$valid = FALSE;
			}
			
			if ( ! $valid)
			{
				$array->error('slug', 'Discount Amount is a required field when Reward Type is Discount.');
			}
		}
	}
	
	public static function _is_discount_unit_valid(Validate $array, $field)
	{
		$valid = TRUE;
		
		if (isset($array['reward_type']) AND $array['reward_type'] == 'discount')
		{
			if ( ! isset($array['discount_unit']) OR $array['discount_unit'] == '')
			{
				$valid = FALSE;
			}
			
			if ( ! $valid)
			{
				$array->error('slug', 'Discount Unit is a required field when Reward Type is Discount.');
			}
		}
	}
	
	public static function _is_sku_valid(Validate $array, $field)
	{
		$valid = TRUE;
		
		if (isset($array['reward_type']) AND $array['reward_type'] == 'item')
		{
			if ( ! isset($array['sku']) OR $array['sku'] == '')
			{
				$valid = FALSE;
			}
			else
			{
				// Check it's a valid SKU
					$sku_exists = (bool) Jelly::select('sku')->where('id', '=', $array['sku'])->where('deleted', 'IS', NULL)->count();
					if ( ! $sku_exists)
					{
						$valid = FALSE;
					}
			}
			
			if ( ! $valid)
			{
				$array->error('slug', 'You must select the item to be given as a reward.');
			}
		}
	}
	
	public function update($promotion_code, $data)
	{
		$this->promotion_code = $promotion_code;
		$this->reward_type = $data['reward_type'];
		$this->basket_minimum_value = $data['basket_minimum_value'];
		
		switch ($this->reward_type)
		{
			case 'discount':
				$this->discount_amount = $data['discount_amount'];
				$this->discount_unit = $data['discount_unit'];
				break;
				
			case 'item':
				$this->sku = $data['sku'];
				$this->sku_reward_price = $data['sku_reward_price'];
				break;
				
			default:
				throw new Kohana_Exception('Unrecognised reward type');
				break;
		}
		
		return $this->save();
	}
}