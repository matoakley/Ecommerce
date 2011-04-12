<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Dashboard extends Controller_Admin_Application {

	function action_index()
	{
		$analytics = Kohanalytics::instance();
		$this->template->visits = $analytics->daily_visit_count();
		
		$latest_orders = Model_Sales_Order::search(array(), 5, array('created' => 'DESC'));
		$this->template->latest_orders = $latest_orders['results'];
		
		$this->template->top_products = Model_Product::most_popular_products(5);
		
		$this->template->monthly_total = Model_Sales_Order::monthly_completed_total();
		$this->template->all_time_total = Model_Sales_Order::overall_completed_total();
	}
	
}