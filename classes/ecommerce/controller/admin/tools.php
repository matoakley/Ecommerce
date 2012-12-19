<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Tools extends Controller_Admin_Application
{
	public function action_items_per_page($items = FALSE)
	{
		if ( ! Request::$is_ajax)
		{
			throw new Kohana_Exception('Not found', NULL, 404);
		}
		
		$this->auto_render = FALSE;
		
		if ( ! $items)
		{
			$items = Kohana::config('ecommerce.default_admin_list_options');
		}
		
		$this->session->set('admin_list_option', $items);
		
		echo 'Success!';
	}
	
	public function action_bulk_delete()
	{ 
		if ($_POST)
		{   
		
		  if ($_POST['type'] == "Categories")
		      {
  		      $type = 'category';
		      }
		     elseif ($_POST['type'] == "Bundles")
		      {
  		      $type = 'product';
		      }
		        elseif ($_POST['type'] == "System Users")
  		        {
      		      $type = 'user';
      		    }
  		    else
  		      {
    		      $type = substr_replace($_POST['type'] ,"",-1);
    		      $type = str_replace(' ', '_', $type);
    		      $type = strtolower($type);
  		      }
		      
  		try
			{
  			  foreach ($_POST['items'] as $item_id)
				{ 
					$item = Jelly::select($type)->where('id', '=', $item_id)->load();
					
					if ($item->loaded())
					{
  					$item->deleted = time();
  					$item->save();
  				}
				}

		  }
			
			catch (Validate_Exception $e)
			{
			
			}

		}
	}
	
		public function action_bulk_change_status()
	{ 
		if ($_POST)
		{   
  		try
			{
  			  foreach ($_POST['items'] as $item_id)
				{ 
					$item = Jelly::select('sales_order')->where('id', '=', $item_id)->load();
					
					if ($item->loaded())
					{  
					  $status = $_POST['status'];
  					$item->status = $status;
  					$item->save();
  					
  					}
					}

				}
			
			catch (Validate_Exception $e)
			{
			
			}

		}
	}
}
