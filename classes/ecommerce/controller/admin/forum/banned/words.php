<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Forum_Banned_Words extends Controller_Admin_Application
{	
	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.forums'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
	}

	public function action_index()
	{
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;

		$search = Model_Forum_Banned_Word::search(array(), $items);

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items'    => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.forum_banned_words.index', $_SERVER['REQUEST_URI']);
		
		$this->template->forum_banned_words = $search['results'];
		$this->template->total_forum_banned_words = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}
	
	public function action_edit($id = FALSE)
	{
		$banned_word = Model_Forum_Banned_Word::load($id);
	
		if ($id AND ! $banned_word->loaded())
		{
			throw new Kohana_Exception('Banned Word could not be found.');
		}
		
		$redirect_to = $this->session->get('admin.forum_banned_words.index', '/admin/forum_banned_words');
		$this->template->cancel_url = $redirect_to;
		
		if ($_POST)
		{
			try
			{
				$banned_word->update($_POST['banned_word']);
								
				// If 'Save & Exit' has been clicked then lets hit the index with previous page/filters
				if (isset($_POST['save_exit']))
				{
					$this->request->redirect($redirect_to);
				}
				else
				{
					$this->request->redirect('/admin/forum_banned_words/edit/' . $banned_word->id);
				}
			}
			catch (Validate_Exception $e)
			{
				$this->template->errors = $e->array->errors();
			}
		}
		
		// Loads the script that counts chars on the fly for Meta fields.
		$this->scripts[] = 'jquery.counter-1.0.min';
		
		$this->template->banned_word = $banned_word;
	}
	
	public function action_delete($id = NULL)
	{
		$this->auto_render = FALSE;
		
		$categories = Model_Forum_Banned_Word::load($id);
		$categories->delete();
		
		$this->request->redirect($this->session->get('admin.forum_banned_words.index', 'admin/forum_banned_words'));
	}

}