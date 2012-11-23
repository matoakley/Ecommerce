<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Comments extends Controller_Admin_Application
{
	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.comments'))
		{
			throw new Kohana_Exception('The Comments module is not enabled');
		}
	
		parent::before();
	}
	
	public function action_index()
	{					
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;

		$search = Model_Comment::search(array(), $items);

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items' => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.comments.index', $_SERVER['REQUEST_URI']);
		
		$this->template->comments = $search['results'];
		$this->template->total_comments = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}
	
	public function action_edit()
	{
  	$comment = Model_Comment::load($this->request->param('id'));
	
		if ($this->request->param('id') AND ! $comment->loaded())
		{
			throw new Kohana_Exception('Review could not be found.');
		}
		
		$fields = array(
		  'comment' => $comment->as_array(),
		);
		$errors = array();
		
		$redirect_to = $this->session->get('admin.comments.index', '/admin/comments');
		$this->template->cancel_url = $redirect_to;
		
		if ($_POST)
		{
  		try
  		{
        $comment->validate($_POST['comment']);
  		}
  		catch (Validate_Exception $e)
  		{
    		$errors['comment'] = $e->array->errors();
  		}
  		
  		if (empty($errors))
  		{
    		$comment->update($_POST['comment']);
    		
    		// If 'Save & Exit' has been clicked then lets hit the index with previous page/filters
				if (isset($_POST['save_exit']))
				{
					$this->request->redirect($redirect_to);
				}
				else
				{
					$this->request->redirect('/admin/comments/edit/'.$comment->id);
				}
  		}
  		else
  		{
    		$fields['comment'] = $_POST['comment'];
  		}
		}
		
		$this->template->fields = $fields;
		$this->template->errors = $errors;
		
		$this->template->comment = $comment;
		$this->template->statuses = Model_Comment::$statuses;
	}
	
	public function action_delete()
	{
		$this->auto_render = FALSE;
		
		$Comment = Model_Comment::load($this->request->param('id'));
		$Comment->delete();
		
		$this->request->redirect($this->session->get('admin.comments.index', 'admin/comments'));
	}
}