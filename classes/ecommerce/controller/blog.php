<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Blog extends Controller_Application {

	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.blog'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
	}
	
	public function action_index()
	{
		$blog_posts = Model_Blog_Post::search(array(), FALSE, array('created' => 'DESC'));
		
		$this->template->blog_posts = $blog_posts['results'];
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
