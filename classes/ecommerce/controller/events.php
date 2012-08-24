<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Events extends Controller_Application
{
	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.events'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
		
		$this->add_breadcrumb(URL::site(Route::get('events')->uri()), 'Events');
	}
		public function action_index()
	{
		$month = $this->request->param('month', date('m'));
		
		$items = 20;
		$page_number = Arr::get($_GET, 'page', 1);	
	
		$events = Model_Event::get_events_by_month($month, $items, ($page_number - 1) * $items);
	
		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items'    => count(Model_Event::get_events_by_month($month, NULL, NULL)),
			'items_per_page' => $items,
		));
	
		$this->template->events = $events;
	
		$calendar_days = array();
		
		// Find out which day of the week the 1st of the month falls on.
		$date = mktime(0, 0, 0, $month, 1, date('Y'));
		
		for ($i = 0; $i < 6; $i++)
		{
			for ($j = 1; $j < 8; $j++)
			{
				$calendar_days[$i][$j]['day_date'] = mktime(12, 0, 0, date('m', $date), date('d', $date) + (($i * 7) + $j) - date('N', $date), date('Y', $date));
				$calendar_days[$i][$j]['class'] = (date('m', $calendar_days[$i][$j]['day_date']) == date('m', $date)) ? 'this_month' : 'other_month';
				$calendar_days[$i][$j]['has_events'] = Jelly::select('event')
																										->where('start_date', '<=', date('Y-m-d', $calendar_days[$i][$j]['day_date']).' 00:00:00')
																										->where('end_date', '>=', date('Y-m-d', $calendar_days[$i][$j]['day_date']).' 00:00:00')
																										->where('status', '=', 'active')
																										->execute();
			}
		}
		$this->template->calendar_title = date("F Y", $date);
		$this->template->calendar_days = $calendar_days;
		
		$tabs = array();
		for ($i = -05; $i < 7; $i++)
		{
			$tabs[date('m')+$i] = date('M y', mktime(0, 0, 0, date('m') + $i, 1, date('Y')));
		}
		
		$this->template->tabs = $tabs;
		$this->template->current_tab = $month;

		
		}	
	public function action_view()
	{
		$event = Model_Event::load($this->request->param('event_slug'));
		if ( ! $event->loaded())
		{
			throw new Kohana_Exception('Event not found.');
		}
		
		$this->template->event = $event;
		
		$this->add_breadcrumb('/event', 'Current Event');
}

}