<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Products extends Controller_Application {

	function action_view($slug = FALSE)
	{
		$product = Model_Product::load($slug);
		
		if ( ! $product->loaded())
		{
			throw new Kohana_Exception('The product that you are searching for could not be found.');
		}
		
		// Try to grab the category that user has come from session, otherwise,
		// grab the first category that the product occurs in.				
		$category = $this->session->get('last_viewed_category', $product->categories->current());
		
		$sidebar_categories = ($category->has_children() OR ! $category->parent->loaded()) ? 
										Model_Category::build_category_tree($category->id, TRUE) : 
										Model_Category::build_category_tree($category->parent->id, TRUE);
										
		// Set product into recently viewed products array in session
		$recent_products = $this->session->get('recent_products',array());
		unset($recent_products[$product->slug]);
		$recent_products[$product->slug] = $product;
		if (count($recent_products) > 3)
		{
			$recent_products = array_reverse($recent_products);
			array_pop($recent_products);
			$recent_products = array_reverse($recent_products);
		}
		$this->session->set('recent_products', $recent_products);
										
		$this->template->product = $product;
		$this->template->brand = $product->brand;
		$this->template->sidebar_categories = (count($sidebar_categories) > 1) ? $sidebar_categories : FALSE;
		$this->template->parent_category = ($category->parent->loaded()) ? $category->parent : FALSE;
		$this->template->meta_description = $product->display_meta_description();
		$this->template->meta_keywords = $product->meta_keywords;
		
		// load up the breadcrumb
		$category = $this->session->get('last_viewed_category');
		
		// Check that the last viewed category (if set) contains this product
		if ($category AND ! array_key_exists($category->id, $product->categories->as_array('id')))
		{
			unset($category);
		}
		
		// If we haven't gotten the category from the session, grab the first category it appears in
		if ( ! isset($category) OR ! $category)
		{
			$category = $product->categories->current();
		}
		
		// Assign the category to the template for microformat markup
		$this->template->category = $category;
		
		if ($category->parent->loaded())
		{
			$this->add_breadcrumb(URL::site(Route::get('view_category')->uri(array('slug' => $category->parent->slug))), $category->parent->name);
		}
		
		if ($category->loaded())
		{
			$this->add_breadcrumb(URL::site(Route::get('view_category')->uri(array('slug' => $category->slug))), $category->name);
		}

		$this->add_breadcrumb(URL::site(Route::get('view_product')->uri(array('slug' => $product->slug))), $product->name);
	}
	
	function action_search()
	{
		if (isset($_GET['q']))
		{
			$items = 10;
			$products_search = Model_Product::search(array('status:active'), $items);
			
			$search_term = implode(' ',$products_search['query_string']);
			
			// Make a record of the search for analytics
			Model_Search::record($search_term, $products_search['count_all']);
			
			$this->template->search_term = $search_term;
			$this->template->products = $products_search['results'];
			
			// Pagination
			$this->template->pagination = Pagination::factory(array(
				'total_items'    => $products_search['count_all'],
				'items_per_page' => $items,
				'auto_hide'	=> false,
			));
		}
				
		$this->add_breadcrumb('/search', 'Search');
	}
	
}