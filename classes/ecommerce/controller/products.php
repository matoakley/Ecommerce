<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Products extends Controller_Application
{
	public function before()
	{
		if ( ! Kohana::config('ecommerce.modules.products'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
		
		parent::before();
	}

	function action_view()
	{
		$product = Model_Product::load($this->request->param('slug'));
		
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
		
		  if (Caffeine::modules('related_products'))
		    {
  		    $this->template->related_products = Model_Related_Product::get_related_products($product->id);
		    }
		    
		$this->template->sidebar_categories = (count($sidebar_categories) > 1) ? $sidebar_categories : FALSE;
		$this->template->parent_category = ($category->parent->loaded()) ? $category->parent : FALSE;
		$this->template->meta_description = $product->display_meta_description();
		$this->template->meta_keywords = $product->meta_keywords;
		
		$this->template->age = Model_User::get_age($this->auth->user->customer->D_O_B);
		
		if (Caffeine::modules('wish_list'))
		  {
  		  $this->template->in_wish_list = ($this->auth->logged_in()) ? in_array($product->id, $this->auth->get_user()->wish_list_items->as_array('id', 'id')) : FALSE;
		  }
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
			$items = Kohana::config('ecommerce.pagination.products');
			$products_search = Model_Product::search(array('status:active'), $items);
			
			$search_term = implode(' ',$products_search['query_string']);
			
			// Make a record of the search for analytics
			Model_Search::record($search_term, $products_search['count_all']);
			
			$this->template->search_term = $search_term;
			$this->template->products = $products_search['results'];
			
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
		}
				
		$this->add_breadcrumb('/search', 'Search');
	}
	
	public function action_price_with_options()
	{
		$this->auto_render = FALSE;
		
		$data = array();
		
		if ($_POST)
		{
			// Find the SKU that matches the product/options combination
			$skus = Model_Product::load($_POST['product'])->skus;

  		foreach ($skus as $sku)
  		{
    		$matches = TRUE;
    		
    		$sku_product_options = $sku->product_options->as_array('id', 'value');
    		
    		foreach ($_POST['options'] as $option_id)
    		{ 
      		if ( ! isset($sku_product_options[$option_id]))
      		{
          	$matches = FALSE;
      		}
    		}
    		
    		if ($matches AND $sku->status == 'active')
    		{
        	$data['price'] = number_format($sku->retail_price(), 2);
        	$data['image'] = $sku->thumbnail->full_size_path;
        
          if (Caffeine::modules('stock_control')) 
          {
            $data['stock'] = $sku->stock;
          }
          else 
          {
            $data['stock'] = $sku->stock_status == 'in_stock';
          }
          
          if (Caffeine::modules('reward_points'))
          {
            $data['reward_points'] = floor($data['price']) * Model_Reward_Points_Profile::load(1)->points_per_pound;
          }
    		}
    	}
		}
		
		echo json_encode($data);
	}
	
	public function action_get_product_reviews()
	{  
	  $product = Model_Product::load($_POST['id']);
	  $errors = array();
	  try
      	{
        	$reviews = $product->get_product_reviews($_POST['items'], $_POST['offset'], isset($_POST['order']) ? $_POST['order'] : 'created' , isset($_POST['direction']) ? $_POST['direction'] : 'ASC');
        }
        catch (Validate_Exception $e)
        {
           $errors['reviews'] = $e->array->errors('model/reviews');
        }
        
        $view = array();
        
  	foreach ($reviews->as_array() as $review)
  	  {
  	    $review_model = Model_Review::load($review['id']);
    	  $template_data = array(
					'review' => $review_model,
					'auth' => $this->auth,
				);
				
				$view[] = Twig::factory('products/_review.html', $template_data, $this->environment)->render(); 
		  }
		  
  	if (Request::$is_ajax)
    {
      $this->auto_render = FALSE;
      $this->request->headers['Content-Type'] = 'application/json';
      echo json_encode(array(
        'errors' => $errors,
        'reviews' => isset($view) ? $view : NULL,
      ));
    }
 }
}
