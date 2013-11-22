<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Event_Categories extends Controller_Admin_Application {

	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.categories'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
	}
	
	public function action_index()
	{
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;

		$search = Model_Event_Category::search(array(), $items);

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items'    => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.events_categories.index', $_SERVER['REQUEST_URI']);
		
		$this->template->categories = $search['results'];
		$this->template->total_categories = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}
	
	public function action_edit($id = FALSE)
	{
		$category = Model_Event_Category::load($id);
	
		if ($id AND ! $category->loaded())
		{
			throw new Kohana_Exception('Category could not be found.');
		}
		
		$redirect_to = $this->session->get('admin.event_categories.index', '/admin/event_categories');
		$this->template->cancel_url = $redirect_to;
		
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
					$this->request->redirect('/admin/event_categories/edit/' . $category->id);
				}
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
		$this->template->top_level_categories = Model_Event_Category::build_category_tree();
	}
	
	public function action_delete($id = NULL)
	{
		$this->auto_render = FALSE;
		
		$categories = Model_Event_Category::load($id);
		$categories->delete();
		
		$this->request->redirect( $this->session->get('admin.event_categories.index', 'admin/event_categories'));
	}

}