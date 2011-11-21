<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Customer extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('customers')
			->fields(array(
				'id' => new Field_Primary,
				'user' => new Field_BelongsTo,
				'firstname' => new Field_String(array(
					'rules' => array(
						'not_empty' => NULL,
					),
				)),
				'lastname' => new Field_String(array(
					'rules' => array(
						'not_empty' => NULL,
					),
				)),
				'email' => new Field_Email(array(
					'rules' => array(
						'not_empty' => NULL,
					),					
				)),
				'referred_by' => new Field_String,
				'addresses' => new Field_HasMany(array(
					'foreign' => 'address.customer_id',
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

	public static function create($data)
	{
		// Format email address to lowercase
		$data['email'] = strtolower($data['email']);
		
		$customer = Jelly::factory('customer');
		
		$customer->firstname = $data['firstname'];
		$customer->lastname = $data['lastname'];
		$customer->email = $data['email'];
		
		if (isset($data['referred_by']))
		{
			$customer->referred_by = $data['referred_by'];
		}
				
		$customer->save();
		
		if (isset($data['email_subscribe']))
		{
			Model_Subscriber::create($customer->email, $customer->id);
		}
		
		return $customer;
	}
	
	public function set_default_address($address)
	{
		$this->default_address = $address;
		$this->save();
	}
}