<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Snippets extends Controller_Admin_Application {

	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.snippets'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
	}
	
	public function action_index()
	{
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;
		
		$search = Model_Snippet::search(array(), $items);
		
		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items' => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.snippets.index', $_SERVER['REQUEST_URI']);
		
		$this->template->snippets = $search['results'];
		$this->template->total_snippets = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}
	
	public function action_edit($id = FALSE)
	{
		$snippet = Model_Snippet::load($id);
	
		if ($id AND ! $snippet->loaded())
		{
			throw new Kohana_Exception('Snippet could not be found.');
		}
		
		$redirect_to = $this->session->get('admin.snippets.index', 'admin/snippets');
		$this->template->cancel_url = $redirect_to;
		
		if ($_POST)
		{
			try
			{
				$snippet->update($_POST['snippet']);
								
				// If 'Save & Exit' has been clicked then lets hit the index with previous page/filters
				if (isset($_POST['save_exit']))
				{
					$this->request->redirect($redirect_to);
				}
				else
				{
					$this->request->redirect('/admin/snippets/edit/' . $snippet->id);
				}
			}
			catch (Validate_Exception $e)
			{
				$this->template->errors = $e->array->errors();
			}
		}
		
		$this->template->snippet = $snippet;
	}

	public function action_delete($id = NULL)
	{
		$this->auto_render = FALSE;
		
		$pages = Model_Page::load($id);
		$pages->delete();
		
		$this->request->redirect($this->session->get('admin.snippets.index', 'admin/snippets'));
	}
	
}