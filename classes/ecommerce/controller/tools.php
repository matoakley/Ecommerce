<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Tools extends Controller_Application
{
	public function action_send_contact_form()
	{
		$this->auto_render = FALSE;
		
		// Email address to send enquiry to
		$to = Kohana::config('ecommerce.copy_order_confirmations_to');
		$subject = 'Enquiry Submitted from Website';
		$message = 'The following enquiry was submitted via the website on ' . date('d/m/Y H:i') . "\r\n\r\n";
	
		if ($_POST)
		{
			$validates = TRUE;
			$errors = array();
			
			// Validate data posted from enquiry form
			if ( ! isset($_POST['name']) OR $_POST['name'] == '')
			{
				$validates = FALSE;
				$errors['name'] = 'Please enter your name.';
			}
			
			if ( ! isset($_POST['email']) OR ! preg_match('/^[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]+$/', $_POST['email']))
			{
				$validates = FALSE;
				$errors['email'] = 'Please provide a valid email address.';
			}
			
			if ( ! isset($_POST['enquiry']) OR $_POST['enquiry'] == '')
			{
				$validates = FALSE;
				$errors['enquiry'] = 'Enter a brief description of your enquiry.';
			}
			
			if ($validates)
			{
				// Send email
				$headers = 'From: ' . $_POST['name'] . ' <' . $_POST['email'] . '>';
				$message .= 'Name: ' . $_POST['name'] . "\r\n\r\n";
				$message .= 'Email: ' . $_POST['email'] . "\r\n\r\n";
				if (isset($_POST['telephone']) AND $_POST['telephone'] != '')
				{
					$message .= 'Phone: ' . $_POST['telephone'] . "\r\n\r\n";
				}
				$message .= 'Enquiry: ' . $_POST['enquiry'];
				
				mail($to, $subject, $message, $headers);
			}
			else
			{
				echo json_encode($errors);
			}
		}
	}
	
	public function action_sitemap()
	{
		if ( ! $this->request->param('human'))
		{
			$this->auto_render = FALSE;	
		}
		
		// We will also create an array that can be used
		// for a human readable sitemap
		$human_sitemap = array();
		
		// Sitemap instance.
		$sitemap = new Sitemap;

		// Homepage
		$url = new Sitemap_URL;

		// Set arguments.
		$url->set_loc(URL::base())
		    ->set_change_frequency('daily');

		// Add it to sitemap.
		$sitemap->add($url);

		if ($this->modules['products'])
		{
			// Products
			$products = Model_Product::search(array('status:active'));
			
			// Human readable container
			$human_sitemap['products'] = array();
			
			foreach ($products['results'] as $product)
			{
				$location = URL::site(Route::get('view_product')->uri(array('slug' => $product->slug)), TRUE);
				$human_sitemap['products'][$product->name] = $location;
				$last_mod = is_int($product->modified) ? $product->modified : $product->created;
								
				// New basic sitemap.
				$url = new Sitemap_URL;
	
				// Set arguments.
				$url->set_loc($location)
				    ->set_last_mod($last_mod)
				    ->set_change_frequency('daily');
	
				// Add it to sitemap.
				$sitemap->add($url);
			}
		}

		if ($this->modules['categories'])
		{
			$categories = Model_Category::search(array('status:active'));
		
			// Human readable container
			$human_sitemap['categories'] = array();
			
			foreach ($categories['results'] as $category)
			{
				$location = URL::site(Route::get('view_category')->uri(array('slug' => $category->slug)), TRUE);
				$human_sitemap['categories'][$category->name] = $location;
				$last_mod = is_int($category->modified) ? $category->modified : $category->created;
							
				// New basic sitemap.
				$url = new Sitemap_URL;
	
				// Set arguments.
				$url->set_loc($location)
				    ->set_last_mod($last_mod)
				    ->set_change_frequency('daily');
	
				// Add it to sitemap.
				$sitemap->add($url);
			}
		}

		if ($this->modules['brands'])
		{
			$brands = Model_Brand::search(array('status:active'));
			
			// Human readable container
			$human_sitemap['brands'] = array();
			
			foreach ($brands['results'] as $brand)
			{
				$location = URL::site(Route::get('view_brand')->uri(array('slug' => $brand->slug)), TRUE);
				$human_sitemap['brands'][$brand->name] = $location;
				$last_mod = is_int($brand->modified) ? $brand->modified : $brand->created;
				
				// New basic sitemap.
				$url = new Sitemap_URL;
	
				// Set arguments.
				$url->set_loc($location)
				    ->set_last_mod($last_mod)
				    ->set_change_frequency('daily');
	
				// Add it to sitemap.
				$sitemap->add($url);
			}
		}
		
		if ($this->modules['stockists'])
		{
			$stockists = Model_Stockist::search(array('status:active'));
			
			// Human readable container
			$human_sitemap['stockists'] = array();
			
			foreach ($stockists['results'] as $stockist)
			{
				$location = URL::site(Route::get('view_stockist')->uri(array('slug' => $stockist->slug)), TRUE);
				$human_sitemap['stockists'][$stockist->name] = $location;
				$last_mod = is_int($stockist->modified) ? $stockist->modified : $stockist->created;
				
				// New basic sitemap.
				$url = new Sitemap_URL;
	
				// Set arguments.
				$url->set_loc($location)
				    ->set_last_mod($last_mod)
				    ->set_change_frequency('daily');
	
				// Add it to sitemap.
				$sitemap->add($url);
			}
		}
		
		if ($this->modules['pages'])
		{
			// CMS Pages
			$pages = Model_Page::search(array('status:active'));
		
			// Human readable container
			$human_sitemap['pages'] = array();
			
			foreach ($pages['results'] as $page)
			{
				$location = URL::site(Route::get('view_page')->uri(array('slug' => $page->slug)), TRUE);
				$human_sitemap['pages'][$page->name] = $location;
				$last_mod = is_int($page->modified) ? $page->modified : $page->created;
				
				// New basic sitemap.
				$url = new Sitemap_URL;
	
				// Set arguments.
				$url->set_loc($location)
				    ->set_last_mod($last_mod)
				    ->set_change_frequency('daily');
	
				// Add it to sitemap.
				$sitemap->add($url);
			}
			
			// Read through static pages
			if ($handle = opendir(APPPATH.'views/pages/static'))
			{
				while (FALSE !== ($page_file = readdir($handle)))
				{
					$file = APPPATH.'views/pages/static/'.$page_file;
					$file_bits = pathinfo($file);
				
					// Ignore elements starting with _, empty filename and .
					if (strlen($file_bits['filename']) > 1 AND substr($file_bits['filename'], 0, 1) != '_')
					{
						$location = URL::site(Route::get('view_static_page')->uri(array('slug' => $file_bits['filename'])), TRUE);
						$tidy_file_name = ucwords(str_replace('-', ' ', $file_bits['filename']));
						$human_sitemap['pages'][$tidy_file_name] = $location;
						$last_mod = filemtime($file);
	
						$url = new Sitemap_URL;
						
						// Set arguments.
						$url->set_loc($location)
					    ->set_last_mod($last_mod)
					    ->set_change_frequency('daily');
		
					  // Add it to sitemap.
					  $sitemap->add($url);
				  }
				}
			}
		}
		
		if ($this->modules['blog'])
		{
			// Products
			$posts = Model_Blog_Post::search(array('status:active'));
		
			// Human readable container
			$human_sitemap['blog_posts'] = array();
			
			foreach ($posts['results'] as $post)
			{
				$location = URL::site(Route::get('blog_view')->uri(array('slug' => $post->slug)), TRUE);
				$human_sitemap['blog_posts'][$post->name] = $location;
				$last_mod = is_int($post->modified) ? $post->modified : $post->created;
				
				// New basic sitemap.
				$url = new Sitemap_URL;
	
				// Set arguments.
				$url->set_loc($location)
				    ->set_last_mod($last_mod)
				    ->set_change_frequency('daily');
	
				// Add it to sitemap.
				$sitemap->add($url);
			}
		}

		if ($this->request->param('human'))
		{
			ksort($human_sitemap);
			foreach ($human_sitemap as &$a)
			{ 
				ksort($a);	
			}
			$this->template->sitemap = $human_sitemap;
		}
		else
		{
			echo $sitemap;
		}
		
	}
	
	public function action_product_feed()
	{
		if ( ! $this->modules['products'])
		{
			throw new Kohana_Exception('Product module is not enabled.');
		}
		
		// Products
		$products = Model_Product::search(array('status:active'));
		$this->template->products = $products['results'];
		$this->template->default_google_product_category = Kohana::config('ecommerce.default_google_product_category');			
	}
	
	public function action_convert_to_skus()
	{
		$this->auto_render = FALSE;
		
		// first, cover only products with options
		$products = Jelly::select('product')
									->distinct(TRUE)
									->join('product_options')
									->on('product_options.product_id', '=', 'product.id')
									->execute();
		
		foreach ($products as $product)
		{		
			// get an array of all options associated to product
			$options = $product->get_options();
			$count_options = count($options);
			
			$option_values = array();
			$counters = array();
			for ($i = 0; $i < $count_options; $i++)
			{
				$option_values[$i] = array_values($product->get('product_options')
													  				->where('key', '=', $options[$i])
																		->execute()->as_array('id', 'id'));
				$counters[$i] = count($option_values[$i]);
			}
			$counter_max_values = $counters;
			
			echo Kohana::debug($option_values);
			
			// calculate the number of skus required to fit all combinations of options
			$total_skus_required = 0;
			foreach ($counters as $counter)
			{
				if ($total_skus_required == 0)
				{
					$total_skus_required = $counter;
				}
				else
				{
					$total_skus_required *= $counter;
				}
			}
			
			$current_counter_index = count($counters) - 1;
			
			// loop X times making a sku and adding array keys
			for ($i = 0; $i < $total_skus_required; $i++)
			{
				$sku = Jelly::factory('sku');
				$sku->product = $product;
				$sku->stock = 1;
				$sku->price = $product->price;
				$sku->status = 'active';
				
				// use the counters to work some magic and add the necessary options
				foreach ($counters as $key => $option_index)
				{
					$product_option = Jelly::select('product_option', $option_values[$key][$option_index-1]);
					$sku->add('product_options', $option_values[$key][$option_index-1]);
				}
				
				$sku->save();
				
				$counters[$current_counter_index] = $counters[$current_counter_index] - 1;
				
				while ($counters[$current_counter_index] == 0)
				{
					// if the current counter is at zero then we need to reset it to its max and move onto the next counter
					$counters[$current_counter_index] = $counter_max_values[$current_counter_index];
					
					echo 'Current counter index: '.$current_counter_index;
					
					if ($current_counter_index > 0)
					{	
						$current_counter_index--;
						$counters[$current_counter_index] = $counters[$current_counter_index] - 1;
						$current_counter_index =  count($counters) - 1;
					}
					else
					{
						break;
					}
				}
			}
		}

		// products with no options
		$products = Jelly::select('product')->execute();
		
		foreach ($products as $product)
		{
			if (count($product->skus) == 0)
			{
				$sku = Jelly::factory('sku');
				$sku->product = $product;
				$sku->sku = $product->sku;
				$sku->price = $product->price;
				$sku->stock = $product->stock;
				$sku->status = 'active';
				$sku->save();
			}
		}
		
		echo '<p><strong>FIN</strong></p>';
	}
	
	public function action_accept_cookies()
	{
		$this->auto_render = FALSE;
		
		// Dump a cookie on the user's machine so that we don't show them
		// the EU Cookie Law disclaimer on future visits.
		Cookie::set('cookies_accepted', time());
		
		if ( ! Request::$is_ajax)
		{
			$this->request->redirect(Request::$referrer);
		}
	}
}