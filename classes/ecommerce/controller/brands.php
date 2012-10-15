<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Brands extends Controller_Application {

	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.brands'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
	}
	
	function action_index()
	{
		$this->template->brands = Jelly::select('brand')
									->where('status', '=', 'active')
									->order_by('name')
									->execute();;
		
		$this->add_breadcrumb('/brands', 'Brands');
	}

	function action_view()
	{
		$brand = Model_Brand::load($this->request->param('slug'));
	
		if ( ! $brand->loaded())
		{
			throw new Kohana_Exception('The brand that you are searching for could not be found.');
		}
				
		$other_brands = Jelly::select('brand')
									->order_by('name')
									->execute();		
	
		$products = $brand->get('products')
								->where('status', '=', 'active')
								->order_by(DB::expr('RAND()'))
								->where('thumbnail_id', 'IS NOT', NULL)
								->limit(5)								
								->execute();
		
		$this->template->brand = $brand;
		$this->template->other_brands = $other_brands;
		$this->template->products = (count($products) > 0) ? $products : FALSE;
		
		// If a meta description has not been set then we'll build one from the description.
		// Not ideal, but it's better than nothing!
		if ( ! is_null($brand->meta_description) AND $brand->meta_description != '')
		{
			$meta_description = $brand->meta_description;
		}
		else
		{
			$meta_description = substr(strip_tags($brand->description), 0, 160);
		}
		$this->template->meta_description = $meta_description;
		$this->template->meta_keywords = $brand->meta_keywords;
		
		$this->add_breadcrumb('/brands', 'Brands');
		$this->add_breadcrumb('/brands/' . $brand->slug, $brand->name);
	}
	
}