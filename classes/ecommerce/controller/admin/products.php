<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Products extends Controller_Admin_Application {

	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.products'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
	}
	
	function action_index()
	{					
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;

		$search = Model_Product::search(array(), $items);

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items' => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.products.index', $_SERVER['REQUEST_URI']);
		
		$this->template->products = $search['results'];
		$this->template->total_products = $search['count_all'];
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
		
		$fields = array(
			'product' => $product->as_array(),
			'product_categories' => $product->categories->as_array('id', 'id'),
		);
		
		$fields['product']['retail_price'] = $product->retail_price();
		
		foreach ($product->images as $product_image)
		{
			$fields['product_images'][] = $product_image->as_array();
		}
		
		$errors = array();
		
		$redirect_to = $this->session->get('admin.products.index', 'admin/products');
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
					$this->request->redirect('/admin/products/edit/' . $product->id);
				}
			}
			else
			{
				// Otherwise display errors and populate fields with new data
				$fields['product'] = $_POST['product'];
				$fields['product']['retail_price'] = $_POST['product']['price'];
				if (isset($_POST['product_images']))
				{
					foreach ($_POST['product_images'] as $key => $values)
					{
						$fields['product_images'][$key] = $product_image->as_array();
					}
				}
			}
		}
			
		$this->template->errors = $errors;
		$this->template->fields = $fields;
		
		// Loads the script that counts chars on the fly for Meta fields.
		$this->scripts[] = 'jquery.counter-1.0.min';
		
		$this->template->product = $product;
		$this->template->statuses = Model_Product::$statuses;
		$this->template->brands = Model_Brand::list_all();
		$this->template->categories = Model_Category::get_admin_categories(FALSE, FALSE);
		$this->template->product_option_statuses = Model_Product_Option::$statuses;
	}
	
	// Bulk price updater
	public function action_bulk_update_price()
	{
		if ($_POST)
		{
			try
			{
				foreach ($_POST['products'] as $product_id)
				{
					Model_Product::update_price($product_id, $_POST['price']);
				}
			}
			catch (Validate_Exception $e)
			{
			
			}
		}
	}
	
	public function action_delete($id = NULL)
	{
		$this->auto_render = FALSE;
		
		$product = Model_Product::load($id);
		$product->delete();
		
		$this->request->redirect($this->session->get('admin.products.index', 'admin/products'));
	}
	
	public function action_upload_image()
	{	
		$this->auto_render = FALSE;
		
		if ($_FILES)
		{
			$product = Model_Product::load($_POST['product_id']);
			
			if ($product->loaded())
			{
				$image = Model_Product_Image::upload($_FILES['image']['tmp_name'], $product->id);
				
				// If this is the only image for this product then set it as default and thumb.
				if (count($product->images) == 1)
				{
					$product->set_default_image($image->id);
					$product->set_thumbnail($image->id);
				}
				
				// Spit out the result for processing
				echo '<div id="upload-response">';
				echo json_encode($image->as_array());
				echo '</div>';
			}
		}
	}
	
	public function action_delete_image($image_id = FALSE)
	{
		$image = Model_Product_Image::load($image_id);
		$product = $image->product;
		
		if ( ! $image->loaded())
		{
			throw new Kohana_Exception('Image not found');
		}
		
		$image->delete();
		
		if (Request::$is_ajax)
		{
			$data = array(
				'default_image' => $product->default_image->id,
				'thumbnail' => $product->thumbnail->id,
			);
			echo json_encode($data);
		}
		else
		{
			// If it ain't AJAX, send 'em back where they came from.
			$this->request->redirect(Request::$referrer);
		}
		
		exit();
	}
	
	public function action_option_statuses()
	{
		$this->auto_render = FALSE;
		
		echo json_encode(Model_Product_Option::$statuses);
	}
	
	public function action_duplicate($id = FALSE)
	{
		$product = Model_Product::load($id);
		
		if (! $product->loaded())
		{
			throw new Kohana_Exception('Product could not be found.');
		}
		
		$cloned_product = $product->copy();
		
		$this->request->redirect('/admin/products/edit/'.$cloned_product->id);
	}
	
	// Takes a search term and provides search result in 
	// JSON format for live searches
	public function action_live_search()
	{
		$this->auto_render = FALSE;
		
		if (isset($_GET['q']))
		{
			$results = Model_Product::search(array(), 10);
			echo json_encode($results['results']->as_array());
		}
	}
}