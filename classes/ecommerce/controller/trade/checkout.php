<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Trade_Checkout extends Controller_Trade_Application
{
	public function before()
	{
		if ( ! Caffeine::modules('sales_orders'))
		{
			throw new Kohana_Exception('The "sales_orders" module is not enabled');
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
		
		// If customer accounts module is enabled, we should check if they are logged in
		// or set as a new customer in session and if not, offer them the chance to log in.
		if ($this->modules['customer_accounts'] AND ! $this->session->get('new_customer') AND ! $this->auth->logged_in())
		{
			$this->request->redirect(Route::get('checkout_login')->uri());
		}
		
		$this->template->addresses = $this->auth->get_user()->customer->get('addresses')->execute();
		$this->template->default_delivery_address = $this->auth->get_user()->customer->default_shipping_address;
		
		$fields = array();
		$errors = array();
		
		if ($_POST)
		{	
			$customer = $this->auth->get_user()->customer;
		
			$delivery_address = NULL;
		
			if (isset($_POST['delivery_address']) AND $_POST['delivery_address'] != '')
			{
				$delivery_address = $_POST['delivery_address'];
			}
			else
			{
				$_POST['new_delivery_address']['country'] = 1;
				try
				{
					Model_Address::customer_address_validator($_POST['new_delivery_address']);
				}
				catch (Validate_Exception $e)
				{
					$errors['new_delivery_address'] = $e->array->errors();
					$fields['new_delivery_address'] = $_POST['new_delivery_address'];
					$fields['show_new_delivery_address'] = TRUE;
				}
			}
			
			if (empty($errors))
			{
				if ( ! $delivery_address)
				{
					$delivery_address = Model_Address::create($_POST['new_delivery_address'], $customer->id, TRUE);
				}
				$customer->set_default_shipping_address($delivery_address);
				$sales_order = Model_Sales_Order::create_trade_from_basket($this->basket, $customer, $delivery_address);
				
				if (Kohana::config('ecommerce.invoice_trade_area_orders_at_checkout'))
				{
					$sales_order->send_invoice(TRUE);
				}
				
				$this->request->redirect('/checkout/confirm');
			}
		}
		
		$this->template->fields = $fields;
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
	
	public function action_login()
	{
		if ( ! $this->basket->loaded() OR count($this->basket->items) == 0)
		{
			$this->request->redirect('/basket');
		}
	
		if ($_POST)
		{
			if (isset($_POST['existing_x']))
			{
				// Log in
				if ($this->auth->login($_POST['login']['email'], $_POST['login']['password']) AND $this->auth->logged_in())
				{
					$this->request->redirect(Route::get('checkout')->uri());
				}
				else
				{
					// Force a log out in case the user has authenticated as an admin rather than customer
					$this->auth->logout();
					$this->template->fields = $_POST;
					$this->template->login_failed = TRUE;
				}
			}
			else
			{
				$this->session->set('new_customer', TRUE);
				$this->request->redirect(Route::get('checkout')->uri());
			}
		}
	}
}