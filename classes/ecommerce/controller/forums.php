<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Forums extends Controller_Application
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
		$this->template->forum_categories = Model_Forum_Category::build_category_tree(NULL, TRUE);
	}
	
	public function action_view_category()
	{
		$forum_category = Model_Forum_Category::load($this->request->param('category_slug'));
		
		if ( ! $forum_category->loaded())
		{
			throw new Kohana_Exception('Forum Category not found.');
		}
		
		$posts_per_page = 20;
		$page_number = Arr::get($_GET, 'page', 1);
		$category_posts = $forum_category->latest_threads($posts_per_page, ($page_number - 1) * $posts_per_page);
		
		$this->template->pagination = Pagination::factory(array(
				'total_items'    => count($forum_category->posts),
				'items_per_page' => $posts_per_page,
				'auto_hide'	=> TRUE,
			));
		
		$this->template->forum_category = $forum_category;
		$this->template->category_posts = $category_posts;
	}
	
	public function action_view_post()
	{
		$post = Model_Forum_Post::load($this->request->param('post_slug'));
		
		if ( ! $post->loaded())
		{
			throw new Kohana_Exception('Post not found.');
		}
		
		$posts_per_page = 20;
		$page_number = Arr::get($_GET, 'page', 1);
		$thread = $post->build_thread($posts_per_page, ($page_number - 1) * $posts_per_page);
		
		$fields = array();
		$errors = array();
		
		if ($_POST)
		{
			try
			{
				$post->add_reply($_POST['post'], $this->auth->get_user());
				// Redirect to last page of comments
				$this->request->redirect(Route::get('forum_post_view')->uri(array('category_slug' => $post->category->slug, 'post_slug' => $post->slug)).'?page='.ceil($post->calculate_thread_length()/$posts_per_page));
			}
			catch (Validate_Exception $e)
			{
				$errors['forum_post'] = $e->array->errors('models/forum_post');
				$fields = $_POST;
			}
		}
		
		$this->template->fields = $fields;
		$this->template->errors = $errors;
		
		$this->template->pagination = Pagination::factory(array(
				'total_items'    => $post->calculate_thread_length(),
				'items_per_page' => $posts_per_page,
				'auto_hide'	=> TRUE,
			));
		
		$this->template->post = $post;

		
		$this->template->thread_posts = $thread;
	}
	
	public function action_new_post()
	{
		$forum_category = Model_Forum_Category::load($this->request->param('category_slug'));
		
		if ( ! $forum_category->loaded())
		{
			throw new Kohana_Exception('Forum Category not found.');
		}
		
		$fields = array();
		$errors = array();
		
		if ($_POST)
		{
			try
			{
				$post = $forum_category->add_post($_POST['forum_post'], $this->auth->get_user());
				$this->request->redirect(Route::get('forum_post_view')->uri(array('category_slug' => $forum_category->slug, 'post_slug' => $post->slug)));
			}
			catch (Validate_Exception $e)
			{
				$errors['forum_post'] = $e->array->errors('models/forum_post');
				$fields = $_POST;
			}
		}
		
		$this->template->fields = $fields;
		$this->template->errors = $errors;
		
		$this->template->forum_category = $forum_category;
	}
}