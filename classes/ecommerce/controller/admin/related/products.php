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

	
	/*
public function action_remove_from_wish_lists()
	{
	  		if (! $this->auth->get_user()) 
		{
  		throw new Kohana_Exception("Customer isn't logged in");
		}
		
		$this->auto_render = FALSE;
		
		$product = Model_Product::load($this->request->param('product_slug'));
		
		if ( ! $product->loaded())
		{
			throw new Kohana_Exception('Product not found');
		}	
		
	  $wish_list = Model_Wish_List::remove_watch_item($this->auth->get_user(), $product);
		$this->request->redirect(Route::get('wish_lists')->uri());
	}
	
	public function action_public_wish_list_page()
	{
	  //get the public id from the url
  	$id = $this->request->param('wish_list_id');
  	
  	//find all the items that have that id
  	$wish_list_item_id = Jelly::select('wish_lists')
  	                              ->where('public_identifier', '=', $id)
  	                              ->where('deleted', '=', NULL)
  	                              ->execute();

    $wish_list_items = array();
  
  //for each of them load the model             
  foreach ($wish_list_item_id->as_array() as $key => $id)
    {
      $wish_list_items[$key] = Jelly::select('product')->where('id', '=', intval($id['product_id']))->load();
      
    }
    
    if (! $wish_list_items)
      {
        $this->template->wish_list_items = $wish_list_items;
        $this->add_breadcrumb('/wish-list', "Public Wish List");
      }
      
    else 
      {
        //grab the customers name so we can spit it out on the page
          $user_id = $wish_list_item_id->as_array();
          $user = Jelly::select('customer')->where('user_id', '=', $user_id[0]['user_id'])->load();
          
        //send the appropriate stuff to the view
          $this->template->wish_list_items = $wish_list_items;
          $this->template->users_name = ucwords($user->firstname . ' ' . $user->lastname);
          $this->add_breadcrumb('/wish-list', $this->template->users_name ."'s Public Wish List");
          
      }
  }
	
	
*/
}