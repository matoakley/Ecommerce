<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Checkout extends Controller_Application {

	function before()
	{
		if(Request::$protocol != 'https' AND IN_PRODUCTION)
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
		
		$this->template->basket = $this->basket;
		$this->template->delivery_options = Model_Delivery_Option::available_options();
	}
	
	function action_confirm()
	{
		$sales_order = Model_Sales_Order::load();
		$this->template->sales_order = $sales_order;
		
		$hash_array = array(
			'storefront_id' => Kohana::config('payment.hsbc.storefront_id'), // HSBC Storefront ID
			'cpi_direct_result_url' => Kohana::config('payment.hsbc.data_return_url'), // Data Return URL
			'cpi_return_url' => Kohana::config('payment.hsbc.user_return_url'), // User Return URL
			'sales_order_id' => $sales_order->id, // Our Sales Order ID
			'order_desc' => Kohana::config('payment.hsbc.transaction_description'), // Transaction description
			'purchase_amount' => $sales_order->order_total * 100, // Order total
			'purchase_currency' => Kohana::config('payment.hsbc.currency_code'), // Currency code
			'timestamp' => time() * 1000, // Current timestamp (miliseconds since UNIX epoch)
			'mode' => Kohana::config('payment.hsbc.mode'), // Appliction status (Test/Production)
			'billing_firstname' => $sales_order->customer->firstname,
			'billing_lastname' => $sales_order->customer->lastname,
			'billing_address_1' => $sales_order->billing_address->line_1,
			'billing_address_2' => $sales_order->billing_address->line_2,
			'billing_address_town' => $sales_order->billing_address->town,
			'billing_address_county' => $sales_order->billing_address->county,
			'billing_address_country' => 826,
			'billing_address_postcode' => $sales_order->billing_address->postcode,
			'delivery_firstname' => $sales_order->delivery_firstname,
			'delivery_lastname' => $sales_order->delivery_lastname,
			'delivery_address_1' => $sales_order->delivery_address->line_1,
			'delivery_address_2' => $sales_order->delivery_address->line_2,
			'delivery_address_town' => $sales_order->delivery_address->town,
			'delivery_address_county' => $sales_order->delivery_address->county,
			'delivery_address_country' => 826,
			'delivery_address_postcode' => $sales_order->delivery_address->postcode,
			'customer_email' => $sales_order->customer->email,
		);
		$this->template->hash_array = $hash_array;
		
		$cmd = '';
		
		foreach ($hash_array as $value)
		{
			$cmd .= '"' . $value . '" ';
		}

		$path = Kohana::config('payment.hsbc.path_to_hash_script');
		$file = Kohana::config('payment.hsbc.hash_script');
		$cpi_hash_key = Kohana::config('payment.hsbc.cpi_hash_key');

		$cmd="$path/$file $cpi_hash_key $cmd";

		$ret=exec($cmd); 

		$ret=explode(':',$ret);

		//Returns the hash 
		$hash=trim($ret[1]); 
		
		$this->template->hsbc_hash = $hash;
		
	}
	
	function action_payment_result()
	{
		if ($_POST)
		{
			$sales_order_id = $_POST['OrderId'];
			$cpi_results_code = $_POST['CpiResultsCode'];
			$order_hash = $_POST['OrderHash'];
			$this->template->sales_order = Model_Sales_Order::process_payment_result($sales_order_id, $cpi_results_code, $order_hash);
		}
		else
		{
			$this->request->redirect('checkout');
		}
	}

}