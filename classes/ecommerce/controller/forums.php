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
		
		$this->template->forum_category = $forum_category;
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
		
		$this->template->pagination = Pagination::factory(array(
				'total_items'    => $post->calculate_thread_length(),
				'items_per_page' => $posts_per_page,
				'auto_hide'	=> TRUE,
			));
		
		$this->template->post = $post;

		
		$this->template->thread_posts = $thread;
	}
}