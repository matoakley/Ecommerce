<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Categories extends Controller_Admin_Application {

	function action_index()
	{
		$items = 25;

		$search = Model_Category::search(array(), $items);

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items'    => $search['count_all'],
			'items_per_page' => $items,
			'auto_hide'	=> false,
		));
		
		$this->template->categories = $search['results'];
		$this->template->total_categories = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}
	
	function action_edit($id = FALSE)
	{
		$category = Model_Category::load($id);
	
		if ($id AND ! $category->loaded())
		{
			throw new Kohana_Exception('Category could not be found.');
		}
		
		if ($_POST)
		{
			try
			{
				$category->update($_POST['category']);
								
				$this->request->redirect('/admin/categories');
			}
			catch (Validate_Exception $e)
			{
				$this->template->errors = $e->array->errors();
			}
		}
		
		// Loads the script that counts chars on the fly for Meta fields.
		$this->scripts[] = 'jquery.counter-1.0.min';
		
		$this->template->category = $category;
		$this->template->statuses = Model_Category::$statuses;
		$this->template->top_level_categories = Model_Category::build_category_tree();
	}
}