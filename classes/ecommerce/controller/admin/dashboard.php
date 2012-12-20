<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Dashboard extends Controller_Admin_Application {

	function action_index()
	{
		// Try/Catch around the Google Analytics call as this sometimes fails
		try
		{
			$analytics = Kohanalytics::instance();
			$this->template->visits = $analytics->daily_visit_count();
			$this->template->top_referrers = $analytics->query('source', 'visits', '-visits', 6);	
						
			$visit_data = $analytics->monthly_visit_count();
			
			$monthly_totals = array();
			foreach ($visit_data as $month => $visits)
			{
				$monthly_totals[Date::month2string($month)] = array(
					'visits' => $visits,
					'total' => Model_Sales_Order::monthly_completed_total((int)$month),
					'orders' => Model_Sales_Order::monthly_sales_orders((int)$month),
					'commerical_orders' => Model_Sales_Order::commercial_monthly_completed_total((int)$month),
					'retail_orders' => Model_Sales_Order::retail_monthly_completed_total((int)$month),
				);
			}
			$this->template->monthly_visits = $monthly_totals;
		}
		catch (Exception $e)
		{
			$this->template->google_api_error = TRUE;
		}
		
		$latest_orders = Model_Sales_Order::recent_dashboard_orders();
		$this->template->latest_orders = $latest_orders;
		
		$this->template->top_products = Model_Product::most_popular_products(5);
		
		$this->template->monthly_total = Model_Sales_Order::monthly_completed_total();
		$this->template->retail_monthly_total = Model_Sales_Order::retail_monthly_completed_total();
		$this->template->commercial_monthly_total = Model_Sales_Order::commercial_monthly_completed_total();
		
		$this->template->all_time_total = Model_Sales_Order::overall_completed_total();
		$this->template->retail_all_time_total = Model_Sales_Order::retail_overall_completed_total();
		$this->template->commercial_all_time_total = Model_Sales_Order::commercial_overall_completed_total();
		
		$this->template->monthly_orders = Model_Sales_Order::monthly_sales_orders();
		$this->template->this_month_sorders = Model_Sales_Order::thismonths_orders();
		$this->template->alltime_orders = Model_Sales_Order::alltime_sales_orders();
		
		$this->template->order_days = Model_Sales_Order::daily_order_count();
		$this->template->thirty = Model_Sales_Order::thirtydays();
		$merged_thirty_orders = Arr::overwrite($this->template->thirty, $this->template->order_days);
		$this->template->merged = array_reverse($merged_thirty_orders);

		
	if (Caffeine::modules('reviews'))
	  {
  	  $this->template->reviews_for_moderation = Model_Review::reviews_to_moderate(5);
	  }
	}
}