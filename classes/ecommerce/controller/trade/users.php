<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Trade_Users extends Controller_Trade_Application
{
	public function action_sign_in()
	{
		// If user is already logged in then send them to the trade homepage
		if ($this->auth->logged_in('trade'))
		{
			$this->request->redirect(Route::get('default')->uri());
		}
	
		$fields = array();
		$errors = array();
		
		if ($_POST)
		{
			if ($this->auth->login($_POST['user']['email'], $_POST['user']['password']) AND $this->auth->logged_in('trade_area'))
			{
				$this->request->redirect($this->session->get_once('redirected_from', Route::get('default')->uri()));
			}
			else
			{
				$this->auth->logout();
				
				$errors = TRUE;
				$fields = $_POST;
			}
		}
		
		$this->template->fields = $fields;
		$this->template->errors = $errors;
	}
	
	public function action_sign_up()
	{
		// If user is already logged in then send them to the trade homepage
		if ($this->auth->logged_in('trade'))
		{
			$this->request->redirect(Route::get('default')->uri());
		}
		
		$fields = array();
		$errors = array();
		
		if ($_POST)
		{
			$customer = Model_Customer::load();
		
			$_POST['customer']['email'] = $_POST['user']['email'];
			
			try
			{
				Model_Customer::customer_email_validator($_POST['customer']);
			}
			catch (Validate_Exception $e)
			{
				$errors['customer'] = $e->array->errors('model/customer');
			}
			
			$address = Model_Address::load();
			
			try
			{
				Model_Address::customer_address_validator($_POST['address']);
			}
			catch (Validate_Exception $e)
			{
				$errors['address'] = $e->array->errors('model/address');
			}
			
			$user = Model_User::load();
			
			$_POST['user']['username'] = $_POST['user']['email'];
			
			try
			{
				$user->validate($_POST['user']);
			}
			catch (Validate_Exception $e)
			{
				$errors['user'] = $e->array->errors('model/user');
			}
			
			if (empty($errors))
			{
				$customer = Model_Customer::create($_POST['customer']);
				$customer->create_account($_POST['user']['password']);
				$address = $customer->add_address($_POST['address']);
				$customer->set_default_billing_address($address);
				$customer->set_default_shipping_address($address);
				
				// Send an email to customer and administrator to confirm receipt
				$customer->email_trade_sign_up_confirmation();
				
				// Redirect to thank you page
				$this->request->redirect(Route::get('sign_up_received')->uri());
			}
			else
			{
				$fields = $_POST;
			}
		}
		
		$countries = Model_Country::search();
		$this->template->countries = $countries['results'];
		if ( ! isset($fields['address']['country']))
		{
			$fields['address']['country'] = Kohana::config('ecommerce.default_country');
		}
		
		$this->template->fields = $fields;
		$this->template->errors = $errors;
	}
	
	public function action_sign_out()
	{
		$this->auth->logout();
		$this->request->redirect(Route::get('default')->uri());
	}
	
	public function action_sign_up_requested()
	{
		
	}
	
	public function action_edit_account()
	{
		$user = $this->auth->get_user();
		$customer = $user->customer;
		$address = $customer->default_billing_address;
		
		$fields = array(
			'address' => $address->as_array(),
			'customer' => $customer->as_array(),
			'user' => $user->as_array(),
		);
		$fields['address']['country'] = $address->country->id;
		$errors = array();
		
		if ($_POST)
		{
			try
			{
				$customer->trade_update_validator($_POST['customer']);
			}
			catch (Validate_Exception $e)
			{
				$errors['customer'] = $e->array->errors();
			}
			
			try
			{
				Model_Address::customer_address_validator($_POST['address']);
			}
			catch (Validate_Exception $e)
			{
				$errors['address'] = $e->array->errors();
			}
			
			if (empty($errors))
			{
				$customer->customer_update($_POST['customer']);
				$address->update($_POST['address']);
				
				$this->request->redirect(Route::get('default')->uri());
			}
			else
			{
				$fields = $_POST;
			}
		}
		
		$this->template->fields = $fields;
		$this->template->errors = $errors;
		
		$countries = Model_Country::search();
		$this->template->countries = $countries['results'];
	}
	
	public function action_change_password()
	{
		$errors = array();
		
		if ($_POST)
		{
			if ($this->auth->check_password($_POST['current_password']))
			{
				try
				{
					$this->auth->get_user()->change_password($_POST['new_password']);
					$this->request->redirect(Route::get('edit_account')->uri());
				}
				catch (Validate_Exception $e)
				{
					$errors['new_password'] = TRUE;
				}
			}
			else
			{
				$errors['current_password'] = TRUE;
			}
		}
		
		$this->template->errors = $errors;
	}
	
	public function action_order_history()
	{
		$this->template->sales_orders = $this->auth->get_user()->customer->get('orders')->where('status', 'IN', array('invoice_generated', 'invoice_sent', 'complete'))->order_by('created', 'DESC')->execute();
	}
	
	public function action_view_order()
	{
		$this->template->sales_order = Model_Sales_Order::load($this->request->param('id'));
	}
	
}