<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Sales_Orders extends Controller_Admin_Application {

	function action_index()
	{				
		$items = 25;

		$search = Model_Sales_Order::search(array(), $items, array('created' => 'DESC'));

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items'    => $search['count_all'],
			'items_per_page' => $items,
			'auto_hide'	=> false,
		));
		
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
		
		$this->template->sales_order = $sales_order;
		$this->template->order_statuses = Model_Sales_Order::$statuses;
	}
	
}