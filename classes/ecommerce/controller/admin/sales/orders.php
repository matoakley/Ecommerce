<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Sales_Orders extends Controller_Admin_Application {

	function action_index()
	{				
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;

		$search = Model_Sales_Order::search(array(), $items, array('created' => 'DESC'));

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
	
		if ($sales_order->loaded() AND $sales_order->status == 'payment_received')
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
}