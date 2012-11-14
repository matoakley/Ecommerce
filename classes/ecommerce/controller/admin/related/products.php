<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Related_Products extends Controller_Application
{
	public function before()
	{
		parent::before();
		
		if (! Caffeine::modules('related_products'))
		{
  			throw new Kohana_Exception('Related Products module not enabled');
		}	
	}
		
	public function action_add_to_related_products()
	{	
		$this->auto_render = FALSE;
		
		$product = Model_Related_Product::add_to_related_products($_POST['product_id'], $_POST['related_id']);
		
		if (! $product) 
		  {
  		  return json_encode('error');
		  }
		else 
		  {
  		  return json_encode('success');
		  }
	}
	
	public function action_remove_from_related_products()
	{	
		$this->auto_render = FALSE;
		
		$product = Model_Related_Product::remove_from_related_products($_POST['product_id'], $_POST['related_id']);
		
		if (! $product) 
		  {
  		  return json_encode('error');
		  }
		else 
		  {
  		  return json_encode('success');
		  }
	}

}