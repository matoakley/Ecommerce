<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Dev extends Controller_Application {
	
	function action_kill_session()
	{
		$this->auto_render = FALSE;
		
		$this->session->destroy();
		
		echo 'Session destroyed!';
	}
	
	function action_correct_prices()
	{
		exit('Remove exit from code to arm this function!');
		
		$this->auto_render = FALSE;
		
		$handle = fopen(APPPATH.'products.csv', 'r');
		
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
		{
			if ((float)$data[2] > 0)
			{
				try
				{
					$product = Jelly::select('product', $data[0]);
					if ($product->loaded())
					{
						$product->price = Model_Product::deduct_tax($data[2]);
						$product->save();
					}
				}
				catch (Validate_Exception $e)
				{
					echo Kohana::debug($e);
				}
			}
		}
		
		fclose($handle);
	}
	
	function action_link_images($image_id)
	{
		$this->template->image = '/images/products/full_size/2011/01/' . $image_id . '.jpg';
		$this->template->next_image_id = (int)$image_id + 1;
		
		if ($_POST)
		{
			$image = Jelly::select('product_image', $image_id);
			$product = Jelly::select('product', $_POST['product_id']);
			
			if ($image->loaded() AND $product->loaded())
			{
				$image->alt_text = $product->name;
				$image->product = $product->id;
				
				$product->default_image = $image->id;
				$product->thumbnail = $image->id;
				
				$product->save();
				$image->save();
				
				echo 'Added image to ' . $product->name;
			}
			else
			{
				echo 'FAILED: Unknown product.';
			}
		}
	}
	
	function action_test_email()
	{
		$sales_order = Jelly::select('sales_order', 1568);
		
		$this->template = Twig::factory('templates/emails/order_confirmation.html');
		$this->template->sales_order = $sales_order;
		// $sales_order->send_confirmation_email();
	}
}