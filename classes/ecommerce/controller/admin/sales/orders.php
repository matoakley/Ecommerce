<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Sales_Orders extends Controller_Admin_Application {

	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.sales_orders'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
	}
	
	function action_index()
	{				
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;

		$filter_by_type = (isset($_GET['type']) AND $_GET['type'] != '') ? 'type:'.$_GET['type'] : '';
		$filter_by_status = (isset($_GET['status']) AND $_GET['status'] != '') ? 'status:'.$_GET['status'] : '';

		$search = Model_Sales_Order::search(array($filter_by_type, $filter_by_status), $items, array('created' => 'DESC'));

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items'  => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.sales_orders.index', $_SERVER['REQUEST_URI']);
		
		$this->template->sales_orders = $search['results'];
		$this->template->total_products = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
		$this->template->types = Model_Sales_Order::$types;
		$this->template->filtered_by_type = (isset($_GET['type']) AND $_GET['type'] != '') ? $_GET['type'] : FALSE;
		$this->template->statuses = Model_Sales_Order::$statuses;
		$this->template->filtered_by_status = (isset($_GET['status']) AND $_GET['status'] != '') ? $_GET['status'] : FALSE;

	}
	
	function action_view($id = FALSE)
	{
		$sales_order = Model_Sales_Order::load($id);
	
		if ($id AND ! $sales_order->loaded())
		{
			throw new Kohana_Exception('Sales Order could not be found.');
		}
		
		$redirect_to = $this->session->get('admin.sales_orders.index', 'admin/sales_orders');
		$this->template->cancel_url = $redirect_to;
		
		if ($_POST)
		{
			// Only update the status if it has actually changed
			if ($sales_order->status != $_POST['sales_order']['status'])
			{
				$sales_order->update_status($_POST['sales_order']['status']);
			}
			
			// If 'Save & Exit' has been clicked then lets hit the index with previous page/filters
			if (isset($_POST['save_exit']))
			{
				$this->request->redirect($redirect_to);
			}
			else
			{
				$this->request->redirect('/admin/sales_orders/view/' . $sales_order->id);
			}
		}
		
		$this->template->sales_order = $sales_order;
		$this->template->order_statuses = Model_Sales_Order::$statuses;
	}
	
	public function action_complete_and_send_email($id = FALSE)
	{
		$this->auto_render = FALSE;
	
		$sales_order = Model_Sales_Order::load($id);
	
		if ($sales_order->loaded() AND in_array($sales_order->status, array('payment_received', 'invoice_generated')))
		{
			$sales_order->update_status('complete')->send_shipped_email();
			echo 'ok';
		}
		else
		{
			echo 'error';
		}
		
		exit;
	}
	
	public function action_add_note()
	{
		$this->auto_render = FALSE;
		
		if ($_POST)
		{
			$sales_order = Model_Sales_Order::load($_POST['sales_order']);
			$note = $sales_order->add_note($_POST['note']);
			
			$data = array(
				'user' => $note->user->firstname . ' ' . $note->user->lastname,
				'text' => $note->text,
				'created' => date('d/m/Y H:i', $note->created),
			);
			
			echo json_encode($data);
		}
		
		exit;
	}
	
	// Bulk ship and email
	public function action_bulk_ship_and_email()
	{
		if ($_POST)
		{
			try
			{
				foreach ($_POST['sales_orders'] as $sales_order_id)
				{
					$sales_order = Model_Sales_Order::load($sales_order_id);
					
					if ($sales_order->status == 'payment_received')
					{
						$sales_order->update_status('complete')->send_shipped_email();
					}
				}
			}
			catch (Validate_Exception $e)
			{
			
			}
		}
	}

	public function action_new()
	{
		if ( ! $this->modules['commercial_sales_orders'])
		{
			throw new Kohana_Exception('Module is not enabled.');
		}
		
		$sales_order = Model_Sales_Order::load($this->request->param('id'));
	
		if ($this->request->param('id') AND ! $sales_order->loaded())
		{
			throw new Kohana_Exception('Sales Order could not be found.');
		}

		$customer_id = isset($_GET['customer']) ? $_GET['customer'] : NULL;
		$customer = Model_Customer::load($customer_id);
			
		if ( ! $customer->loaded())
		{
			throw new Kohana_Exception('Unable to load Customer.');
		}
			
		$this->template->customer = $customer;
			
		$items_per_page = 5;
		$page = isset($_GET['addresses_page']) ? $_GET['addresses_page'] : 1;
		
		$this->template->addresses = $customer->get('addresses')->order_by('created', 'DESC')->limit($items_per_page)->offset(($page - 1) * $items_per_page)->execute();
		$this->template->addresses_pagination = Pagination::factory(array(
			'total_items' => $customer->get('addresses')->count(),
			'items_per_page' => $items_per_page,
			'auto_hide'	=> false,
			'current_page'   => array('source' => 'query_string', 'key' => 'addresses_page'),
			'view' => 'pagination/asynchronous',
		));
		
		$fields = array(
			'sales_order' => array(
				'delivery_address' => $customer->default_shipping_address->id,
				'delivery_charge' => 0,
			),
		);
		$errors = array();
		
		if ($_POST)
		{
			try
			{
				$sales_order->validate($_POST['sales_order']);
			}
			catch (Validate_Exception $e)
			{
				$errors['sales_order'] = $e->array->errors();
			}
			
			if (empty($errors))
			{
				$sales_order = Model_Sales_Order::create_commercial_sales_order($_POST['sales_order']);
				
				// If 'Save & Exit' has been clicked then lets hit the index with previous page/filters
				if (isset($_POST['save_exit']))
				{
					$this->request->redirect($redirect_to);
				}
				else
				{
					$this->request->redirect('/admin/sales_orders/view/'.$sales_order->id);
				}
			}
			
			$fields = $_POST;
		}
		
		$sales_order_total = 0;	
		if (isset($fields['sales_order']['skus']))
		{
			foreach ($fields['sales_order']['skus'] as $sales_order_line)
			{
				$sales_order_total += ($sales_order_line['price'] * $sales_order_line['quantity']);
			}
		}
		$fields['sales_order_total'] = $sales_order_total;
		
		$this->template->fields = $fields;
		$this->template->errors = $errors;
			
		$this->template->countries = Model_Country::list_active();
		$this->template->skus = Model_Sku::list_all();
		
		$this->template->default_vat = Kohana::config('ecommerce.vat_rate');
	}
	
	public function action_add_sales_order_line()
	{	
		$customer = Model_Customer::load($this->request->param('customer_id'));
		$sku = Model_Sku::load($this->request->param('sku_id'));
		
		if ( ! $customer->loaded())
		{
			throw new Kohana_Exception('Customer not found.');
		}
		
		if (! $sku->loaded())
		{
			throw new Kohana_Exception('SKU not found.');
		}
		
		$data = array();

		$data['html'] = Twig::factory('admin/sales/orders/_add_sales_order_line.html', array(
			'modules' => $this->modules,
			'sku' => $sku,
			'customer' => $customer,
		))->render();
		$data['sku'] = $sku->as_array();
		
		echo json_encode($data);
	}
	
	public function action_new_sales_order_addresses()
	{
		$customer = Model_Customer::load($_GET['customer']);
		
		$items_per_page = 5;
		$page = isset($_GET['addresses_page']) ? $_GET['addresses_page'] : 1;
	
		$this->template->addresses = $customer->get('addresses')->order_by('created', 'DESC')->limit($items_per_page)->offset(($page - 1) * $items_per_page)->execute();
		$this->template->addresses_pagination = Pagination::factory(array(
			'total_items' => $customer->get('addresses')->count(),
			'items_per_page' => $items_per_page,
			'auto_hide'	=> false,
			'current_page'   => array('source' => 'query_string', 'key' => 'addresses_page'),
			'view' => 'pagination/asynchronous',
		));
		
		$this->template->customer = $customer;
		
		$data = array(
			'html' => $this->template->render(),
		);
		
		echo json_encode($data);
	}
}