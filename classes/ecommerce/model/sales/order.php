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
				'delivery_option_name' => new Field_String,
				'delivery_option_price' => new Field_Float(array(
					'places' => 4,
				)),
				'promotion_code' => new Field_BelongsTo,
				'promotion_code_code' => new Field_String,
				'discount_amount' => new Field_Float(array(
					'places' => 4,
					'default' => 0,
				)),
				'status' => new Field_String,
				'order_total' => new Field_Float(array(
					'places' => 2,
				)),
				'items' => new Field_HasMany(array(
					'foreign' => 'sales_order_item.sales_order_id',
				)),
				'ip_address' => new Field_String,
				'basket' => new Field_HasOne,
				'notes' => new Field_HasMany(array(
					'foreign' => 'sales_order_note.sales_order_id',
				)),
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
		'fraud_shield_review', // HSBC specific
		'payment_received',
		'complete',
		'order_cancelled',
		'problem_occurred',
	);
	
	public static function recent_dashboard_orders()
	{
		return Jelly::select('sales_order')
										->where('status', '<>', 'awaiting_payment')
										->where('status', '<>', 'order_cancelled')
										->order_by('created', 'DESC')
										->limit(5)
										->execute();
	}

	public static function create_from_basket($basket, $customer, $billing_address, $delivery_address, $delivery_name)
	{		
		$sales_order = Jelly::factory('sales_order');
		$sales_order->customer = $customer;
		$sales_order->billing_address = $billing_address;
		$sales_order->delivery_address = $delivery_address;
		$sales_order->delivery_option = $basket->delivery_option;
		$sales_order->delivery_option_name = $basket->delivery_option->name;
		$sales_order->delivery_option_price = $basket->delivery_option->retail_price();
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
		
		// Handle any promotional codes that are added to the basket.
		if ($basket->promotion_code_reward->loaded())
		{
			$sales_order->promotion_code = $basket->promotion_code;
			$sales_order->promotion_code_code = $basket->promotion_code->code;
			$basket->promotion_code->redeem();
			
			switch ($basket->promotion_code_reward->reward_type)
			{
				case 'discount':
					$sales_order->discount_amount = $basket->calculate_discount();
					break;
					
				case 'item':
					Model_Sales_Order_Item::create_from_promotion_code_reward($sales_order, $basket->promotion_code_reward);
					break;
					
				default:
					break;
			}
			
			$sales_order->save();
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
	
	public static function process_payment_result($data)
	{
		// Should be implemented on a per driver basis. 
		
		return $sales_order->save();
	}
	
	public static function monthly_completed_total($month = FALSE)
	{
		if ( ! $month)
		{
			$month = date('m');
		}
		
		$year = date('Y');
		
		$sql = "SELECT SUM(order_total) as total
						FROM sales_orders
						WHERE status IN ('payment_received', 'complete')
						AND EXTRACT(MONTH FROM created) = $month
						AND EXTRACT(YEAR FROM created) = $year";
						
		$result = Database::instance()->query(Database::SELECT, $sql, FALSE)->as_array();
		
		return ( ! is_null($result[0]['total'])) ? $result[0]['total'] : 0;;
	}
	
	public static function overall_completed_total()
	{
		$sql = "SELECT SUM(order_total) as total
						FROM sales_orders
						WHERE status IN ('payment_received', 'complete')";
						
		$result = Database::instance()->query(Database::SELECT, $sql, FALSE)->as_array();
		
		return ( ! is_null($result[0]['total'])) ? $result[0]['total'] : 0;;
	}
	
	public function send_confirmation_email()
	{
		Email::connect();

		$message = Twig::factory('emails/order_confirmation.html');
		$message->sales_order = $this;
		$message->site_name = Kohana::config('ecommerce.site_name');

		$to = array(
			'to' => array($this->customer->email, $this->customer->firstname . ' ' . $this->customer->lastname),
		);
		
		$bcc_address = Kohana::config('ecommerce.copy_order_confirmations_to');
		if ($bcc_address != '')
		{
			$to['bcc'] = array($bcc_address, '');
		}

		return Email::send($to, array(Kohana::config('ecommerce.email_from_address') => Kohana::config('ecommerce.email_from_name')), 'Your order confirmation from ' . Kohana::config('ecommerce.site_name'), $message, true);
	}
	
	public function send_shipped_email()
	{
		Email::connect();
		
		$message = Twig::factory('emails/order_shipped.html');
		$message->sales_order = $this;
		$message->site_name = Kohana::config('ecommerce.site_name');

		$to = array(
			'to' => array($this->customer->email, $this->customer->firstname . ' ' . $this->customer->lastname),
		);

		return Email::send($to, array(Kohana::config('ecommerce.email_from_address') => Kohana::config('ecommerce.email_from_name')), 'Your order from ' . Kohana::config('ecommerce.site_name') . ' has been shipped', $message, true);
	}
	
	public function update_status($status)
	{
		if (in_array($status, self::$statuses))
		{
			$user = Auth::instance()->get_user();
			if (is_object($user) AND $user->loaded())
			{
				$note_text = $user->firstname . ' ' . $user->lastname . ' changed order status from ' . ucwords(Inflector::humanize($this->status)) . ' to ' . ucwords(Inflector::humanize($status)) . '.';
			}
			else
			{
				$note_text = 'System changed order status from ' . ucwords(Inflector::humanize($this->status)) . ' to ' . ucwords(Inflector::humanize($status)) . '.';
			}
			
			$this->status = $status;
			
			$this->add_note($note_text, TRUE);
			
			// If we are controlling stock and setting an order to payment received then we should decrement the stock count of each item
			$is_controlling_stock = Kohana::config('ecommerce.modules.stock_control');
			if ($is_controlling_stock)
			{
				if ($status == 'payment_received')
				{
					foreach ($this->items as $item)
					{
						if ($item->sku->loaded())
						{
							$item->sku->remove_from_stock($item->quantity);
						}
					}
				}
			}
			
			return $this->save();
		}
		else
		{
			throw new Kohana_Exception('Unrecognised status.');
		}
	}
	
	public function add_note($text = FALSE, $is_system = FALSE)
	{
		return Model_Sales_Order_Note::add_note($this, $text, $is_system);
	}
}