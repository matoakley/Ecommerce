<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Categories extends Controller_Application
{
	public function before()
	{
		if ( ! Caffeine::modules('categories'))
		{
			throw new Kohana_Exception('The "categories" module is not enabled');
		}
		
		parent::before();
	}
	
	//heres the function for now testing
	function by_high($product)
	{
    return $product;
	}
	
	function action_view()
	{
		$category = Model_Category::load($this->request->param('slug'));
						
		$this->session->set('last_viewed_category', $category);			
								
		$sidebar_categories = ($category->has_children() OR ! $category->parent->loaded()) ? 
										Model_Category::build_category_tree($category->id, TRUE) : 
										Model_Category::build_category_tree($category->parent->id, TRUE);
		
		$parent_category = $category->parent;

		$items = Kohana::config('ecommerce.pagination.products');
		$products_search = Model_Product::search(array('category:'.$category->id, 'status:active'), $items);
		
		if (isset($_POST['sort-high']))
		  {
  		  $products_search = Model_Product::sort_high_or_low_values($products_search, $direction = "high");
		  }
		if (isset($_POST['sort-low']))
		  {
  		  $products_search = Model_Product::sort_high_or_low_values($products_search, $direction = "low");
		  }
		  
		// If number of items is set then we should paginate the results
		if ($items AND $products_search['count_all'] > $items)
		{
			// Pagination
			$this->template->pagination = Pagination::factory(array(
				'total_items'    => $products_search['count_all'],
				'items_per_page' => $items,
				'auto_hide'	=> false,
			));
		}
		
		$this->template->category = $category;
		$this->template->sidebar_categories = (count($sidebar_categories) > 1) ? $sidebar_categories : FALSE;
		$this->template->parent_category = ($category->parent->loaded()) ? $category->parent : FALSE;
		
		// If site is using brands then assign them to template
		if (Kohana::config('ecommerce.modules.brands'))
		{
			$brands = $category->get_brands();
			$this->template->sidebar_brands = (count($brands) > 1) ? $brands : FALSE;
		}

		$this->template->products = $products_search['results'];
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
			$this->add_breadcrumb(URL::site(Route::get('view_category')->uri(array('slug' => $category->parent->slug))), $category->parent->name);
		}
		$this->add_breadcrumb(URL::site(Route::get('view_category')->uri(array('slug' => $category->slug))), $category->name);
	}
	
	function action_index()
	{
		$items = FALSE;

  		  $search = Model_Category::search(array(), $items);
		
		// Set URI into session for redirecting back from forms
		$this->session->set('categories.index', $_SERVER['REQUEST_URI']);
		
		$this->template->categories = $search['results'];
		$this->template->total_categories = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
 	}
	
}