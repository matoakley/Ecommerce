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
	
	function action_view($slug = FALSE)
	{		
		$page = Model_Page::get_by_slug($slug);
		
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

		if (is_array($slug))
		{
			$slug_parts = $slug;	
		}
		else
		{
			$slug_parts[] = $slug;
		}

		$this->template = Twig::factory('pages/static/'.end($slug_parts), array(), IN_PRODUCTION ? 'production' : 'development');

		if (end($slug_parts) == 'home')
		{
			$this->template->featured_products = Jelly::select('product')
														->where('status', '=', 'active')
														->where('thumbnail_id', 'IS NOT', NULL)
														->order_by(DB::expr('RAND()'))
														->limit(4)								
														->execute();
		}
		
		// build breadcrumb
		// Try to find a route based on the static page name otherwise use the standard /pages/static/<slug> route
		try
		{
			foreach ($slug_parts as $slug_part)
			{
				try
				{
					$page_name = ucwords(Inflector::humanize($slug_part));
					$this->add_breadcrumb(URL::site(Route::get($slug_part)->uri()), $page_name);
				}
				catch (Kohana_Exception $e)
				{
					continue;
				}
			}
		}
		catch (Kohana_Exception $e)
		{
			$this->add_breadcrumb(URL::site(Route::get('view_static_page')->uri(array('slug' => $slug))), $page_name);			
		}
	}
	
}