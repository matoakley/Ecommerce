<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Brands extends Controller_Admin_Application {

	function action_index()
	{
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;
		
		$search = Model_Brand::search(array(), $items);

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items' => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.brands.index', $_SERVER['REQUEST_URI']);
		
		$this->template->brands = $search['results'];
		$this->template->total_brands = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}
	
	function action_edit($id = FALSE)
	{
		$brand = Model_Brand::load($id);
	
		if ($id AND ! $brand->loaded())
		{
			throw new Kohana_Exception('Brand could not be found.');
		}
		
		$redirect_to = $this->session->get('admin.brands.index', 'admin/brands');
		$this->template->cancel_url = $redirect_to;
		
		if ($_POST)
		{
			try
			{
				$brand->update($_POST['brand']);
				
				// If 'Save & Exit' has been clicked then lets hit the index with previous page/filters
				if (isset($_POST['save_exit']))
				{
					$this->request->redirect($redirect_to);
				}
				else
				{
					$this->request->redirect('/admin/brands/edit/' . $brand->id);
				}
			}
			catch (Validate_Exception $e)
			{
				$this->template->errors = $e->array->errors();
			}
		}
		
		// Loads the script that counts chars on the fly for Meta fields.
		$this->scripts[] = 'jquery.counter-1.0.min';
		
		$this->template->brand = $brand;
		$this->template->statuses = Model_Brand::$statuses;
	}
	
	public function action_delete($id = NULL)
	{
		$this->auto_render = FALSE;
		
		$brands = Model_Brand::load($id);
		$brands->delete();
		
		$this->request->redirect($this->session->get('admin.brands.index', 'admin/brands'));
	}
	
}