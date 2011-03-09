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
			
			$item = $this->basket->add_item($product_id, $quantity);
		}
		
		if (Request::$is_ajax)
		{
			$data = array(
				'basket_items' => $this->basket->count_items(),
				'basket_subtotal' => $this->basket->calculate_subtotal(),
				'line_items' => $item->quantity,
				'line_total' => number_format(($item->product->retail_price() * $item->quantity), 2),
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
		$this->basket->delivery_option = $_POST['delivery_option'];
		$this->basket->save($this->session->get('basket_id'));
		
		echo $this->basket->delivery_option->price;
	}
	
	public function action_update_delivery_option()
	{
		$this->auto_render = FALSE;
		echo $this->basket->update_delivery_option($_POST['id']);
	}
	
	public function action_update_total()
	{
		$this->auto_render = FALSE;		
		echo $this->basket->calculate_total();
	}
	
}