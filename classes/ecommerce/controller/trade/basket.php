<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Trade_Basket extends Controller_Trade_Application
{
	public function before()
	{
		if ( ! Caffeine::modules('sales_orders'))
		{
			throw new Kohana_Exception('The "sales_orders" module is not enabled');
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
				'basket_vat' => $this->basket->calculate_vat(),
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
}