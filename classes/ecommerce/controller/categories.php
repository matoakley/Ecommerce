<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Categories extends Controller_Application {

	function action_view($slug = FALSE)
	{
		$category = Model_Category::load($slug);
						
		$this->session->set('last_viewed_category', $category);						
								
		$sidebar_categories = ($category->has_children() OR ! $category->parent->loaded()) ? 
										Model_Category::build_category_tree($category->id, TRUE) : 
										Model_Category::build_category_tree($category->parent->id, TRUE);
		
		$parent_category = $category->parent;
		
		// Super nasty query to pull a distinct list of Brands that exist in this category.
		// Maybe this should move to a model?
		$sidebar_brands = DB::select('brands.*')
								->from('brands')
								->distinct(TRUE)
								->join('products')->on('products.brand_id', '=', 'brands.id')
								->join('categories_products')->on('categories_products.product_id', '=', 'products.id')
								->where('categories_products.category_id', '=' , $category->id)							
								->order_by('brands.name')
								->execute();
		
		$items = 10;
		
		$products_search = Model_Product::search(array('category:'.$category->id, 'status:active'), $items);
		
		// Only include pagination if it is required
		if ($products_search['count_all'] > $items)
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
		$this->template->sidebar_brands = (count($sidebar_brands) > 1) ? $sidebar_brands : FALSE;
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
	
}