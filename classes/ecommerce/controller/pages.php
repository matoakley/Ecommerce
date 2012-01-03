<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Pages extends Controller_Application {

	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.pages'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
		
		if($this->request->action == 'static')
		{
			$this->template = 'pages/static/'.$this->request->param('id');
		}
		
		parent::before();
	}
	
	function action_view($slug = FALSE)
	{		
		$page = Model_Page::get_by_slug($slug);
		
		$this->template->page = $page;
		
		$this->add_breadcrumb('/pages/view/' . $page->slug, $page->name);
	}
	
	function action_static($slug = FALSE)
	{
		if ($slug = 'home')
		{
			$this->template->featured_products = Jelly::select('product')
														->where('status', '=', 'active')
														->where('thumbnail_id', 'IS NOT', NULL)
														->order_by(DB::expr('RAND()'))
														->limit(4)								
														->execute();
		}
	}
	
}