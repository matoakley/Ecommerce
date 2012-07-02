<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Blog extends Controller_Application {

	function before()
	{
		parent::before();
		
		if ( ! $this->modules['blog'])
		{
			throw new Kohana_Exception('This module is not enabled');
		}
		
		if ($this->modules['blog_categories'])
		{
			$this->template->blog_categories = Model_Blog_Category::build_category_tree(NULL, TRUE);
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
		
		// load up the breadcrumb
		$blog_category = $this->session->get('last_viewed_blog_category');
		
		// Check that the last viewed category (if set) contains this product
		if ($blog_category AND ! array_key_exists($blog_category->id, $blog_post->categories->as_array('id')))
		{
			unset($blog_category);
		}
		
		// If we haven't gotten the category from the session, grab the first category it appears in
		if ( ! isset($blog_category) OR ! $blog_category)
		{
			$blog_category = $blog_post->categories->current();
		}
		
		if ($blog_category->parent->loaded())
		{
			$this->add_breadcrumb(URL::site(Route::get('view_blog_category')->uri(array('slug' => $blog_category->parent->slug))), $blog_category->parent->name);
		}
		
		if ($blog_category->loaded())
		{
			$this->add_breadcrumb(URL::site(Route::get('view_blog_category')->uri(array('slug' => $blog_category->slug))), $blog_category->name);
		}
		
		$this->add_breadcrumb('/blog/' . $blog_post->slug, $blog_post->name);
		
	}

	// Generate an RSS feed containing all Blog posts	
	public function action_rss_feed()
	{
		$this->auto_render = FALSE;

		$blog_posts = Jelly::select('blog_post')->where('status', '=', 'active')->execute();
		
		$this->request->headers['Content-type'] = 'text/xml';
		
		$site = Kohana::config('ecommerce.site_name');
		
		$info = array(
			'title' => $site.' Blog',
			'description' => 'News and articles from '.$site,
			'link' => URL::site(Route::get('blog_rss_feed')->uri()),
		);
		
		// Create array to contain posts
		$items = array();
		foreach ($blog_posts as $blog_post)
		{
 			$date = date(DATE_RFC1123, $blog_post->created);

			$items[] = array(
				'title' => $blog_post->name,
				'guid' => URL::site(Route::get('blog_view')->uri(array('slug' => $blog_post->slug))),
				'pubDate' => strip_tags($date),
				'author' => htmlentities(strip_tags($blog_post->author->email.' ('.$blog_post->author->firstname.' '.$blog_post->author->lastname.')')),
				'description' => HTML::entities(strip_tags(Text::limit_words($blog_post->body, 100, ''))),
/* 				"description" => strip_tags(str_replace(array("&mdash;", "&rsquo;", "&ldquo;", "&lsquo;", "&rdquo;", "&hellip;", "&lt;", "&gt;", "&amp;"), array("-", "'", "'", "'", "'", "...", "<", ">", "+"), Text::limit_words($blog_post->body))), */
			);
		}
		
		echo Feed::create($info, $items);
	}
}
