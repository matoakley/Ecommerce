<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Blog extends Controller_Application
{
	public function action_index()
	{
		$blog_posts = Model_Blog_Post::search(array(), FALSE, array('published_on' => 'DESC'));
		
		$this->template->blog_posts = $blog_posts['results'];
	}
	
	public function action_view($slug = FALSE)
	{
		$this->template->blog_post = Model_Blog_Post::load($slug);
	}
}
