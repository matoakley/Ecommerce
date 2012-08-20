<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Represents a Customer within the ecommerce system.
 *
 * @package    Ecommerce
 * @author     Matt Oakley
 */
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
				'firstname' => new Field_String,
				'lastname' => new Field_String,
				'company' => new Field_String,
				'account_ref' => new Field_String,
				'customer_types' => new Field_ManyToMany,
				'email' => new Field_Email,
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
				'status' => new Field_String,
				'price_tier' => new Field_BelongsTo,
				'parent' => new Field_BelongsTo(array(
					'foreign' => 'customer.id',
					'column' => 'customer_id',
				)),
				'notes' => new Field_String,
				'contacts' => new Field_HasMany(array(
					'foreign' => 'customer.customer_id',
				)),
				'telephone' => new Field_String,
				'position' => new Field_String,
				'invoice_terms' => new Field_Integer,
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
			
		// Include relationships that exist with CRM module
		if (Kohana::config('ecommerce.modules.crm'))
		{
			$meta->fields(array(
				'communications' => new Field_HasMany(array(
					'foreign' => 'customer_communication.customer_id',
				)),
			));
		}
	}
	
	public static $statuses = array(
		'active',
		'on_hold',
		'archived',
	);
	
	public static $searchable_fields = array(
		'filtered' => array(
			'customer_type' => array(
				'join' => array(
					'customer_types_customers' => array('customer.id', 'customer_types_customers.customer_id'),
					'customer_types' => array('customer_types.id', 'customer_types_customers.customer_type_id'),
				),
				'field' => 'customer_type.id',
			),
			'status' => array(
				'field' => 'status',
			),
			'price_tier' => array(
				'field' => 'price_tier',
			),
		),
		'search' => array(
			'firstname',
			'lastname',
			'account_ref',
			'company',
		),
	);
	
	public static function customer_email_validator($data)
	{
		$validator = Validate::factory($data)
											->filter(TRUE, 'trim')
											->rule('email', 'not_empty')
											->rule('firstname', 'not_empty')
											->rule('lastname', 'not_empty');
		
		if ( ! $validator->check())
		{
			throw new Validate_Exception($validator);
		}
		
		return TRUE;
	}
	
	public static function create($data)
	{
		// Format email address to lowercase
		$data['email'] = strtolower($data['email']);
		
		$customer = Jelly::factory('customer');
		
		$customer->firstname = $data['firstname'];
		$customer->lastname = $data['lastname'];
		$customer->email = $data['email'];
		
		if (isset($data['notes']))
		{
  		$customer->notes = $data['contact_notes'];
		}
		
		if (isset($data['referred_by']))
		{
			$customer->referred_by = $data['referred_by'];
		}
		
		if (Caffeine::modules('crm'))
		{
			$customer->add('customer_types', Kohana::config('ecommerce.default_web_customer_type'));
		}
		
		if (isset($data['company']))
		{
			$customer->company = $data['company'];
		}
		
		$customer->status = 'active';
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
	
	/*
	 * Little helper method to spit out the customer's full name.
	 */
	public function name()
	{
		if ($this->firstname != '')
		{
			return $this->firstname.' '.$this->lastname;
		}
		elseif ($this->company != '')
		{
			return $this->company; 
		}
	}
	
	public function add_communication($data)
	{
		return Model_Customer_Communication::create_communication_for_customer($this, $data);
	}

	public function add_address($data)
	{
		return Model_Address::create($data, $this->id);
	}
		
	public function admin_update($data)
	{
		$this->firstname = $data['firstname'];
		$this->lastname = $data['lastname'];
		$this->company = $data['company'];
		$this->account_ref = $data['account_ref'];
		$this->email = $data['email'];
		if (isset($data['default_billing_address']))
		{
			$this->default_billing_address = $data['default_billing_address'];
		}
		if (isset($data['default_shipping_address']))
		{
			$this->default_shipping_address = $data['default_shipping_address'];
		}
		if (isset($data['notes']))
		{
  		$customer->notes = $data['contact_notes'];
		}

	
		// Clear down and save customer types.
		$this->remove('customer_types', $this->customer_types);
		if (isset($data['customer_types']))
		{
			$this->add('customer_types', $data['customer_types']);
		}
		
		if (Caffeine::modules('tiered_pricing') AND isset($data['price_tier']))
		{
			$this->price_tier = $data['price_tier'];
		}
		if (isset($data['invoice_terms']))
		{
			$this->invoice_terms = $data['invoice_terms'];
		}
		
		if (Caffeine::modules('trade_area') AND isset($data['trade_area']))
		{
			$this->user->add('roles', Jelly::select('role')->where('name', '=', 'trade_area')->load())->save();
		}
		else
		{
			$this->user->remove('roles', Jelly::select('role')->where('name', '=', 'trade_area')->load())->save();
		}

		$this->status = $data['status'];
		
	
		return $this->save();
	}
	
	public function is_commercial_customer()
	{
		return (bool) $this->get('customer_types')->where('id', '=', Kohana::config('ecommerce.default_commercial_customer_type'))->count();
	}
	
	/**
	 * Fetch the price that the Customer should pay for a SKU, taking tiered pricing into account when necessary.
	 * @author  Matt Oakley
	 * @param   Model_Sku   SKU to fetch price for
	 * @return  float				price
	 */
	public function price_for_sku($sku)
	{
		if (Kohana::config('ecommerce.modules.tiered_pricing') AND $this->price_tier->loaded())
		{
			return $sku->price_for_tier($this->price_tier);
		}
		else
		{
			return $sku->retail_price();
		}
	}
	
	public function delete($key = NULL)
	{
		// Remove any communications held against the customer to keep the DB tidy
		foreach ($this->communications as $communication)
		{
			$communication->delete();
		}  
	
		return parent::delete($key);
	}
	
	public function archive()
	{
		$this->status = 'archived';
		return $this->save();
	}
	
	/**
	 * Creates a new Customer as a Contact of the Customer.
	 * @author  Matt Oakley
	 * @param   array   Contact data
	 * @return  Customer
	 */
	public function add_contact($data)
	{
		$contact = Jelly::factory('customer');
		$contact->parent = $this;
		$contact->firstname = $data['firstname'];
		$contact->lastname = $data['lastname'];
		$contact->email = $data['email'];
		$contact->telephone = $data['telephone'];
		$contact->position = $data['position'];
		$contact->status = 'active';
		$contact->id = 'id';
		if (isset($data['notes']))
		{
  		$contact->notes = $data['notes'];
		}
		


		return $contact->save();
	}
	

	/**
	 * Email a new trade customer to confirm receipt.
	 * @author  Matt Oakley
	 * @return  boolean
	 */
	public function email_trade_sign_up_confirmation()
	{
		Email::connect();
		
		$message = Twig::factory('emails/trade_sign_up_received.html');
		$message->customer = $this;
		$message->site_name = Kohana::config('ecommerce.site_name');

		$to = array(
			'to' => array($this->user->email, $this->firstname . ' ' . $this->lastname),
		);

		return Email::send($to, array(Kohana::config('ecommerce.email_from_address') => Kohana::config('ecommerce.email_from_name')), 'Trade account sign up for '.Kohana::config('ecommerce.site_name').' received', $message, true);
	}
	
	public function update()
	{
		if (isset($_POST['email']))
		{
	    $this->email = $_POST['email'];
	  }
	  
		if (isset($_POST['notes']))
		{
	    $this->notes = $_POST['notes'];
	  }
	  
	  if (isset($_POST['telephone']))
		{
	    $this->telephone = $_POST['telephone'];
	  }
	  
	  if (isset($_POST['position']))
		{
	    $this->position = $_POST['position'];
	  }
	  
	  if (isset($_POST['firstname']))
	  {
	    explode(" ", $_POST['firstname']);
	    $first = explode(" ", $_POST['firstname']);
	    $this->firstname = $first[0];
	    $this->lastname = $first[1];
	  }
 
  	return $this->save();
	}
	
	public function trade_update_validator($data)
	{
		$validator = Validate::factory($data)
			->filters('firstname', array('trim' => NULL))->rules('firstname', array('not_empty' => NULL)) // Firstname
			->filters('lastname', array('trim' => NULL))->rules('lastname', array('not_empty' => NULL)) // Lastname
			->filters('email', array('trim' => NULL))->rules('email', array('not_empty' => NULL, 'email' => NULL))->callback('email', 'Model_User::_email_is_unique', array('id' => $this->user->id)); // Email
			
		if ( ! $validator->check())
		{
			throw new Validate_Exception($validator);
		}
		
		return TRUE;
	}
	
	// This is where the customer updates their own account
	public function customer_update($data)
	{
		$this->firstname = $data['firstname'];
		$this->lastname = $data['lastname'];
		if (isset($data['company']))
		{
			$this->company = $data['company'];
		}
		$this->email = $data['email'];
		$this->user->update_email($data['email']);
		return $this->save();
	}
}