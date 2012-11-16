<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Bundles extends Controller_Admin_Application {

	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.bundles'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
	}
	
	function action_index()
	{
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;
		
		$product = Model_Product::load();
		
		$search = $product->get_admin_bundles();

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items' => count($search),
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.bundles.index', $_SERVER['REQUEST_URI']);
		
		$this->template->bundles = $search;
		$this->template->total_bundles = count($search);
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}
	
	function action_edit($id = FALSE, $cloning = FALSE)
	{
		$product = Model_Product::load($id);
		
		if ($id AND ! $product->loaded())
		{
			throw new Kohana_Exception('Product could not be found.');
		}
		echo Kohana::debug($id, $product);exit;
		$fields = array(
			'product' => $product->as_array(),
			'product_categories' => $product->categories->as_array('id', 'id'),
		);
		if ($this->modules['custom_fields'])
		{
			$fields['custom_fields'] = $product->custom_fields();
		}
		$fields['product']['vat_code'] = $product->vat_code->id;
		
		foreach ($product->skus as $sku)
		{
			$fields['skus'][$sku->id] = $sku->as_array();
			$fields['skus'][$sku->id]['retail_price'] = $sku->retail_price();
			$fields['skus'][$sku->id]['product_options'] = $sku->product_options->as_array();
			foreach ($sku->tiered_prices as $tiered_price)
			{
  			$fields['skus'][$sku->id]['tiered_prices_array'][$tiered_price->price_tier->id] = $tiered_price->retail_price();
			}
		}
  		
		foreach ($product->images as $product_image)
		{
			$fields['product_images'][] = $product_image->as_array();
		}
		
		$errors = array();
		
		$redirect_to = $this->session->get('admin.bundles.index', '/admin/bundles/');
		$this->template->cancel_url = $redirect_to;
		
		  
		if ($_POST)
		{	
				// Try validating the posted data
			try
			{
				$product->validate($_POST['product']);
			}
			catch (Validate_Exception $e)
			{
				$errors['product'] = $e->array->errors();
			}
			
			if (isset($_POST['skus']))
			{
				foreach ($_POST['skus'] as $sku_id => $sku_data)
				{
					$sku = Model_Sku::load($sku_id);
					
					try
					{
						$sku->validate($sku_data);
					}
					catch (Validate_Exception $e)
					{
						$errors['skus'][$sku_id] = $e->array->errors();
					}
				}
			}
						
			// Loop through and validate each of the product options
			if (isset($_POST['product_options']))
			{
				foreach ($_POST['product_options'] as $option_id => $option_data)
				{
					$option = Model_Product_Option::load($option_id);
					
					try
					{
						$option->validate($option_data);
					}
					catch (Validate_Exception $e)
					{
						$errors['product_options'][$option_id] = $e->array->errors();
					}
				}
			}
			
			// Loop through and validate each of the product images
			if (isset($_POST['product_images']))
			{
				foreach ($_POST['product_images'] as $key => $values)
				{
					$image = Model_Product_Image::load($key);
					
					try
					{
						$image->validate($values);
					}
					catch (Validate_Exception $e)
					{
						$errors['product_images'][$key] = $e->array->errors();
					}
				}
			}
			
			// No errors, so let's save the data
			if (empty($errors))
			{
				// Save the product
				$product->update($_POST['product']);
				if ($this->modules['custom_fields'] AND isset($_POST['custom_fields']))
				{
					$product->update_custom_field_values($_POST['custom_fields']);
				}

				// Loop through and save each of the SKUs
				if (isset($_POST['skus']))
				{
					foreach ($_POST['skus'] as $sku_id => $sku_data)
					{
						if (count($_POST['skus']) == 1)
						{
							$sku_data['status'] = 'active';
						}
						
						$sku = Model_Sku::load($sku_id);
						$sku->update($sku_data);
					}
				}
				
				// Loop through and save each of the Product Options
				if (isset($_POST['product_options']))
				{
					foreach ($_POST['product_options'] as $option_id => $option_data)
					{
						$option = Model_Product_Option::load($option_id);
						$option->update($option_data);
					}
				}
				
				// Loop through and save each of the product images
				if (isset($_POST['product_images']))
				{
					foreach ($_POST['product_images'] as $key => $values)
					{
						$image = Model_Product_Image::load($key);
						$image->update($values);
					}
				}
								
				// If 'Save & Exit' has been clicked then lets hit the index with previous page/filters
				if (isset($_POST['save_exit']))
				{
					$this->request->redirect($redirect_to);
				}
				else
				{
					$this->request->redirect('/admin/bundles/edit/' . $product->id);
				}
			}
			else
			{
				// Otherwise display errors and populate fields with new data
				$fields['product'] = $_POST['product'];
				$fields['skus'] = isset($_POST['skus']) ? $_POST['skus'] : array();
				$fields['custom_fields'] = isset($_POST['custom_fields']) ? $_POST['custom_fields'] : array();
				
				if (isset($_POST['skus']))
				{
  				foreach ($_POST['skus'] as $sku_id => $sku)
  				{
  				  if (Caffeine::modules('tiered_prices'))
  				     {
        					foreach($sku['tiered_prices'] as $tier_id => $price)
        					{
        						$fields['skus'][$sku_id]['tiered_prices_array'][$tier_id] = $price;
        					}
        		   }
  				  }
  		  }
				
				if (isset($_POST['product_images']))
				{
					foreach ($_POST['product_images'] as $key => $values)
					{
						$fields['product_images'][$key] = $product_image->as_array();
					}
				}
			}
		}
		$this->template->default_price_includes_vat = Kohana::config('ecommerce.default_price_includes_vat');
		$this->template->errors = $errors;
		$this->template->fields = $fields;
		
		  if (Caffeine::modules('related_products'))
		    {
  		    $this->template->related_products = Model_Related_Product::get_related_products($product->id);
  		    $this->template->products = Model_Product::list_all();
		    }
		    
		$this->template->product = $product;
		$this->template->statuses = Model_Product::$statuses;
		$this->template->inputs = Model_Product::$inputs;
		$this->template->sku_statuses = Model_Sku::$statuses;
		$this->template->brands = Model_Brand::list_all();
		$this->template->categories = Model_Category::get_admin_categories(FALSE, FALSE);
		
		if ($this->modules['tiered_pricing'])
		{
			$this->template->price_tiers = Jelly::select('price_tier')->execute();
		}
		
		if ($this->modules['vat_codes'])
		{
			$this->template->vat_codes = Jelly::select('vat_code')->execute();
		}
	}
	
	public function action_delete($id = NULL)
	{
		$this->auto_render = FALSE;
		
		$bundles = Model_Product::load($id);
		$bundles->delete();
		
		$this->request->redirect($this->session->get('admin.bundles.index', 'admin/bundles/'));
	}
	
	public function action_duplicate($id = FALSE)
	{
		$product = Model_Product::load($id);
		
		if (! $product->loaded())
		{
			throw new Kohana_Exception('Bundle could not be found.');
		}
		
		$cloned_product = $product->copy();
		
		$this->request->redirect('/admin/bundles/edit/'.$cloned_product->id);
	}
	
}