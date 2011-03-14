<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Blog extends Controller_Admin_Application {

	function action_index()
	{
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;
		
		$search = Model_Blog_Post::search(array(), $items, array('created' => 'DESC'));

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items' => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
		));
		
		$this->template->blog_posts = $search['results'];
		$this->template->total_blog_posts = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}
	
	function action_edit_post($id = FALSE)
	{
		$blog_post = Model_Blog_Post::load($id);
	
		if ($id AND ! $blog_post->loaded())
		{
			throw new Kohana_Exception('Blog Post could not be found.');
		}
		
		if ($_POST)
		{
			try
			{
				$brand->update($_POST['brand']);
				
				$this->request->redirect('/admin/brands');
			}
			catch (Validate_Exception $e)
			{
				$this->template->errors = $e->array->errors();
			}
		}
		
		// Loads the script that counts chars on the fly for Meta fields.
		$this->scripts[] = 'jquery.counter-1.0.min';
		
		$this->template->blog_post = $blog_post;
		$this->template->statuses = Model_Blog_Post::$statuses;
	}
	
	public function action_delete_post($id = NULL)
	{
		$this->auto_render = FALSE;
		
		$blog_post = Model_Blog_Post::load($id);
		$blog_post->delete();
		
		$this->request->redirect('admin/blog');
	}
	
}