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
		
		if ($this->auth->logged_in('customer'))
		{
  		$this->template->customer = $this->auth->get_user()->get('customer')->load();
    }
    else
    {
      if (Caffeine::modules('reward_points'))
      {
        $reward_points_profile = Model_Reward_Points_Profile::load(1);
        $this->template->customer_referral_reward = $reward_points_profile->customer_referral;
        $this->template->new_customer_referral_reward = $reward_points_profile->new_customer_referral;
      }
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
		
		$this->basket->calculate_shipping();
		
		if (Request::$is_ajax)
		{
			$data = array(
				'basket_items' => $this->basket->count_items(),
				'basket_subtotal' => $this->basket->calculate_subtotal(),
				'basket_total' => number_format($this->basket->calculate_total(), 2),
				'line_name' => $item->sku->name(),
				'line_items' => ($item !== 0) ? $item->quantity : 0,
				'line_total' => ($item !== 0) ? number_format(($item->sku->retail_price() * $item->quantity), 2) : 0,
				'shipping' => number_format($this->basket->delivery_option->retail_price(), 2),
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
		
		$this->basket->calculate_shipping();
		
		if (Request::$is_ajax)
		{
			$data = array(
				'basket_items' => $this->basket->count_items(),
				'basket_subtotal' => number_format($this->basket->calculate_subtotal(), 2),
				'basket_total' => number_format($this->basket->calculate_total(), 2),
				'shipping' => number_format($this->basket->delivery_option->retail_price(), 2),
				'discount_amount' => number_format($this->basket->calculate_discount(), 2),
			);
			
			if ($item){
				$data['line_name'] = $item->sku->name();
				$data['line_items'] = ($item !== 0) ? $item->quantity : 0;
				$data['line_total'] = ($item !== 0) ? number_format(($item->sku->retail_price() * $item->quantity), 2) : 0;
		  } else {
  		  $data['line_items'] = 0;
		  }
			
			if (Caffeine::modules('reward_points'))
			{
  			$data['max_reward_points'] = $this->basket->max_reward_points();
  			$data['max_reward_points_discount'] = number_format($this->basket->calculate_discount_for_reward_points(), 2);
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
			
			try
			{
  			$reward_item = Twig::factory('basket/_promotion_code_item.html', $template_data, $this->environment)->render();
			}
			catch (Exception $e)
			{
  			$reward_item = NULL;
			}
						
		  $this->basket->calculate_shipping();
						
			$data = array(
				'code' => $this->basket->promotion_code->code,
				'reward_item' => $reward_item,
				'shipping' => number_format($this->basket->delivery_option->retail_price(), 2),
				'discount_amount' => number_format($this->basket->calculate_discount(), 2),
				'basket_total' => number_format($this->basket->calculate_total(), 2),
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
	 	
	 	if ( ! Caffeine::modules('reward_points'))
	 	{
  	 	throw new Kohana_Exception('The Reward Points module is not enabled.');
	 	}
	 	
	 	if ( ! Auth::instance()->logged_in('customer'))
	 	{
  	 	throw new Kohana_Exception('Customer is not logged in.');
	 	}
	 	
	 	$this->basket->use_reward_points($_POST['use_reward_points']);
	 		  
	  //array to send remaining points and value back to basket
	  $data = array(
  	  'basket_discount' => number_format($this->basket->calculate_discount(), 2),
  	  'basket_total' => number_format($this->basket->calculate_total(), 2)
    );
  	  
  	echo json_encode($data);
	}
	
	public function action_add_customer_referral_code()
	{
	  $this->auto_render = FALSE;
	  
  	$customer = Model_Customer::find_by_referral_code($_POST['code']);
  	
  	if ($customer->loaded())
  	{
    	$this->basket->use_referral_code($_POST['code']);
      $data = array(
    	 'code' => $_POST['code'],
    	);
  	}
  	else
  	{
      $data = array(
  	   'code' => $_POST['code'],
  	  );
  	}
  	
    echo json_encode($data);	
	}
}