<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Pages extends Controller_Admin_Application
{
	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.pages'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
	}
	
	public function action_index()
	{
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;
		
		$search = Model_Page::search(array(), $items);
		
		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items' => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.pages.index', $_SERVER['REQUEST_URI']);
		
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
		
		$redirect_to = $this->session->get('admin.pages.index', '/admin/pages');
		$this->template->cancel_url = $redirect_to;
		
		if ($_POST)
		{
			try
			{
				$page->update($_POST['page']);
								
				// If 'Save & Exit' has been clicked then lets hit the index with previous page/filters
				if (isset($_POST['save_exit']))
				{
					$this->request->redirect($redirect_to);
				}
				else
				{
					$this->request->redirect('/admin/pages/edit/' . $page->id);
				}
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
		$this->template->top_level_pages = Model_Page::build_page_tree();
	}

	public function action_delete($id = NULL)
	{
		$this->auto_render = FALSE;
		
		$pages = Model_Page::load($id);
		$pages->delete();
		
		$this->request->redirect($this->session->get('admin.pages.index', 'admin/pages'));
	}
	
}