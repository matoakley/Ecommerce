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
		$this->auto_render = FALSE;
		
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
		}

		if ($this->modules['categories'])
		{
			$categories = Model_Category::search(array('status:active'));
			
			foreach ($categories['results'] as $category)
			{
				$last_mod = time();
				
				// New basic sitemap.
				$url = new Sitemap_URL;
	
				// Set arguments.
				$url->set_loc(URL::site(Route::get('view_category')->uri(array('slug' => $category->slug)), TRUE))
				    ->set_last_mod($last_mod)
				    ->set_change_frequency('daily');
	
				// Add it to sitemap.
				$sitemap->add($url);
			}
		}

		
		if ($this->modules['brands'])
		{
			$brands = Model_Brand::search(array('status:active'));
			
			foreach ($brands['results'] as $brand)
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
		
		if ($this->modules['pages'])
		{
			// CMS Pages
			$pages = Model_Page::search(array('status:active'));
			
			foreach ($pages['results'] as $page)
			{
				if (is_int($page->modified))
				{
					$last_mod = $page->modified;
				}
				else
				{
					$last_mod = $page->created;
				}
				
				// New basic sitemap.
				$url = new Sitemap_URL;
	
				// Set arguments.
				$url->set_loc(URL::site(Route::get('view_page')->uri(array('slug' => $page->slug)), TRUE))
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
					$last_mod = filemtime($file);
				
					$url = new Sitemap_URL;
					
					// Set arguments.
					$url->set_loc(URL::site(Route::get('view_static_page')->uri(array('slug' => $file_bits['filename'])), TRUE))
				    ->set_last_mod($last_mod)
				    ->set_change_frequency('daily');
	
				// Add it to sitemap.
				$sitemap->add($url);
				}
			}
		}
		
		if ($this->modules['blog'])
		{
			// Products
			$posts = Model_Blog_Post::search(array('status:active'));
			
			foreach ($posts['results'] as $post)
			{
				if (is_int($post->modified))
				{
					$last_mod = $post->modified;
				}
				else
				{
					$last_mod = $post->created;
				}
				
				// New basic sitemap.
				$url = new Sitemap_URL;
	
				// Set arguments.
				$url->set_loc(URL::site(Route::get('blog_view')->uri(array('slug' => $post->slug)), TRUE))
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
	
	public function action_product_feed()
	{
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
}