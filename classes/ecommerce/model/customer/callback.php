<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Customer_Callback extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->sorting(array('created' => 'DESC'))
			->fields(array(
				'id' => new Field_Primary,
				'customer' => new Field_BelongsTo,
				'date' => new Field_Timestamp(array(
					'format' => 'Y-m-d H:i:s',
				)),
				'notes' => new Field_Text,
				'complete' => new Field_Boolean,
				'user' => new Field_BelongsTo,
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
	
	public static function create_callback_for_customer($customer, $data)
	{
  	$callback = Jelly::factory('customer_callback');
		
		$callback->customer = $customer;
		$callback->user = $data['user'];
				
		$callback->notes = $data['notes'];
		$communication->date = $data['date'];
		
		return $callback->save();
	}	
}