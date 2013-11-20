<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Events extends Controller_Admin_Application
{
	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.events'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
		
	}
	
	function action_index()
	{
	  $month = $this->request->param('month', date('m'));
		
		$items = 20;
		$page_number = Arr::get($_GET, 'page', 1);
    // Find out which day of the week the 1st of the month falls on.
		$date = mktime(0, 0, 0, $month, 1, date('Y'));
		
		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items'    => count(Model_Event::get_events_by_month($date, NULL, NULL)),
			'items_per_page' => $items,
		));
	
		$calendar_days = array();
		
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
																										->where('deleted', '=', NULL)
																										->execute();
			}
		}
		
		$events = Model_Event::get_events_by_month($date, $items, ($page_number - 1) * $items);
		
		$this->template->calendar_title = date("F Y", $date);
		$this->template->calendar_days = $calendar_days;
		$this->template->events = $events;
		
		$tabs = array();
		for ($i = -05; $i < 7; $i++)
		{
			$tabs[date('m')+$i] = date('M y', mktime(0, 0, 0, date('m') + $i, 1, date('Y')));
		}
		
		$this->template->tabs = $tabs;
		$this->template->current_tab = $month;
		$this->template->breadcrumbs = NULL;
		$this->template->types = Model_Event::$types;
		
		//products for the prices panel
		$this->template->barn_products = Jelly::select('sku')->where('venue', '=', 'the_barn')->where('deleted', '=', NULL)->where('status', '=', 'active')->execute();
		$this->template->hall_products = Jelly::select('sku')->where('venue', '=', 'the_hall')->where('deleted', '=', NULL)->where('status', '=', 'active')->execute();
		$this->template->hall_barn_products = Jelly::select('sku')->where('venue', '=', 'the_hall_and_barn')->where('deleted', '=', NULL)->where('status', '=', 'active')->execute();
		
		//logs
		$this->template->event_logs = Jelly::select('event_logs')->order_by('created', 'DESC')->execute();
		
  	$items = ($this->list_option != 'all') ? $this->list_option : FALSE;
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

	public function action_edit()
	{
		$event = Model_Event::load($this->request->param('id'));
	
		if ($this->request->param('id') AND ! $event->loaded())
		{
			throw new Kohana_Exception('Event could not be found.');
		}
		
		$redirect_to = $this->session->get('admin.events.index', '/admin/events');
		$this->template->cancel_url = $redirect_to;
		
		$fields = array(
			'event' => $event->as_array(),
			'address' => $event->address->as_array(),
		);
		$errors = array();
		if (isset($_POST['event']))
			{
				try
				{
					Model_Event::event_validator($_POST['event']);
				}
				catch (Validate_Exception $e)
				{
				$errors['event'] = $e->array->errors();
				$fields['event'] = $_POST['event'];
			}
			try
			{
				$event->address->validate($_POST['address']);
			}
			catch (Validate_Exception $e)
			{
				$errors['address'] = $e->array->errors();
			}
			if (empty($errors))
			{
				$event->address->update($_POST['address']);
				$_POST['event']['address'] = $event->address;
				$event->update($_POST['event']);
				
				// If 'Save & Exit' has been clicked then lets hit the index with previous page/filters
				if (isset($_POST['save_exit']))
				{
					$this->request->redirect($redirect_to);
				}
				else
				{
					$this->request->redirect('/admin/events/edit/' . $event->id);
				}
				}
     }
		
		$this->template->fields = $fields;
		$this->template->statuses = Model_Event::$statuses;
		$this->template->categories = Model_Event_Category::get_admin_categories(FALSE, FALSE);
		$this->template->errors = $errors;
		$this->template->event = $event;
		$this->template->types = Model_Event::$types;
	}

	public function action_delete($id = NULL)
	{
		$this->auto_render = FALSE;
		
		$events = Model_Event::load($id);
		$events->delete();
		
		$this->request->redirect($this->session->get('admin.events.index', 'admin/events'));
	}	
}
