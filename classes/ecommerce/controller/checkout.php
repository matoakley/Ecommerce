<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Checkout extends Controller_Application {

	function before()
	{
		if(Request::$protocol != 'https' AND IN_PRODUCTION AND ! Kohana::config('ecommerce.no_ssl'))
		{
			$this->request->redirect(URL::site(Request::Instance()->uri, 'https'));
		}
		
		parent::before();
	}

	function action_index()
	{					
		if ( ! $this->basket->loaded())
		{
			$this->request->redirect('/basket');
		}
		
		if ($_POST)
		{
			try
			{
				$customer = Model_Customer::create($_POST['customer']);
				$delivery_name = array();
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
			catch (Validate_Exception $e)
			{
				$this->template->customer = $_POST['customer'];
				$this->template->billing_address = $_POST['billing_address'];
				$this->template->delivery_address = $_POST['delivery_address'];
				$this->template->errors = $e->array->errors();
			}
		}
		
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