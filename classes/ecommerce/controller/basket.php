<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Basket extends Controller_Application
{
	public function action_view()
	{
		if ($_POST)
		{
			if (isset($_POST['checkout_x']))
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
	
	public function action_add_item($product_id = FALSE, $quantity = FALSE)
	{
		// This function should be called over AJAX, else just process and redirect to action_view.
		$this->auto_render = FALSE;
		
		if (isset($_POST['basket_item']) OR ($product_id AND $quantity))
		{	
			$product_id = ($product_id) ? $product_id : $_POST['basket_item']['product_id'];
			$quantity = ($quantity) ? $quantity : $_POST['basket_item']['qty'];
			$product_options = isset($_POST['basket_item']['options']) ? $_POST['basket_item']['options'] : NULL;
			
			$item = $this->basket->add_item($product_id, $quantity, $product_options);
		}
		
		if (Request::$is_ajax)
		{
			$data = array(
				'basket_items' => $this->basket->count_items(),
				'basket_subtotal' => $this->basket->calculate_subtotal(),
				'line_items' => ($item !== 0) ? $item->quantity : 0,
				'line_total' => ($item !== 0) ? number_format(($item->product->retail_price() * $item->quantity), 2) : 0,
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
				'basket_subtotal' => $this->basket->calculate_subtotal(),
				'line_items' => ($item !== 0) ? $item->quantity : 0,
				'line_total' => ($item !== 0) ? number_format(($item->product->retail_price() * $item->quantity), 2) : 0,
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
		if (isset($_POST['id']))
		{
			echo $this->basket->update_delivery_option($_POST['id']);
		}
		else
		{
			echo 'false';
		}
	}
	
	public function action_update_total()
	{
		$this->auto_render = FALSE;		
		
		$data = array(
			'basket_total' => $this->basket->calculate_total(),
			'discount' => $this->basket->calculate_discount(),
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
			echo $_POST['code'];
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
	
}