<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Events extends Controller_Application
{
	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.events'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
		
		$this->add_breadcrumb(URL::site(Route::get('event')->uri()), 'Event');
	}
	
	function action_index()
	{
  $this->template->events = Jelly::select('event')
									->order_by('start_date', 'ASC')
									->execute();
									
		}
		
   function action_view()
   {
			
		
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
	}

	public function action_delete($id = NULL)
	{
		$this->auto_render = FALSE;
		
		$events = Model_Event::load($id);
		$events->delete();
		
		$this->request->redirect($this->session->get('admin.events.index', 'admin/events'));
	}
	
}
