<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Checkout extends Controller_Application
{
	public function before()
	{
		if ( ! Kohana::config('ecommerce.modules.sales_orders'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}

		if(Request::$protocol != 'https' AND IN_PRODUCTION AND ! Kohana::config('ecommerce.no_ssl'))
		{
			$this->request->redirect(URL::site(Request::Instance()->uri, 'https'));
		}
		
		parent::before();
	}
	
	
	function action_index()
	{					
		if ( ! $this->basket->loaded() OR count($this->basket->items) == 0)
		{
			$this->request->redirect('/basket');
		}
		
		// If the customer is logged in we should attempt to 
		// auto fill some of the fields
		if ($this->auth->logged_in('customer'))
		{
			$this->template->customer = $this->auth->get_user()->customer;
			$this->template->billing_address = $this->auth->get_user()->customer->get('addresses')->order_by('id', 'DESC')->limit(1)->execute();
		}
		
		$errors = array();
		
		if ($_POST)
		{
			$customer = $this->auth->logged_in('customer') ? $this->auth->get_user()->customer : Model_Customer::load(NULL);
			try
			{
				// If customer is already logged in then update their account with details provided, else create a new customer
				$customer->validate($_POST['customer']);
			}
			catch (Validate_Exception $e)
			{
				$errors['customer'] = $e->array->errors();
			}
			
			$delivery_name = array();
			if (isset($_POST['delivery_address']['same']))
			{
				try
				{
					Model_Address::customer_address_validator($_POST['billing_address']);
				}
				catch (Validate_Exception $e)
				{
					$errors['billing_address'] = $e->array->errors();
				}
			}
			else
			{
				try
				{
					Model_Address::customer_address_validator($_POST['billing_address']);
				}
				catch (Validate_Exception $e)
				{
					$errors['billing_address'] = $e->array->errors();
				}
				
				try
				{
					Model_Address::customer_address_validator($_POST['delivery_address']);
				}
				catch (Validate_Exception $e)
				{
					$errors['delivery_address'] = $e->array->errors();
				}
			}
			
			if (empty($errors))
			{
				if ($this->auth->logged_in('customer'))
				{
					$customer->update_at_checkout($_POST['customer']);
				}
				else
				{
					$customer = Model_Customer::create($_POST['customer']);
				}
				
				if (isset($_POST['delivery_address']['same']))
				{
					$billing_address = Model_Address::create($_POST['billing_address'], $customer->id, TRUE);
					$delivery_address = $billing_address;
					$delivery_name['delivery_firstname'] = $customer->firstname;
					$delivery_name['delivery_lastname'] = $customer->lastname;
				}
				else
				{
					$billing_address = Model_Address::create($_POST['billing_address'], $customer->id);
					$delivery_address = Model_Address::create($_POST['delivery_address'], $customer->id, TRUE);
					$delivery_name = $_POST['sales_order'];
				}
			
				$sales_order = Model_Sales_Order::create_from_basket($this->basket, $customer, $billing_address, $delivery_address, $delivery_name);
				$this->request->redirect('/checkout/confirm');
			}
			else
			{
				$this->template->customer = $_POST['customer'];
				$this->template->billing_address = $_POST['billing_address'];
				$this->template->delivery_address = $_POST['delivery_address'];
			}
		}
		
		$this->template->errors = $errors;
		
		$countries = Model_Country::search();
		$this->template->countries = $countries['results'];
		$this->template->default_country = Kohana::config('ecommerce.default_country');
		
		$this->template->basket = $this->basket;
		$this->template->delivery_options = Model_Delivery_Option::available_options();
	}
	
	function action_confirm()
	{
		$sales_order = Model_Sales_Order::load();
		$this->template->sales_order = $sales_order;
		
		$fields = array();
		$this->template->fields = $fields;			
	}
	
	function action_payment_result()
	{
		if ($_POST)
		{
			$this->template->sales_order = Model_Sales_Order::process_payment_result($_POST);
		}
		else
		{
			$this->request->redirect('checkout');
		}
	}

}