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
				$errors['customer'] = $e->array->errors();
			}
			
			$address = Model_Address::load();
			
			try
			{
				Model_Address::customer_address_validator($_POST['address']);
			}
			catch (Validate_Exception $e)
			{
				$errors['address'] = $e->array->errors();
			}
			
			$user = Model_User::load();
			
			$_POST['user']['username'] = $_POST['user']['email'];
			
			try
			{
				$user->validate($_POST['user']);
			}
			catch (Validate_Exception $e)
			{
				$errors['user'] = $e->array->errors();
			}
			
			if (empty($errors))
			{
				$customer = Model_Customer::create($_POST['customer']);
				$customer->create_account($_POST['user']['password'])->add_address($_POST['address']);
				
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
}