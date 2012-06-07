<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Blog extends Controller_Application {

	function before()
	{
		parent::before();
		
		if ( ! $this->modules['blog'])
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	}
	
	public function action_index()
	{
		$items = Kohana::config('ecommerce.pagination.blog_posts');
		
		$blog_post_search = Model_Blog_Post::search(array('status:active'), $items, array('created' => 'DESC'));
		
		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items'    => $blog_post_search['count_all'],
			'items_per_page' => $items,
			'auto_hide'	=> false,
		));
		
		$this->template->blog_posts = $blog_post_search['results'];
		if ($this->modules['blog_categories'])
		{
			$this->template->blog_categories = Model_Blog_Category::build_category_tree(NULL, TRUE);
		}
		$this->add_breadcrumb('/blog', 'Blog');
	}
	
	public function action_view($slug = FALSE)
	{
		$blog_post = Model_Blog_Post::load($slug);
		
		if ( ! $blog_post->loaded())
		{
			throw new Kohana_Exception('The blog post that you are searching for could not be found.');
		}
		
		$this->template->blog_post = $blog_post;
		
		$this->add_breadcrumb('/blog', 'Blog');
		$this->add_breadcrumb('/blog/' . $blog_post->slug, $blog_post->name);
	}
}
