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
  	$items = ($this->list_option != 'all') ? $this->list_option : FALSE;
		
		$order = array();
		$order['start_date'] = 'DESC';
		
		
		$search = Model_Event::search(array(), $items, $order);

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items' => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.events.index', $_SERVER['REQUEST_URI']);
		
		$this->template->events = $search['results'];
		$this->template->total_events = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
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
	}

	public function action_delete($id = NULL)
	{
		$this->auto_render = FALSE;
		
		$events = Model_Event::load($id);
		$events->delete();
		
		$this->request->redirect($this->session->get('admin.events.index', 'admin/events'));
	}
	
}
