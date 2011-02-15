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
				$basket_item = Jelly::select('basket_item')->load($key);				
				
				if ($value > 0)
				{					
					$basket_item
						->set(array(
							'quantity' => $value,											
						))
						->save($key);
				}
				else
				{
					$basket_item->delete();
				}
			}
		}
		
		$this->template->basket = $this->basket;
		$this->template->delivery_options = Model_Delivery_Option::available_options();
		
		$this->add_breadcrumb('/basket', 'Your Basket');
	}
	
	public function action_add_item()
	{
		// This function should be called over AJAX, else just process and redirect to action_view.
		$this->auto_render = FALSE;
		
		if (isset($_POST['basket_item']))
		{			
			$this->basket->add_item($_POST['basket_item']['product_id'], $_POST['basket_item']['qty']);
		}
		
		if (Request::$is_ajax)
		{
			echo $this->basket->count_items();
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