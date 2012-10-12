<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Basket extends Controller_Application
{
	public function before()
	{
		if ( ! Kohana::config('ecommerce.modules.sales_orders'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
		
		parent::before();
	}
	
	public function action_view()
	{
		$this->basket->calculate_shipping();
		
		if ($_POST)
		{
			if (isset($_POST['checkout_x']) OR isset($_POST['checkout']))
			{
				$this->request->redirect('/checkout');
			}
			foreach($_POST['basket_items'] as $key => $value)
			{
				Model_Basket_Item::load($key)->update_quantity($value);
			}
		}
		
		$this->template->basket = $this->basket;
		$this->template->delivery_options = Model_Delivery_Option::available_options();
		$this->template->customer = $this->auth->logged_in() ? $this->auth->get_user()->customer : NULL;
		
		if (Kohana::config('ecommerce.modules.reward_points'))
		 {
  		//reward points stuff
  		if ($this->auth->logged_in('customer'))
  		{ 
//    		$this->template->customer_referral_code = $this->basket->generate_unique_code($customer);
  		}
  		else
  		{
    		$this->template->customer_referral_code = $this->basket->generate_unique_code();
  		}
  		
  		$this->template->reward_points_value = Model_Sales_Order::calculate_reward_points_redemption($this->template->reward_points);
  		$reward_points_profile = Jelly::select('reward_points_profile')->where('is_default', '=', 1)->limit(1)->execute();
  		$this->template->customer_referral = $reward_points_profile->customer_referral;
  		$this->template->new_customer_referral = $reward_points_profile->new_customer_referral;
    }
		
		$this->add_breadcrumb('/basket', 'Your Basket');
	}

	public function action_add_items()
	{
		// This function should be called over AJAX, else just process and redirect to action_view.
		$this->auto_render = FALSE;
		
		if (isset($_POST['skus']))
		{	
			foreach ($_POST['skus'] as $sku_id => $quantity)
			{
				if ($quantity > 0)
				{
					$item = $this->basket->add_item($sku_id, $quantity);
				}
			}
		}
		elseif ($_POST['product'])
		{
  		$skus = Model_Product::load($_POST['product'])->skus;

  		if (isset($_POST['options']))
  		{
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
	        		$item = $this->basket->add_item($sku->id, $_POST['quantity']);
	    		}
	  		}
	  	}
	  	else
	  	{
		  	$item = $this->basket->add_item($skus[0]->id, $_POST['quantity']);
	  	}
		}
		
		if (Request::$is_ajax)
		{
			$data = array(
				'basket_items' => $this->basket->count_items(),
				'basket_subtotal' => $this->basket->calculate_subtotal(),
				'line_items' => ($item !== 0) ? $item->quantity : 0,
				'line_total' => ($item !== 0) ? number_format(($item->sku->retail_price() * $item->quantity), 2) : 0,
			);
			
			echo json_encode($data);
		}
		else
		{
			$this->request->redirect('/basket');
		}
	}
	
	public function action_adjust_item($item_id = FALSE, $quantity = FALSE)
	{
		$this->auto_render = FALSE;
		
		if ($_POST)
		{
			$item = Model_Basket_Item::load($_POST['item_id'])->update_quantity($_POST['quantity']);
		}
		elseif ($item_id AND $quantity)
		{
			$item = Model_Basket_Item::load($item_id)->update_quantity($quantity);
		}
		
		if (Request::$is_ajax)
		{
			$data = array(
				'basket_items' => $this->basket->count_items(),
				'basket_subtotal' => number_format($this->basket->calculate_subtotal(), 2),
				'basket_total' => number_format($this->basket->calculate_total(), 2),
				'line_items' => ($item !== 0) ? $item->quantity : 0,
				'line_total' => ($item !== 0) ? number_format(($item->sku->retail_price() * $item->quantity), 2) : 0,
			);
			
			if (Caffeine::modules('reward_points'))
			{
  			$data['max_reward_points'] = $this->basket->max_reward_points();
  			$data['max_reward_points_discount'] = $this->basket->calculate_discount_for_reward_points();
			}
			
			echo json_encode($data);
		}
		else
		{
			$this->request->redirect('/basket');
		}
	}
	
	public function action_update_basket()
	{
		$this->auto_render = FALSE;
		
		header('Cache-Control: max-age=0,no-cache,no-store,post-check=0,pre-check=0'); 
		
		$this->basket->delivery_option = $_POST['delivery_option'];
		$this->basket->save($this->session->get('basket_id'));
		
		echo $this->basket->delivery_option->price;
	}
	
	public function action_update_delivery_option()
	{
		$this->auto_render = FALSE;
		
		$this->basket->update_delivery_option($_POST['id']);
		$this->basket->calculate_shipping();

		echo number_format($this->basket->delivery_option->retail_price(), 2);
	}
	
	public function action_update_total()
	{
		$this->auto_render = FALSE;		
		
		$data = array(
			'basket_subtotal' => number_format($this->basket->calculate_subtotal(), 2),
			'basket_total' => number_format($this->basket->calculate_total(), 2),
			'discount' => number_format($this->basket->calculate_discount(), 2),
		);
		
		echo json_encode($data);
	}
	
	public function action_add_promotion_code()
	{
		$this->auto_render = FALSE;
		
		if (isset($_POST['code']))
		{
			// Check if promotion code exists and if it is valid.
			$this->basket->add_promotion_code($_POST['code']);
			
			$template_data = array(
				'basket' => $this->basket,
			);
			$reward_item = Twig::factory('basket/_promotion_code_item.html', $template_data, $this->environment)->render();
						
			$data = array(
				'code' => $this->basket->promotion_code->code,
				'reward_item' => $reward_item,
			);
			echo json_encode($data);
		}
		else
		{
			throw new Kohana_Exception('No data received.', array(), 500);
		}
			
		exit;
	}
	
	public function action_remove_promotion_code()
	{
		$this->auto_render = FALSE;
		$this->basket->remove_promotion_code();
		echo 'OK';
	}
	
	public function action_create_from_sales_order()
	{
		$sales_order = Model_Sales_Order::load($this->request->param('sales_order_id'));
		
		if ( ! $sales_order->loaded() OR ! $this->auth->logged_in() OR $this->auth->get_user()->customer->id != $sales_order->customer->id)
		{
			throw new Kohana_Exception('Unable to load Sales Order');
		}
		
		$this->basket->create_from_sales_order($sales_order);
		
		$this->request->redirect(Route::get('basket')->uri());
	}
	
	public function action_use_reward_points()
	{
	  
	  $this->auto_render = FALSE;
	
	  // get the customer
	  $user_id = Auth::instance()->get_user()->id;
	 	$customer = Jelly::select('customer')->where('user_id', '=', $user_id)->load();
	 	
	 	//get the customers points and calculate the value
	 	$customer_points = $customer->get_reward_points($customer);
	 	$discount = Model_Sales_Order::calculate_reward_points_redemption($customer_points);
	 
	  //recieve the order total from ajax
	  $order_total = number_format($_POST['data'], 2);
	 
	  //calculate the remaining discount to the nearest 10p
	  $remaining_discount = round(($discount - $order_total), 1, PHP_ROUND_HALF_DOWN);
	  
	  //if the discount is less than zero then its zero
	  if ($remaining_discount < 0)
  	  {
    	  $remaining_discount = 0;
    	  $used_discount = $discount;
    	 }
    else 
    {
      $used_discount = $order_total;
    }
	  //calculate the remaining points
	  $remaining_points = Model_Sales_Order::calculate_points_from_remaining_value($remaining_discount);
	  
	  //if the points are less than zero then there are none
	  if ($remaining_points < 0)
  	  {
    	  $remaining_points = 0;
  	  }
	  
	  //array to send remaining points and value back to basket
	  $data = array(
  	  'points' => $remaining_points,
  	  'value' => number_format($remaining_discount, 2),
  	  'discount' => $used_discount,
  	  'order_total' => $order_total,
  	  'remaining' => $remaining_discount,
  	  );
  	  
  	 // save the discount to the basket to update the total with ajax
  	 $this->basket->save_reward_points_discount($used_discount);
  	 
  	 //now we have finished remove the used points from the customers total
  	$customer->remove_reward_points($remaining_points);
  	  
  	echo json_encode($data);
	}
}