<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Customer extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->sorting(array('lastname' => 'ASC', 'firstname' => 'ASC'))
			->fields(array(
				'id' => new Field_Primary,
				'user' => new Field_BelongsTo,
				'orders' => new Field_HasMany(array(
					'foreign' => 'sales_order.customer_id',
				)),
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
				'default_billing_address' => new Field_BelongsTo(array(
					'foreign' => 'address.id',
					'column' => 'default_billing_address_id',
				)),
				'default_shipping_address' => new Field_BelongsTo(array(
					'foreign' => 'address.id',
					'column' => 'default_shipping_address_id',					
				)),
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
	
	public static function send_forgotten_password_email($email_address)
	{
		// Send an email to user with a key (maybe use hashed password?)
		Email::connect();
		
		$message = Twig::factory('emails/forgotten_password.html');
		
		$user = Jelly::select('user')->where('email', '=', $email_address)->limit(1)->execute();
		
		// Check that the email address provided links to a use and also to a customer
		if ( ! $user->loaded() OR ! $user->customer->loaded())
		{
			throw new Kohana_Exception('User not found');
		}
		
		$message->user = $user->customer;
		$message->site_name = Kohana::config('ecommerce.site_name');
		$message->reset_link = Route::url('customer_reset_password', array('reset_hash' => urlencode($user->password), 'email' => urlencode($user->email)));

		$to = array(
			'to' => array($user->email, $user->customer->firstname . ' ' . $user->customer->lastname),
		);

		return Email::send($to, array(Kohana::config('ecommerce.email_from_address') => Kohana::config('ecommerce.email_from_name')), 'Password reset request from ' . Kohana::config('ecommerce.site_name'), $message, true);
	}
	
	public static function validate_password_reset($email, $hash)
	{
		// Checks that the email adddress/password hash combination exists so that we know the customer is legit
		$user = Jelly::select('user')->where('email', '=', $email)->where('password', '=', $hash)->limit(1)->execute();
		return ($user->loaded()) ? $user : FALSE;
	}
	
	public function update_at_checkout($data)
	{
		// Format email address to lowercase
		$data['email'] = strtolower($data['email']);
		
		$this->firstname = $data['firstname'];
		$this->lastname = $data['lastname'];
		$this->email = $data['email'];
				
		$this->save();
		
		if (isset($data['email_subscribe']))
		{
			Model_Subscriber::create($this->email, $this->id);
		}
		
		return $this;
	}
	
	public function create_account($password)
	{
		$this->user = Model_User::create_for_customer($this, $password);
		return $this->save();
	}
	
	public function set_default_billing_address($address)
	{
		$this->default_billing_address = $address;
		$this->save();
	}
	
	public function set_default_shipping_address($address)
	{
		$this->default_shipping_address = $address;
		$this->save();
	}
	
	public function completed_orders()
	{
		return $this->get('orders')->where('status', '=', 'complete')->execute();
	}
}