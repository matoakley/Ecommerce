<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Blog extends Controller_Admin_Application {

	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.blog'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
	}
	
	function action_index()
	{
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;
		
		$search = Model_Blog_Post::search(array(), $items, array('created' => 'DESC'));

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items' => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.blog.index', $_SERVER['REQUEST_URI']);
		
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
		
		$redirect_to = $this->session->get('admin.blog.index', '/admin/blog');
		$this->template->cancel_url = $redirect_to;
		
		$fields = array(
			'blog_post' => $blog_post->as_array(),
			'blog_categories' => $blog_post->categories->as_array('id', 'id'),
		);
		if ($this->modules['custom_fields'])
		{
			$fields['custom_fields'] = $blog_post->custom_fields();
		}
		$errors = array();
		
		if ($_POST)
		{
			try
			{
				$blog_post->update($_POST['blog_post']);
				if ($this->modules['custom_fields'] AND isset($_POST['custom_fields']))
				{
					$blog_post->update_custom_field_values($_POST['custom_fields']);
				}
				
				// If 'Save & Exit' has been clicked then lets hit the index with previous page/filters
				if (isset($_POST['save_exit']))
				{
					$this->request->redirect($redirect_to);
				}
				else
				{
					$this->request->redirect('/admin/blog/edit_post/' . $blog_post->id);
				}
			}
			catch (Validate_Exception $e)
			{
				$errors['blog_post'] = $e->array->errors();
				$fields = $_POST;
			}
		}
		
		$this->template->fields = $fields;
		$this->template->errors = $errors;
		
		// Loads the script that counts chars on the fly for Meta fields.
		$this->scripts[] = 'jquery.counter-1.0.min';
		
		$this->template->blog_post = $blog_post;
		$this->template->statuses = Model_Blog_Post::$statuses;
		$this->template->categories = Model_Blog_Category::get_admin_categories(FALSE, FALSE);
	}
	
	public function action_delete_post($id = NULL)
	{
		$this->auto_render = FALSE;
		
		$blog_post = Model_Blog_Post::load($id);
		$blog_post->delete();
		
		$this->request->redirect($this->session->get('admin.blog.index', 'admin/blog'));
	}
	
	public function action_upload_image()
	{
		$this->auto_render = FALSE;
		
		if ($_POST)
		{	
			$blog_post = Model_Blog_Post::load($_POST['blog_post_id']);
			$blog_post->upload_image($_FILES['image']['tmp_name']);
		}
	}

}
