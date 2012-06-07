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
		);
		$errors = array();
		
		if ($_POST)
		{
			
		}
		
		$this->template->fields = $fields;
		$this->template->errors = $errors;
	
		if ($this->modules['crm'])
		{
			$items_per_page = 5;
			$page = isset($_GET['communications_page']) ? $_GET['communications_page'] : 1;
			
			$this->template->communications = $customer->get('communications')->limit($items_per_page)->offset(($page - 1) * $items_per_page)->execute();
			$this->template->communications_pagination = Pagination::factory(array(
				'total_items' => $customer->get('communications')->count(),
				'items_per_page' => $items_per_page,
				'auto_hide'	=> false,
				'view' => 'pagination/admin',
				'current_page'   => array('source' => 'query_string', 'key' => 'communications_page'),
			));
		}
	
		$this->template->customer = $customer;
	}
}