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
			$this->template = 'pages/static/'.$this->request->param('slug', $this->request->param('id', FALSE));
		}
		
		parent::before();
	}
	
	function action_view()
	{	
		$page = Model_Page::get_by_slug($this->request->param('slug'));
		
		if ( ! $page->loaded())
		{
			throw new Kohana_Exception('Page not found');
		}
		$this->template->page = $page;
	
		// build breadcrumb
		if ($page->parent->loaded())
		{
			$this->add_breadcrumb(URL::site(Route::get('view_page')->uri(array('slug' => $page->parent->slug))), $page->parent->name);
		}	
		$this->add_breadcrumb(URL::site(Route::get('view_page')->uri(array('slug' => $page->slug))), $page->name);
	}
	
	function action_static()
	{	
	  $slug = $this->request->param('slug');
	  
		if ($slug == 'home')
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