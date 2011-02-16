<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Pages extends Controller_Admin_Application {

	public function action_index()
	{
		$items = 25;
		
		$search = Model_Page::search(array(), $items);
		
		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items'    => $search['count_all'],
			'items_per_page' => $items,
			'auto_hide'	=> false,
		));
		
		$this->template->pages = $search['results'];
		$this->template->total_pages = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}
	
	public function action_edit($id = FALSE)
	{
		$page = Model_Page::load($id);
		
		if ( ! $page->loaded())
		{
			$page->template = 'default';
		}
	
		if ($id AND ! $page->loaded())
		{
			throw new Kohana_Exception('Page could not be found.');
		}
		
		if ($_POST)
		{
			try
			{
				$page->update($_POST['page']);
								
				$this->request->redirect('/admin/pages');
			}
			catch (Validate_Exception $e)
			{
				$this->template->errors = $e->array->errors();
			}
		}
		
		// Loads the script that counts chars on the fly for Meta fields.
		$this->scripts[] = 'jquery.counter-1.0.min';
		
		$this->template->page = $page;
		$this->template->statuses = Model_Page::$statuses;
	}

	public function action_delete($id = NULL)
	{
		$this->auto_render = FALSE;
		
		$pages = Model_Page::load($id);
		$pages->delete();
		
		$this->request->redirect('admin/pages');
	}
	
}