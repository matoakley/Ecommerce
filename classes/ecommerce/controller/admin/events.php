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
	
	public function action_get_next_logs()
	{
  	if ($_POST)
  	{
  	  $html = array('html' => '');
    	$offset = $_POST['offset'];
    	$limit = $_POST['limit'];
    	$logs_total = Jelly::select('event_logs')->order_by('created', 'DESC')->execute();
    	$logs = Jelly::select('event_logs')->order_by('created', 'DESC')->offset($offset)->limit($limit)->execute();
    	
    	foreach ($logs as $log)
    	{
    	  $html['html'] .= '<div class="log-panel-item">'.$log['created'] .' - '. $log['change'].'</div>';
    	}
      $html['max'] = count($logs_total);
    	
    	echo json_encode($html);
  	}
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
	
	public function action_get_events()
  {
    if (isset($_GET['start']) && isset($_GET['end']))
    {
      $results = array();
      $key = 0;
      $events = Jelly::select('event')->where('start_date', '>=', date('Y-m-d H:i:s', $_GET['start']))->where('end_date', '<=', date('Y-m-d H:i:s', $_GET['end']))->execute();
      $bookings = Jelly::select('sales_order')->where('type', '=', 'booking')->where('deleted', '=', NULL)->where('status', 'NOT IN', array('order_cancelled', 'problem_occured'))->execute();
      
      foreach ($events as $key => $event)
      {
        $results[$key]['start'] = $event->start_date;
        $results[$key]['end'] = $event->end_date;
        $results[$key]['title'] = ucfirst($event->name);
        $results[$key]['event_id'] = $event->id;
        $results[$key]['description'] = $event->description;
        $results[$key]['color'] = Model_Event::$types[$event->event_type ? $event->event_type : 'Other'];
      }
      $key++;
      foreach ($bookings as $event)
      {
        $results[$key]['start'] = $event->return_earliest_function_date($event->id,'from');
        $results[$key]['end'] = $event->return_earliest_function_date($event->id,'to');
        $results[$key]['title'] = ucfirst($event->customer->name());
        $results[$key]['booking_id'] = $event->id;
        $results[$key]['allday'] = FALSE;
        $results[$key]['description'] = Model_Sales_Order_Item::load($event->return_earliest_function_item_id($event->id,'to'))->sku->product->name;
        $results[$key]['color'] = Model_Event::$types['Booking'];
        $key++;
      }
      
      foreach ($bookings as $event)
      {
        if ($event->deposit_date != NULL)
        {
          $results[$key]['start'] = $event->deposit_date;
          $results[$key]['end'] = $event->deposit_date;
          $results[$key]['title'] = ucfirst($event->customer->name()) .' Deposit Due';
          $results[$key]['editable'] = FALSE;
          $results[$key]['payment_id'] = $event->id;
          $results[$key]['description'] = ucfirst($event->customer->name()) .' Deposit Due '. date('d/m/Y', $event->deposit_date);
          $results[$key]['color'] = Model_Event::$types['Payment'];
          $key++;
        }
        if ($event->remaining_date != NULL)
        {
          $results[$key]['start'] = $event->remaining_date;
          $results[$key]['end'] = $event->remaining_date;
          $results[$key]['title'] = ucfirst($event->customer->name()) .' Balance Due';
          $results[$key]['editable'] = FALSE;
          $results[$key]['payment_id'] = $event->id;
          $results[$key]['description'] = ucfirst($event->customer->name()) .' Balance Due '. date('d/m/Y', $event->remaining_date);
          $results[$key]['color'] = Model_Event::$types['Payment'];
          $key++;
        }
        if ($event->damages_date != NULL)
        {
          $results[$key]['start'] = $event->damages_date;
          $results[$key]['end'] = $event->damages_date;
          $results[$key]['title'] = ucfirst($event->customer->name()) .' Damages Due';
          $results[$key]['editable'] = FALSE;
          $results[$key]['payment_id'] = $event->id;
          $results[$key]['description'] = ucfirst($event->customer->name()) .' Damages Due '. date('d/m/Y', $event->damages_date);
          $results[$key]['color'] = Model_Event::$types['Payment'];
          $key++;
        }
      }
      
      echo json_encode($results);
    }
  }
  
  public function action_get_events_for_month()
  {
    if (isset($_GET['start']) && isset($_GET['end']))
    {
      //if no tpye specified false to get all.
      $type = FALSE;
      if (isset($_GET['type']))
      {
        $type = $_GET['type'];
      }
      
      $results = '';
      $key = 0;
      $bookings = array();
      
      $events = Jelly::select('event')->where('start_date', '>=', date('Y-m-d H:i:s', $_GET['start']))->where('end_date', '<=', date('Y-m-d H:i:s', $_GET['end']))->execute();
      $all_bookings = Jelly::select('sales_order')->where('type', '=', 'booking')->where('deleted', '=', NULL)->where('status', 'NOT IN', array('order_cancelled', 'problem_occured'))->execute();
      
      foreach ($all_bookings as $booking)
      {
        $item = Model_Sales_Order_Item::load($booking->return_earliest_function_item_id($booking->id, 'from'));
        if ($item->loaded() && $item->from_date >= $_GET['start'] && $item->to_date <= $_GET['end'])
        {
          $bookings[] = $booking;
        }
      }
      
      if ($type == FALSE || $type == 'events')
      {
        //use events... well because they're events duuh.
        foreach ($events as $key => $event)
        {
          $results .= '<a href="/admin/events/edit/'.$event->id.'"><div class="panel-item" style="color:'.Model_Event::$types[$event->event_type ? $event->event_type : 'Other'].'">' .date('d/m/Y', $event->start_date) . '-' .date('d/m/Y', $event->end_date).' - '. ucfirst($event->name) . '</div></a>';
        }
      }
      
      if ($type == FALSE || $type == 'bookings')
      {
        //use bookings because we have narrowed down the date already
        foreach ($bookings as $event)
        {
          $results .= '<a href="/admin/bookings/view/'.$event->id.'"><div class="panel-item" style="color:'.Model_Event::$types['Booking'].'">'.date('d/m/Y', $event->return_earliest_function_date($event->id,'from')) . '-' .date('d/m/Y', $event->return_earliest_function_date($event->id,'to')).' - '. ucfirst($event->customer->name()) .' Booking. </div></a>';
        }
      }
      
      if ($type == FALSE || $type == 'payments')
      {
        //use all bookings because we check the dates in here
        foreach ($all_bookings as $event)
        {
          if ($event->deposit_date != NULL && $event->deposit_date >= $_GET['start'] && $event->deposit_date <= $_GET['end'])
          {
            $results .= '<a href="/admin/bookings/view/'.$event->id.'"><div class="panel-item" style="color:'.Model_Event::$types['Payment'].'">'.date('d/m/Y', $event->deposit_date).' - '. ucfirst($event->customer->name()) .' Damages Due'.'</div></a>';
          }
          if ($event->remaining_date != NULL && $event->remaining_date >= $_GET['start'] && $event->remaining_date <= $_GET['end'])
          {
            $results .= '<a href="/admin/bookings/view/'.$event->id.'"><div class="panel-item" style="color:'.Model_Event::$types['Payment'].'">'.date('d/m/Y', $event->remaining_date).' - '. ucfirst($event->customer->name()) .' Damages Due'.'</div></a>';
          }
          if ($event->damages_date != NULL && $event->damages_date >= $_GET['start'] && $event->damages_date <= $_GET['end'])
          {
            $results .= '<a href="/admin/bookings/view/'.$event->id.'"><div class="panel-item" style="color:'.Model_Event::$types['Payment'].'">'.date('d/m/Y', $event->damages_date).' - '. ucfirst($event->customer->name()) .' Damages Due'.'</div></a>';
          }
  
        }
      }
      
      echo json_encode($results);
    }
  }
  
  public function action_change_date()
  {
    //if its an event.
    if (isset($_POST['event_id']))
    { 
      //declare some variables son!
      $overlap = FALSE;
      $event = Jelly::select('event')->where('id', '=', $_POST['event_id'])->load();
      $days = $_POST['days'] < 0 ? $_POST['days'] : '+' . $_POST['days'];
      $bookings = Jelly::select('sales_order')->where('type', '=', 'booking')->where('deleted', '=', NULL)->where('status', '<>', 'order_cancelled')->execute();
      
      //if we have an event lets do it!
      if ($event->loaded())
      {
        //get the originals for the message.
        $original_from = $event->start_date;
        $original_to = $event->end_date;
        
        //save the variables to save code.
        $start_date = strtotime(date('d-m-Y', $event->start_date).' '.$days.' days');
        $end_date = strtotime(date('d-m-Y', $event->end_date).' '.$days.' days');
        
        //find the bookings and compare the dates.
        foreach($bookings as $booking)
        {
          $from = $booking->return_earliest_function_date($booking->id, $type = 'from');
          $to = $booking->return_earliest_function_date($booking->id, $type = 'to');
          
          if ($start_date >= $from && $start_date <= $to || $end_date >= $from && $end_date <= $to && $event->type == 'Viewing')
          {
            $overlap = TRUE;
          }
        }
        
        //if the overlap is false then go ahead, also if the overlap post is set do it anyway
        if ($overlap == FALSE && !isset($_POST['overlap']) || $overlap == TRUE && isset($_POST['overlap']))
        {
          $event->start_date = $start_date;
          $event->end_date = $end_date;
          $event->save();
        
          Model_Event_Log::create($event, '"'.$event->name.'"' . ' was changed from, ' . date('d/m/Y',$original_from) . '-' . date('d/m/Y',$original_to) . ' to ' . date('d/m/Y',$event->start_date) . '-' . date('d/m/Y',$event->end_date));
          
          echo json_encode('OK');
        }
        else
        {
          echo json_encode(array('overlap' => TRUE));
        }
      }
    }
    
    //if its a booking.
    if (isset($_POST['booking_id']))
    {     
      //declare some variables son!
      $overlap = FALSE;
      $sales_order = Jelly::select('sales_order')->where('id', '=', $_POST['booking_id'])->load();
      $item = Model_Sales_Order_Item::load($sales_order->return_earliest_function_item_id($_POST['booking_id'], $type = 'from'));
      $days = $_POST['days'] < 0 ? $_POST['days'] : '+' . $_POST['days'];
      $events = Jelly::select('event')->where('deleted', '=', NULL)->where('event_type', '=', 'Viewing')->execute();
      
      //if our booking is loaded (item)
      if ($item->loaded())
      { 
        //grab the old dates for the message.
        $old_to_date = $item->to_date;
        $old_from_date = $item->from_date;
        $start_date = strtotime(date('d-m-Y', $item->from_date).' '.$days.' days');
        $end_date = strtotime(date('d-m-Y', $item->to_date).' '.$days.' days');
        
        //find the viewings and compare the dates.
        foreach($events as $event)
        {
          $from = $event->start_date;
          $to = $event->end_date;
          
          if ($start_date >= $from && $start_date <= $to || $end_date >= $from && $end_date <= $to && $event->type == 'Viewing')
          {
            $overlap = TRUE;
          }
        }
        
         //if the overlap is false then go ahead, also if the overlap post is set do it anyway
        if ($overlap == FALSE && !isset($_POST['overlap']) || $overlap == TRUE && isset($_POST['overlap']))
        {
          $item->from_date = $start_date;
          $item->to_date = $end_date;
          $item->save();
        
          Model_Event_Log::create(NULL, '"'.$sales_order->customer->name().'" Booking was changed from, ' . date('d/m/Y',$old_from_date) . '-' . date('d/m/Y',$old_to_date) . ' to ' . date('d/m/Y',$item->from_date) . '-' . date('d/m/Y',$item->to_date));
          $item->sales_order->add_note('The dates were changed from ' . date('d/m/Y', $old_from_date) .'-'. date('d/m/Y', $old_to_date) .' to ' . date('d/m/Y', $item->from_date) .'-'. date('d/m/Y', $item->to_date), FALSE);
        
          echo json_encode('OK');
        }
        else
        {
          echo json_encode(array('overlap' => TRUE));
        }
      }
    }
  }
  
  public function action_change_period()
  {
    if (isset($_POST['event_id']))
    {     
      $event = Jelly::select('event')->where('id', '=', $_POST['event_id'])->load();
      $days = $_POST['days'] < 0 ? $_POST['days'] : '+' . $_POST['days'];

      if ($event->loaded())
      {
        $original_to = $event->end_date;
        $event->end_date = strtotime(date('d-m-Y', $event->end_date).' '.$days.' days');
        $event->save();
        
        Model_Event_Log::create($event, '"'.$event->name.'"' . ' "to" date was changed from, ' .date('d/m/Y',$original_to) . ' to ' . date('d/m/Y',$event->end_date));
      }
      
      echo json_encode('OK');
    }
    
    if (isset($_POST['booking_id']))
    {     
      $event = Jelly::select('sales_order')->where('id', '=', $_POST['booking_id'])->load();
      $item = Model_Sales_Order_Item::load($event->return_earliest_function_item_id($_POST['booking_id'], $type = 'from'));
      $days = $_POST['days'] < 0 ? $_POST['days'] : '+' . $_POST['days'];

      if ($item->loaded())
      {
        $old_date = $item->to_date;
        $item->to_date = strtotime(date('d-m-Y', $item->to_date).' '.$days.' days');
        $item->save();
        
         Model_Event_Log::create(NULL, '"'.$event->customer->name().'" "to" date was changed from, ' . date('d/m/Y',$old_date) . ' to ' . date('d/m/Y',$item->to_date));
        $item->sales_order->add_note('The "to" date was changed from ' . date('d/m/Y', $old_date) . ' to ' . date('d/m/Y', $item->to_date), FALSE);
      }
      
      echo json_encode('OK');
    }

  }
  
  public function action_add_new()
  {
    if ($_POST)
    { 
      $timestamp = $_POST['date'];
      $event = Jelly::factory('event');
      $event->start_date = ($_POST['date']/1000);
      $event->end_date = ($_POST['date']/1000);
      $event->name = isset($_POST['name']) && $_POST['name'] != '' ? $_POST['name'] : 'New Event';
      $event->event_type = isset($_POST['type']) && $_POST['type'] != '' ? $_POST['type'] : 'Other';
      $event->description = isset($_POST['name']) && $_POST['name'] != '' ? $_POST['name'] : 'New Event';
      $event->save();
      
      echo json_encode('all gravy');
    }
  }
	
}
