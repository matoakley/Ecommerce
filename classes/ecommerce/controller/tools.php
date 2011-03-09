<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Tools extends Controller_Application {

	function action_sitemap()
	{
		$this->auto_render = FALSE;
		
		// Sitemap instance.
		$sitemap = new Sitemap;

		// Homepage
		$url = new Sitemap_URL;

		// Set arguments.
		$url->set_loc('http://www.southwoldpharmacy.co.uk')
		    ->set_change_frequency('daily');

		// Add it to sitemap.
		$sitemap->add($url);

		// Products
		$products = Model_Product::search(array('status:active'));
		
		foreach ($products['results'] as $product)
		{
			if (is_int($product->modified))
			{
				$last_mod = $product->modified;
			}
			else
			{
				$last_mod = $product->created;
			}
			
			// New basic sitemap.
			$url = new Sitemap_URL;

			// Set arguments.
			$url->set_loc(URL::site(Route::get('view_product')->uri(array('slug' => $product->slug)), TRUE))
			    ->set_last_mod($last_mod)
			    ->set_change_frequency('daily');

			// Add it to sitemap.
			$sitemap->add($url);
		}		
		
		if (Kohana::config('ecommerce.modules.brands'))
		{
			$brands = Jelly::select('brand')
										->where('status', '=', 'active')
										->order_by('name')
										->execute();
			
			foreach ($brands as $brand)
			{
				$last_mod = time();
				
				// New basic sitemap.
				$url = new Sitemap_URL;
	
				// Set arguments.
				$url->set_loc(URL::site(Route::get('view_brand')->uri(array('slug' => $brand->slug)), TRUE))
				    ->set_last_mod($last_mod)
				    ->set_change_frequency('daily');
	
				// Add it to sitemap.
				$sitemap->add($url);
			}
		}
		
		// Render the output.
		$output = $sitemap->render();

		// __toString is also supported.
		echo $sitemap;
		
	}
	
	function action_product_feed()
	{
		// Products
		$products = Model_Product::search(array('status:active'));
		$this->template->products = $products['results'];
		
	}
}