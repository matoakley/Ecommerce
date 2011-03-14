<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Products extends Controller_Admin_Application
{
	function action_index()
	{					
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;

		$search = Model_Product::search(array(), $items);

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items'    => $search['count_all'],
			'auto_hide'	=> false,
		));
		
		if ($items)
		{
			$this->template->pagination->items_per_page = $items;
		}
		
		$this->template->products = $search['results'];
		$this->template->total_products = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}
	
	function action_edit($id = FALSE)
	{
		$product = Model_Product::load($id);
	
		if ($id AND ! $product->loaded())
		{
			throw new Kohana_Exception('Product could not be found.');
		}
		
		if ($_POST)
		{
			try
			{
				$product->update($_POST['product']);
				
				if (array_key_exists('images', $_POST))
				{
					foreach ($_POST['images'] as $key => $data)
					{
						$image = Model_Product_Image::load($key);
						$image->update($data);
					}
				}
				
				if (array_key_exists('delete_images', $_POST))
				{
					foreach ($_POST['delete_images'] as $image_id)
					{
						$image = Model_Product_Image::load($image_id);
						$image->delete();
					}
				}
				
				$this->request->redirect('/admin/products');
			}
			catch (Validate_Exception $e)
			{
				$this->template->errors = $e->array->errors();
			}
		}
		
		// Loads the script that counts chars on the fly for Meta fields.
		$this->scripts[] = 'jquery.counter-1.0.min';
		
		$this->template->product = $product;
		$this->template->statuses = Model_Product::$statuses;
		$this->template->brands = Model_Brand::list_all();
		$this->template->product_categories = $product->categories->as_array('id', 'name');
		$this->template->categories = Model_Category::get_admin_categories(FALSE, FALSE);
	}
	
	public function action_edit_image($id = NULL)
	{
		$image = Model_Product_Image::load($id);
		
		$this->template->image = $image;
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
		
		$this->request->redirect('admin/products');
	}
	
}