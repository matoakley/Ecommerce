<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Customers extends Controller_Admin_Application
{
	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.customer_accounts'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
	}
	
	public function action_index()
	{
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;

		$search = Model_Customer::search(array(), $items);

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items'  => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.customers.index', $_SERVER['REQUEST_URI']);
		
		$this->template->customers = $search['results'];
		$this->template->total_customers = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}
	
	public function action_edit()
	{
		$customer = Model_Customer::load($this->request->param('id'));
	
		if ($this->request->param('id') AND ! $customer->loaded())
		{
			throw new Kohana_Exception('Customer could not be found.');
		}
	
		$redirect_to = $this->session->get('admin.customers.index', '/admin/customers');
		$this->template->cancel_url = $redirect_to;
	
		$fields = array(
			'customer' => $customer->as_array(),
			'custom_fields' => $customer->custom_fields(),
		);
		$fields['customer']['customer_types'] = $customer->customer_types->as_array('id', 'id');
		$fields['customer']['default_billing_address'] = $customer->default_billing_address->id;
		$fields['customer']['default_shipping_address'] = $customer->default_shipping_address->id; 
		$errors = array();
		
		if ($_POST)
		{
			try
			{
				$customer->validate($_POST['customer']);
			}
			catch (Validate_Exception $e)
			{
				$errors['customer'] = $e->array->errors();
			}
			
			if (empty($errors))
			{
				$customer->admin_update($_POST['customer']);
				$customer->update_custom_field_values($_POST['custom_fields']);
			
				// If 'Save & Exit' has been clicked then lets hit the index with previous page/filters
				if (isset($_POST['save_exit']))
				{
					$this->request->redirect($redirect_to);
				}
				else
				{
					$this->request->redirect('/admin/customers/edit/'.$customer->id);
				}
			}
			else
			{
				$fields = $_POST;
				$fields['custom_fields'] = isset($_POST['custom_fields']) ? $_POST['custom_fields'] : array();
			}
		}
		
		$this->template->fields = $fields;
		$this->template->errors = $errors;
	
		$items_per_page = 20;
		$page = isset($_GET['addresses_page']) ? $_GET['addresses_page'] : 1;
		
		$this->template->addresses = $customer->get('addresses')->order_by('created', 'DESC')->limit($items_per_page)->offset(($page - 1) * $items_per_page)->execute();
		$this->template->addresses_pagination = Pagination::factory(array(
			'total_items' => $customer->get('addresses')->count(),
			'items_per_page' => $items_per_page,
			'auto_hide'	=> false,
			'current_page'   => array('source' => 'query_string', 'key' => 'addresses_page'),
		));
	
		$items_per_page = 20;
		$page = isset($_GET['orders_page']) ? $_GET['orders_page'] : 1;
		
		$this->template->orders = $customer->get('orders')->order_by('created', 'DESC')->limit($items_per_page)->offset(($page - 1) * $items_per_page)->execute();
		$this->template->orders_pagination = Pagination::factory(array(
			'total_items' => $customer->get('orders')->count(),
			'items_per_page' => $items_per_page,
			'auto_hide'	=> false,
			'current_page'   => array('source' => 'query_string', 'key' => 'orders_page'),
		));
	
		if ($this->modules['crm'])
		{
			$items_per_page = 20;
			$page = isset($_GET['communications_page']) ? $_GET['communications_page'] : 1;
			
			$this->template->communications = $customer->get('communications')->order_by('date', 'DESC')->limit($items_per_page)->offset(($page - 1) * $items_per_page)->execute();
			$this->template->communications_pagination = Pagination::factory(array(
				'total_items' => $customer->get('communications')->count(),
				'items_per_page' => $items_per_page,
				'auto_hide'	=> false,
				'current_page'   => array('source' => 'query_string', 'key' => 'communications_page'),
			));
			
			$this->template->communication_types = Model_Customer_Communication::$types;
		}
	
		$this->template->customer = $customer;
		$this->template->customer_types = Jelly::select('customer_type')->execute();
		$this->template->customer_statuses = Model_Customer::$statuses;
		$this->template->countries = Model_Country::list_active();
	}
	
	public function action_add_communication()
	{
		if ( ! Request::$is_ajax)
		{
			throw new Kohana_Exception('Action only available over AJAX.');
		}
	
		$customer = Model_Customer::load($this->request->param('customer_id'));
		
		if ( ! $customer->loaded())
		{
			throw new Kohana_Exception('Customer could not be found.');
		}
		
		$errors = array();
		
		try
		{
			$customer->add_communication($_POST['communication']);
			$items_per_page = 20;
			$page = isset($_GET['communications_page']) ? $_GET['communications_page'] : 1;
			
			$this->template->communications = $customer->get('communications')->order_by('date', 'DESC')->limit($items_per_page)->offset(($page - 1) * $items_per_page)->execute();
			$this->template->communications_pagination = Pagination::factory(array(
				'total_items' => $customer->get('communications')->count(),
				'items_per_page' => $items_per_page,
				'auto_hide'	=> false,
				'current_page'   => array('source' => 'query_string', 'key' => 'communications_page'),
			));
		}
		catch (Validate_Exception $e)
		{
			$errors['communication'] = $e->array->errors();
		}
		
		$this->template->errors = $errors;
	}
	
	public function action_add_address()
	{
		if ( ! Request::$is_ajax)
		{
			throw new Kohana_Exception('Action only available over AJAX.');
		}
	
		$customer = Model_Customer::load($this->request->param('customer_id'));
		
		if ( ! $customer->loaded())
		{
			throw new Kohana_Exception('Customer could not be found.');
		}
		
		$errors = array();
		
		try
		{
			$address = $customer->add_address($_POST['address']);
			$items_per_page = 20;
			$page = isset($_GET['addresses_page']) ? $_GET['addresses_page'] : 1;
			
			$this->template->addresses = $customer->get('addresses')->order_by('created', 'DESC')->limit($items_per_page)->offset(($page - 1) * $items_per_page)->execute();
			$this->template->addresses_pagination = Pagination::factory(array(
				'total_items' => $customer->get('addresses')->count(),
				'items_per_page' => $items_per_page,
				'auto_hide'	=> false,
				'current_page'   => array('source' => 'query_string', 'key' => 'addresses_page'),
			));
		}
		catch (Validate_Exception $e)
		{
			$errors['address'] = $e->array->errors();
		}
		
		$this->template->errors = $errors;
		
		$this->template->fields = array(
			'customer' => array(
				'default_billing_address' => $customer->default_billing_address->id,
				'default_shipping_address' => $customer->default_shipping_address->id,
			),
		);
	}
}