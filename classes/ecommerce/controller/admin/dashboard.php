<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Dashboard extends Controller_Admin_Application {

	function action_index()
	{
		$analytics = Kohanalytics::instance();
		$this->template->visits = $analytics->daily_visit_count();
		$this->template->top_referrers = $analytics->query('source', 'visits', '-visits', 5);	
	
		$latest_orders = Model_Sales_Order::search(array(), 5, array('created' => 'DESC'));
		$this->template->latest_orders = $latest_orders['results'];
		
		$this->template->top_products = Model_Product::most_popular_products(5);
		
		$this->template->monthly_total = Model_Sales_Order::monthly_completed_total();
		$this->template->all_time_total = Model_Sales_Order::overall_completed_total();
		
		$monthly_totals = array();
		foreach ($analytics->monthly_visit_count() as $month => $visits)
		{
			$monthly_totals[Date::month2string($month)] = array(
				'visits' => $visits,
				'total' => Model_Sales_Order::monthly_completed_total((int)$month)
			);
		}
		$this->template->monthly_visits = $monthly_totals;
	}
	
}