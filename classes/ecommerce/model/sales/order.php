<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Sales_Order extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('sales_orders')
			->fields(array(
				'id' => new Field_Primary,
				'customer' => new Field_BelongsTo(array(
					'foreign' => 'customer.id',
					'column' => 'customer_id',
				)),
				'billing_address' => new Field_BelongsTo(array(
					'foreign' => 'address.id',
					'column' => 'billing_address_id',
				)),
				'delivery_firstname' => new Field_String,
				'delivery_lastname' => new Field_String,				
				'delivery_address' => new Field_BelongsTo(array(
					'foreign' => 'address.id',
					'column' => 'delivery_address_id',
				)),
				'delivery_option' => new Field_BelongsTo(array(
					'foreign' => 'delivery_option.id',
					'column' => 'delivery_option_id',
				)),
				'status' => new Field_String,
				'order_total' => new Field_Float(array(
					'places' => 2,
				)),
				'items' => new Field_HasMany(array(
					'foreign' => 'sales_order_item.sales_order_id',
				)),
				'ip_address' => new Field_String,
				'hsbc_order_hash' => new Field_String,
				'hsbc_cpi_results_code' => new Field_Integer,
				'basket' => new Field_HasOne,
				'created' =>  new Field_Timestamp(array(
					'auto_now_create' => TRUE,
					'format' => 'Y-m-d H:i:s',
				)),
				'modified' => new Field_Timestamp(array(
					'auto_now_update' => TRUE,
					'format' => 'Y-m-d H:i:s',
				)),
				'deleted' => new Field_Timestamp(array(
					'format' => 'Y-m-d H:i:s',
				)),
			));
	}

	public static $statuses = array(
		'awaiting_payment',
		'payment_received',
		'order_cancelled',
		'fraud_shield_review',
		'problem_occurred',
	);

	public static function create_from_basket($basket, $customer, $billing_address, $delivery_address, $delivery_name)
	{		
		$sales_order = Jelly::factory('sales_order');
		$sales_order->customer = $customer;
		$sales_order->billing_address = $billing_address;
		$sales_order->delivery_address = $delivery_address;
		$sales_order->delivery_option = $basket->delivery_option;
		$sales_order->delivery_firstname = $delivery_name['delivery_firstname'];
		$sales_order->delivery_lastname = $delivery_name['delivery_lastname'];
		$sales_order->status = 'awaiting_payment';
		$sales_order->order_total = $basket->calculate_total();
		$sales_order->ip_address = $_SERVER['REMOTE_ADDR'];
		$sales_order->basket = $basket;
		$sales_order->save();
		
		foreach ($basket->items as $basket_item)
		{
			Model_Sales_Order_Item::create_from_basket($sales_order, $basket_item);
		}
		
		$session = Session::instance();
		$session->delete('basket_id');
		$session->set('sales_order_id', $sales_order->id);
		
		return $sales_order;
	}
	
	public static function load($id = FALSE)
	{
		if ($id)
		{
			return Jelly::select('sales_order', $id);
		}
		else
		{
			return Jelly::select('sales_order', Session::instance()->get('sales_order_id'));
		}
	}
	
	public static function process_payment_result($sales_order_id, $cpi_results_code, $order_hash)
	{
		$sales_order = Jelly::select('sales_order', $sales_order_id);
		$sales_order->hsbc_order_hash = $order_hash;
		$sales_order->hsbc_cpi_results_code = $cpi_results_code;
		
		if ($sales_order->hsbc_cpi_results_code == 0)
		{
		 	$sales_order->status = 'payment_received';
			$sales_order->send_confirmation_email();
		}
		elseif ($sales_order->hsbc_cpi_results_code == 9)
		{
			$sales_order->status = 'fraud_shield_review';
			$sales_order->send_confirmation_email();
		}
		elseif (in_array($sales_order->hsbc_cpi_results_code, array(1, 2, 3, 5, 6, 7, 8, 10, 11, 14, 15, 16)))
		{
			$sales_order->status = 'order_cancelled';
		}
		else
		{
			$sales_order->status = 'problem_occurred';
		}
		
		return $sales_order->save();
	}
	
	public function send_confirmation_email()
	{
		Email::connect();

		$message = Twig::factory('templates/emails/order_confirmation.html');
		$message->sales_order = $this;

		Email::send($this->customer->email, array('sales@southwoldpharmacy.co.uk' => 'Southwold Pharmacy'), 'Your Order Confirmation from ' . Kohana::config('ecommerce.site_name'), $message, true);

	}
}