<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Blog_Categories extends Controller_Admin_Application {

	function before()
	{
		if ( ! $this->modules['blog'] OR ! $this->modules['blog_categories'] )
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
	}
	
	public function action_index()
	{
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;

		$search = Model_Blog_Category::search(array(), $items);

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items'    => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.blog_categories.index', $_SERVER['REQUEST_URI']);
		
		$this->template->categories = $search['results'];
		$this->template->total_categories = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}
	
	public function action_edit()
	{
		$category = Model_Blog_Category::load($this->request->param('id'));
	
		if ($this->request->param('id') AND ! $category->loaded())
		{
			throw new Kohana_Exception('Blog Category could not be found.');
		}
		
		$redirect_to = $this->session->get('admin.blog_categories.index', '/admin/blog_categories');
		$this->template->cancel_url = $redirect_to;
		
		$fields = array(
			'category' => $category->as_array(),
		);
		$errors = array();
		
		if ($_POST)
		{
			try
			{
				$category->update($_POST['category']);
								
				// If 'Save & Exit' has been clicked then lets hit the index with previous page/filters
				if (isset($_POST['save_exit']))
				{
					$this->request->redirect($redirect_to);
				}
				else
				{
					$this->request->redirect('/admin/blog_categories/edit/' . $category->id);
				}
			}
			catch (Validate_Exception $e)
			{
				$errors['category'] = $e->array->errors();
				$fields['category'] = $_POST['category'];
			}
		}
		
		$this->template->fields = $fields;
		$this->template->errors = $errors;
		
		// Loads the script that counts chars on the fly for Meta fields.
		$this->scripts[] = 'jquery.counter-1.0.min';
		
		$this->template->category = $category;
		$this->template->statuses = Model_Blog_Category::$statuses;
		$this->template->top_level_categories = Model_Blog_Category::build_category_tree();
	}
	
	public function action_delete($id = NULL)
	{
		$this->auto_render = FALSE;
		
		$categories = Model_Blog_Category::load($id);
		$categories->delete();
		
		$this->request->redirect( $this->session->get('admin.blog_categories.index', 'admin/blog_categories'));
	}
}