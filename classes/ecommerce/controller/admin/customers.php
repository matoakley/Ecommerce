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

		$search = Model_Customer::search(array(), $items, FALSE, isset($_GET['include_archived']));

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
		
		$this->template->showing_archived = isset($_GET['include_archived']);
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
		if ($this->modules['custom_fields'])
		{
		  $fields['custom_fields'] = $customer->custom_fields();
		}
		
		$fields['customer']['customer_types'] = $customer->customer_types->as_array('id', 'id');
		$fields['customer']['default_billing_address'] = $customer->default_billing_address->id;
		$fields['customer']['default_shipping_address'] = $customer->default_shipping_address->id; 
		if ($this->modules['tiered_pricing'])
		{
			$fields['customer']['price_tier'] = $customer->price_tier->id;
		}
		
		if ( ! $customer->loaded())
		{
			$fields['customer']['invoice_terms'] = Kohana::config('ecommerce.default_invoice_terms');
		}
		
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
			
			if (isset($_POST['address']))
			{
				$address = Model_Address::load();
			
				try
				{
					$address->validate($_POST['address']);
				}
				catch (Validate_Exception $e)
				{
					$errors['address'] = $e->array->errors();
				}
			}
			if (empty($errors))
			{
				$customer->admin_update($_POST['customer']);
				
				if (isset($_POST['custom_fields']))
				{
					$customer->update_custom_field_values($_POST['custom_fields']);
				}
				
				if (isset($_POST['address']))
				{
					$address->create_for_new_customer($customer, $_POST['address']);
				}
			
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
		$this->template->addresses = $customer->get('addresses')->where('archived', 'IS', NULL)->order_by('created', 'DESC')->limit($items_per_page)->offset(($page - 1) * $items_per_page)->execute();
		$this->template->addresses_pagination = Pagination::factory(array(
			'total_items' => $customer->get('addresses')->where('archived', 'IS', NULL)->count(),
			'items_per_page' => $items_per_page,
			'auto_hide'	=> false,
			'current_page'   => array('source' => 'query_string', 'key' => 'addresses_page'),
		));
	
		$page = isset($_GET['contacts_page']) ? $_GET['contacts_page'] : 1;
		$this->template->contacts = $customer->get('contacts')->limit($items_per_page)->offset(($page - 1) * $items_per_page)->execute();
		$this->template->contacts_pagination = Pagination::factory(array(
			'total_items' => $customer->get('contacts')->count(),
			'items_per_page' => $items_per_page,
			'auto_hide'	=> false,
			'current_page'   => array('source' => 'query_string', 'key' => 'contacts_page'),
		));
	
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
		
		if ($this->modules['tiered_pricing'])
		{
			$this->template->price_tiers = Jelly::select('price_tier')->execute();
		}
	}
	
	public function action_edit_communication()
	{
	
	$communication = Model_Customer_Communication::load($this->request->param('communication_id'));
	
	$communication->update($_POST);
	if (isset($_POST['text']))
	{
	echo $_POST['text'];
	}
	if (isset($_POST['title']))
	{
	echo $_POST['title'];
	}
	
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
			$communication = $customer->add_communication($_POST['communication']);
			$items_per_page = Kohana::config('ecommerce.pagination.crm_customer_items');
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
		$this->template->customer = $customer;
		
				
		$data = array(
			'html' => $this->template->render(),
		);
		
		echo json_encode($data);
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
			$items_per_page = $_POST['template'] == 'customer' ? 20 : 5;
			$page = isset($_GET['addresses_page']) ? $_GET['addresses_page'] : 1;
			
			$this->template->addresses = $customer->get('addresses')->where('archived', 'IS', NULL)->order_by('created', 'DESC')->limit($items_per_page)->offset(($page - 1) * $items_per_page)->execute();
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
		
		$this->template->customer = $customer;
		$this->template->template = $_POST['template'];
		
		$data = array(
			'html' => $this->template->render(),
		);
		
		echo json_encode($data);
	}
	
	public function action_delete_address()
	{
		if ( ! Request::$is_ajax)
		{
			throw new Kohana_Exception('Action only available over AJAX.');
		}
		
		// Load address through customer to avoid any mishaps
		$customer = Model_Customer::load($this->request->param('customer_id'));
		if ( ! $customer->loaded())
		{
			throw new Kohana_Exception('Customer could not be found.');
		}
		$address = $customer->get('addresses')->where('id', '=', $this->request->param('address_id'))->load();
		
		$address->archive();
	
		$items_per_page = 20;
		$page = isset($_GET['addresses_page']) ? $_GET['addresses_page'] : 1;
		
		$this->template->addresses = $customer->get('addresses')->where('archived', 'IS', NULL)->order_by('created', 'DESC')->limit($items_per_page)->offset(($page - 1) * $items_per_page)->execute();
		$this->template->addresses_pagination = Pagination::factory(array(
			'total_items' => $customer->get('addresses')->where('archived', 'IS', NULL)->count(),
			'items_per_page' => $items_per_page,
			'auto_hide'	=> false,
			'current_page'   => array('source' => 'query_string', 'key' => 'addresses_page'),
		));
		$this->template->customer = $customer;
	
		$data = array(
			'html' => $this->template->render(),
		);
		
		echo json_encode($data);
	}
	
	public function action_delete_communication()
	{
		if ( ! Request::$is_ajax)
		{
			throw new Kohana_Exception('Action only available over AJAX.');
		}
		
		// Load address through customer to avoid any mishaps
		$customer = Model_Customer::load($this->request->param('customer_id'));
		if ( ! $customer->loaded())
		{
			throw new Kohana_Exception('Customer could not be found.');
		}
		$communication = $customer->get('communications')->where('id', '=', $this->request->param('communication_id'))->load();
		
		$communication->delete();
	
		$items_per_page = 20;
		$page = isset($_GET['communications_page']) ? $_GET['communications_page'] : 1;
		
		$this->template->communications = $customer->get('communications')->limit($items_per_page)->offset(($page - 1) * $items_per_page)->execute();
		$this->template->communications_pagination = Pagination::factory(array(
			'total_items' => $customer->get('communications')->count(),
			'items_per_page' => $items_per_page,
			'auto_hide'	=> false,
			'current_page'   => array('source' => 'query_string', 'key' => 'communications_page'),
		));
		$this->template->customer = $customer;
	
		$data = array(
			'html' => $this->template->render(),
		);
		
		echo json_encode($data);
	}
	
	
	public function action_add_contact()
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
			$contact = $customer->add_contact($_POST['contact']);
			$items_per_page = 20;
			$page = isset($_GET['contacts_page']) ? $_GET['contacts_page'] : 1;
			
			$this->template->contacts = $customer->get('contacts')->limit($items_per_page)->offset(($page - 1) * $items_per_page)->execute();
			$this->template->contacts_pagination = Pagination::factory(array(
				'total_items' => $customer->get('contacts')->count(),
				'items_per_page' => $items_per_page,
				'auto_hide'	=> false,
				'current_page'   => array('source' => 'query_string', 'key' => 'contacts_page'),
			));
		}
		catch (Validate_Exception $e)
		{
			$errors['contact'] = $e->array->errors();
		}
		
		$this->template->customer = $customer;
		
		$data = array(
			'html' => $this->template->render(),
			'errors' => $errors,
		);
		
		echo json_encode($data);
	}

	public function action_delete_contact()
	{
		if ( ! Request::$is_ajax)
		{
			throw new Kohana_Exception('Action only available over AJAX.');
		}
		
		// Load address through customer to avoid any mishaps
		$customer = Model_Customer::load($this->request->param('customer_id'));
		if ( ! $customer->loaded())
		{
			throw new Kohana_Exception('Customer could not be found.');
		}
		$contact = $customer->get('contacts')->where('id', '=', $this->request->param('contact_id'))->load();
		
		$contact->delete();
	
		$items_per_page = 20;
		$page = isset($_GET['contacts_page']) ? $_GET['contacts_page'] : 1;
		
		$this->template->contacts = $customer->get('contacts')->limit($items_per_page)->offset(($page - 1) * $items_per_page)->execute();
		$this->template->contacts_pagination = Pagination::factory(array(
			'total_items' => $customer->get('contacts')->count(),
			'items_per_page' => $items_per_page,
			'auto_hide'	=> false,
			'current_page'   => array('source' => 'query_string', 'key' => 'contacts_page'),
		));
		$this->template->customer = $customer;
	
		$data = array(
			'html' => $this->template->render(),
		);
		
		echo json_encode($data);
	}
	
	public function action_delete()
	{
		$this->auto_render = FALSE;
		
		$customer = Model_Customer::load($this->request->param('id'));
		$customer->delete();
		
		$this->request->redirect($this->session->get('admin.customers.index', 'admin/customers'));
	}
	
	public function action_archive()
	{
		$this->auto_render = FALSE;
		
		$customer = Model_Customer::load($this->request->param('id'));
		$customer->archive();
		
		$this->request->redirect($this->session->get('admin.customers.index', 'admin/customers'));
	}
	
	public function action_export_to_sage()
	{
		$this->auto_render = FALSE;
		
		$customer = Model_Customer::load($this->request->param('customer_id'));
		
		if ( ! $customer->loaded())
		{
			throw new Kohana_Exception('Customer not found.');
		}
		
		$data = array(
			$customer->account_ref,				// Account ref
			$customer->company,																// Account name
			$customer->default_billing_address->line_1,				// Address line 1
			$customer->default_billing_address->line_2,				// Address line 2
			$customer->default_billing_address->town,					// Address town
			$customer->default_billing_address->county,				// Address county
			$customer->default_billing_address->postcode,			// Address postcode
			$customer->name(),																// Contact name
			$customer->default_billing_address->telephone,		// Telephone
			'',																								// Fax
		);
		
		$dir_name = APPPATH.'tmp/customer_export/';
		
		if ( ! is_dir($dir_name))
		{
			mkdir($dir_name, 0777, TRUE);
		}
		
		$file_path = $dir_name.$customer->id.'_'.Text::random().'_'.time().'.csv';
		$handle = fopen($file_path, 'w+');
		fputcsv($handle, $data);
		$this->request->send_file($file_path, $customer->account_ref.'.csv', array('delete' => TRUE));
		exit();
	}
}