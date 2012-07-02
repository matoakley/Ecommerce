<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Blog_Categories extends Controller_Application
{
	public function before()
	{
		parent::before();
		
		if ( ! $this->modules['blog'] OR ! $this->modules['blog_categories'])
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	}
	
	function action_view()
	{
		$category = Model_Blog_Category::load($this->request->param('slug'));
		
		$this->session->set('last_viewed_blog_category', $category);						
								
		$sidebar_categories = ($category->has_children() OR ! $category->parent->loaded()) ? 
										Model_Blog_Category::build_category_tree($category->id, TRUE) : 
										Model_Blog_Category::build_category_tree($category->parent->id, TRUE);
		
		$parent_category = $category->parent;
		
		$items = Kohana::config('ecommerce.pagination.blog_posts');
		$posts_search = Model_Blog_Post::search(array('category:'.$category->id, 'status:active'), $items);
		
		// If number of items is set then we should paginate the results
		if ($items AND $posts_search['count_all'] > $items)
		{
			// Pagination
			$this->template->pagination = Pagination::factory(array(
				'total_items'    => $posts_search['count_all'],
				'items_per_page' => $items,
				'auto_hide'	=> false,
			));
		}
		
		$this->template->blog_category = $category;
		$this->template->sidebar_categories = (count($sidebar_categories) > 1) ? $sidebar_categories : FALSE;
		$this->template->parent_category = ($category->parent->loaded()) ? $category->parent : FALSE;		
		$this->template->blog_posts = $posts_search['results'];
		$this->template->sub_categories = Model_Category::build_category_tree($category->id, TRUE);
		
		// If a meta description has not been set then we'll build one from the description.
		// Not ideal, but it's better than nothing!
		if ( ! is_null($category->meta_description) AND $category->meta_description != '')
		{
			$meta_description = $category->meta_description;
		}
		else
		{
			$meta_description = substr(strip_tags($category->description), 0, 160);
		}
		$this->template->meta_description = $meta_description;
		$this->template->meta_keywords = $category->meta_keywords;
		
		// load up the breadcrumb
		if ($category->parent->loaded())
		{
			$this->add_breadcrumb(URL::site(Route::get('view_blog_category')->uri(array('slug' => $category->parent->slug))), $category->parent->name);
		}
		$this->add_breadcrumb(URL::site(Route::get('view_blog_category')->uri(array('slug' => $category->slug))), $category->name);
	}
	
}