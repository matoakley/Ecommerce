<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Reward_Points_Profile extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->sorting(array('id' => 'ASC'))
			->fields(array(
			   'id' => new Field_Primary,
			  'name' => new Field_String,
				'points_per_pound' => new Field_Float(array(
				  'places' => 4,
				)),
				'redeem_value' => new Field_Float(array(
				  'places' => 4,
				)),
				'customer_referral' => new Field_Integer,
				'new_customer_referral' => new Field_Integer,
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
			
	public function update($data)
	{
		$this->points_per_pound = $data['price_per_pound'];
		$this->redeem_value = $data['redeem_price'] / 100;
		$this->customer_referral = $data['customer_referral'];
		$this->new_customer_referral = $data['new_referral'];
				
		return $this->save();
	}
	
	// a little helper to keep the pence per point tidy
	 public function decimal_to_pence()
  {
    echo $this->redeem_value * 100;
  }
	
}