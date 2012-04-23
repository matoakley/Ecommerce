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
	}
	
	public function action_index()
	{
		
	}
	
	public function action_view()
	{
		$event = Model_Event::load($this->request->param('event_slug'));
		
		if ( ! $event->loaded())
		{
			throw new Kohana_Exception('Event not found.');
		}
		
		$this->template->event = $event;
	}
}