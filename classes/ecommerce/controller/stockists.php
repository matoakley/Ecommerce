<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Stockists extends Controller_Application
{
	public function before()
	{
		if ( ! Kohana::config('ecommerce.modules.stockists'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
		
		parent::before();
		
		$this->add_breadcrumb(URL::site(Route::get('stockists')->uri()), 'Stockists');
	}

	public function action_index()
	{
		$stockists_search = Model_Stockist::search(array('status:active'));
		
		$this->template->stockists = $stockists_search['results'];
	}
	
	public function action_view()
	{
		$stockist = Model_Stockist::load($this->request->param('slug'));
		
		if ( ! $stockist->loaded())
		{
			throw new Kohana_Exception('The stockist that you are searching for could not be found.');
		}
		
		$this->template->stockist = $stockist;
		
		$this->add_breadcrumb(URL::site(Route::get('view_stockist')->uri(array('slug' => $stockist->slug))), $stockist->name.', '.$stockist->address->town);
	}
	
}