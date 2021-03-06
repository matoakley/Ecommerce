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
				'delivery_option_net_price' => new Field_Float(array(
					'places' => 4,
				)),
				'delivery_option_price' => new Field_Float(array(
					'places' => 4,
				)),
				'promotion_code' => new Field_BelongsTo,
				'promotion_code_code' => new Field_String,
				'discount_amount' => new Field_Float(array(
					'places' => 4,
					'default' => 0,
				)),
				'reward_points_used' => new Field_Integer,
				'reward_points_used_value' => new Field_Float(array(
				  'places' => 4,
				)),
				'reward_points_earned' => new Field_Integer,
				'reward_points_processed' => new Field_Boolean(array(
				  'default' => FALSE,
				)),
				'type' => new Field_String,
				'status' => new Field_String,
				'order_subtotal' => new Field_Float(array(
					'places' => 4,
				)),
				'order_vat' => new Field_Float(array(
					'places' => 4,
				)),
				'order_total' => new Field_Float(array(
					'places' => 4,
				)),
				'items' => new Field_HasMany(array(
					'foreign' => 'sales_order_item.sales_order_id',
				)),
				'ip_address' => new Field_String,
				'user_agent' => new Field_String,
				'basket' => new Field_HasOne,
				'notes' => new Field_HasMany(array(
					'foreign' => 'sales_order_note.sales_order_id',
				)),
				'ref' => new Field_String,
				'user' => new Field_BelongsTo,
				'invoice_terms' => new Field_Integer,
				'exported_to_sage' => new Field_Timestamp(array(
					'format' => 'Y-m-d H:i:s',
				)),
				'invoiced_on' => new Field_Timestamp(array(
					'format' => 'Y-m-d H:i:s',
				)),
				'customer_referral_code' => new Field_String,
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
		'retail' => array(
			'awaiting_payment',
			'problem_occurred',
			'payment_received',
			'complete',
			'order_cancelled',
		),
		'commercial' => array(
			'invoice_generated',
			'invoice_sent',
			'complete',
			'order_cancelled',
		),
		'booking' => array(
		  'awaiting_payment',
			'problem_occurred',
			'payment_received',
			'payment_due',
			'complete',
			'order_cancelled',
		),
	);
	
	public static $searchable_fields = array(
		'filtered' => array(
			'status' => array(
				'field' => 'status',
			),
			'type' => array(
				'field' => 'type',
			),
		),
		'search' => array(
			'id',
		),
	);
	
	public static $types = array(
		'commercial',
		'retail',
	);
	
	protected function calculate_vat_and_subtotal()
	{
		$vat = 0;
	
		foreach ($this->items as $item)
		{
			$vat += $item->total_price - $item->net_total_price;
		}
	
		$this->order_vat = $vat + ($this->delivery_option_price - $this->delivery_option_net_price);
		$this->order_subtotal = $this->order_total - $vat;
		
		return $this->save();
	}
	
	protected function calculate_total()
	{
		$total = 0;
		foreach ($this->items as $item)
		{
			$total += $item->total_price;
		}
		$total += $this->delivery_option_price;
		$this->order_total = $total;
		return $this->save();
	}
	
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
		// Final check that basket shipping total is correct if using cusotm calculations
		Model_Basket::instance()->calculate_shipping();
	
		$sales_order = Jelly::factory('sales_order');
		$sales_order->customer = $customer;
		$sales_order->billing_address = $billing_address;
		$sales_order->delivery_address = $delivery_address;
		$sales_order->delivery_option = $basket->delivery_option;
		$sales_order->delivery_option_name = $basket->delivery_option->name;
		$sales_order->delivery_option_net_price = $basket->delivery_option->price;
		$sales_order->delivery_option_price = $basket->delivery_option->retail_price();
		$sales_order->delivery_firstname = $delivery_name['delivery_firstname'];
		$sales_order->delivery_lastname = $delivery_name['delivery_lastname'];
		$sales_order->status = 'awaiting_payment';
		$sales_order->ip_address = $_SERVER['REMOTE_ADDR'];
		$sales_order->basket = $basket;
		$sales_order->type = 'retail';
		$sales_order->user_agent = Request::$user_agent;
		
		$sales_order->save();
	
		// Handle any promotional codes that are added to the basket.
		if ($basket->promotion_code_reward->loaded())
		{
			$sales_order->promotion_code = $basket->promotion_code;
			$sales_order->promotion_code_code = $basket->promotion_code->code;
			
			switch ($basket->promotion_code_reward->reward_type)
			{
				case 'discount':
					$sales_order->discount_amount = $basket->calculate_discount();
					$sales_order->save();
					break;
					
				case 'item':
					Model_Sales_Order_Item::create_from_promotion_code_reward($sales_order, $basket->promotion_code_reward);
					break;
					
				default:
					break;
			}

			$sales_order->save();
		}
		
		foreach ($basket->items as $basket_item)
		{
			Model_Sales_Order_Item::create_from_basket($sales_order, $basket_item);
		}
		
		$sales_order->order_total = $basket->calculate_total();
		$sales_order->calculate_vat_and_subtotal();
		
		if (Caffeine::modules('reward_points'))
		{
  	  if ($basket->use_reward_points)
  	  {
    	  $sales_order->reward_points_used = $basket->max_reward_points();
    	  $sales_order->reward_points_used_value = $basket->calculate_discount_for_reward_points();
		  }
		  $sales_order->reward_points_earned = floor($sales_order->order_total - $sales_order->delivery_option_price) * Model_Reward_Points_Profile::load(1)->points_per_pound;
		  //ORIGINAL $sales_order->order_subtotal / Model_Reward_Points_Profile::load(1)->points_per_pound;
		  
		  $sales_order->customer_referral_code = $basket->customer_referral_code;
		}
		
		if ($basket->promotion_code_reward->loaded())
		  {
  		  $basket->promotion_code->redeem();
		  }
		
		$session = Session::instance();
		$session->delete('basket_id');
		$session->set('sales_order_id', $sales_order->id);
		
		return $sales_order->save();
	}
	
	public static function create_trade_from_basket($basket, $customer, $delivery_address)
	{
		// Final check that basket shipping total is correct if using cusotm calculations
		Model_Basket::instance()->calculate_shipping();
	
		$sales_order = Jelly::factory('sales_order');
		$sales_order->customer = $customer;
		$sales_order->billing_address = $customer->default_billing_address;
		$sales_order->delivery_address = $delivery_address;
		$sales_order->delivery_option = $basket->delivery_option;
		$sales_order->delivery_option_name = $basket->delivery_option->name;
		$sales_order->delivery_option_net_price = $basket->delivery_option->price;
		$sales_order->delivery_option_price = $basket->delivery_option->retail_price();
		$sales_order->status = 'invoice_generated';
		$sales_order->ip_address = $_SERVER['REMOTE_ADDR'];
		$sales_order->basket = $basket;
		$sales_order->type = 'commercial';
		$sales_order->invoice_terms = $customer->invoice_terms == NULL ? $customer->invoice_terms : Kohana::config('ecommerce.default_invoice_terms');
		$sales_order->user_agent = Request::$user_agent;
		
		if (Auth::instance()->logged_in('customer'))
		{
  		$sales_order->user = Auth::instance()->get_user();
		}
		
		$sales_order->save();
		
		foreach ($basket->items as $basket_item)
		{
			Model_Sales_Order_Item::create_from_basket($sales_order, $basket_item);
		}
		
		//save the baskets referral code against the customer
		$customer->customer_referral_code = $basket->customer_referral_code;
		$customer->save();
		
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
		}
		
		$sales_order->calculate_vat_and_subtotal();
		$sales_order->calculate_total();
		
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
		
	public static function monthly_sales_orders($month = FALSE)
	{
	 $this_month = date('m');
	 
		if ( ! $month)
		{
			$month = date('m');
		}
		$year = date('Y');
						
		//funky shit to stop it resetting in january
		if ($this_month <= 6 AND $month != intval($this_month) AND $month > 6)
			{
			   $last_year = date('Y', strtotime('last year'));
			
			$sql =  "SELECT COUNT(*) as orders
			        FROM sales_orders
			        WHERE status IN ('payment_received', 'complete')
			        AND EXTRACT(MONTH FROM created) = $month
			        AND EXTRACT(YEAR FROM created) = $last_year";
			}
		else
			{
			 $sql = "SELECT COUNT(*) as orders
  						FROM sales_orders
  						WHERE status IN ('payment_received', 'complete')
  						AND EXTRACT(MONTH FROM created) = $month
  						AND EXTRACT(YEAR FROM created) = $year";	
			}
												
		$result = Database::instance()->query(Database::SELECT, $sql, FALSE)->as_array();
		
		return ( ! is_null($result[0]['orders'])) ? $result[0]['orders'] : 0;
	}
	
	public static function thismonths_orders($month = FALSE)
	{
	
	   $month = date('m');
	   $year = date('Y');
		
		$sql = "SELECT COUNT(*) as thismonthsorders
						FROM sales_orders
						WHERE status IN ('payment_received', 'complete')
						AND EXTRACT(MONTH FROM created) = $month
						AND EXTRACT(YEAR FROM created) = $year";
						
		$result = Database::instance()->query(Database::SELECT, $sql, FALSE)->as_array();
		
		return ( ! is_null($result[0]['thismonthsorders'])) ? $result[0]['thismonthsorders'] : 0;
	}
	
	public static function alltime_sales_orders($month = FALSE)
	{
				
		$sql = "SELECT COUNT(*) as alltimeorders
						FROM sales_orders
						WHERE status IN ('payment_received', 'complete')";
						
		$result = Database::instance()->query(Database::SELECT, $sql, FALSE)->as_array();
		
		return ( ! is_null($result[0]['alltimeorders'])) ? $result[0]['alltimeorders'] : 0;
	}
	
  	public static function daily_order_count()
  	{
  	  $num = date("t");
    	$query = "SELECT COUNT(created) AS order_no, 
            DATE(created) AS order_day 
            FROM 
                sales_orders 
            WHERE status IN ('payment_received', 'complete') 
            GROUP BY 
                order_day 
            ORDER BY 
                created 
            DESC
            LIMIT $num";
              
      $results = Database::instance()->query(Database::SELECT, $query, FALSE);
      
          
      $orders = array();
  		foreach ($results as $result)
  		{
        $value = $result['order_no'];
        $key = $result['order_day'];
        $keystripped = str_replace("-","", $key);
        $orders[intval($keystripped)] = intval($value); 
  		}
  
      return $orders;
    }		
      
    public static function thirtydays()
    {
       //CLEAR OUTPUT FOR USE
       $output = array();
  
        //SET CURRENT DATE
       $month = date("m");
       $day = date("d");
       $year = date("Y");
       $num = date("t", mktime(0,0,0, date("n") - 1)); //get the days number from the LAST month to keep it right.
       
        //LOOP THROUGH DAYS
       for($i=0; $i<=($num); $i++){
            $results[] = date('Ymd',mktime(0,0,0,$month,($day-$i),$year));
       }
       
       foreach ($results as $result)
       {
           $output[$result] = 0;
       }
       //RETURN DATE ARRAY
       return $output;
    }
	
	public static function create_commercial_sales_order($data)
	{
		$sales_order = Jelly::factory('sales_order');
		
		$customer = Model_Customer::load($data['customer']);
		
		if ( ! $customer->loaded())
		{
			throw new Kohana_Exception('Unable to find Customer.');
		}
		
		$sales_order->type = "commercial";
		$sales_order->customer = $customer;
		$sales_order->billing_address = $customer->default_billing_address;
		$sales_order->delivery_address = $data['delivery_address'];
		$sales_order->delivery_option_name = 'Commercial Delivery';
		$sales_order->delivery_option_net_price = $data['delivery_charge'];
		$sales_order->delivery_option_price = Currency::add_tax($data['delivery_charge'], Kohana::config('ecommerce.vat_rate'));
		$sales_order->status = 'invoice_generated';
		$sales_order->ip_address = Request::$client_ip;
		$sales_order->ref = $data['ref'];
		$sales_order->user = Auth::instance()->get_user();
		$sales_order->invoice_terms = $data['invoice_terms'];
		$sales_order->save();
		
		foreach ($data['skus'] as $sku)
		{
			$line = Model_Sales_Order_Item::create_commercial_sales_order_item($sales_order, $sku);
			$sales_order->order_total += $line->total_price;
		}
		
		$sales_order->calculate_vat_and_subtotal();
		$sales_order->calculate_total();
		
		return $sales_order->save();
	}

	public function generate_invoice()
	{
		if ( ! $this->type == 'commercial')
		{
			throw new Kohana_Exception('Not a Commercial Sales Order.');
		}
	}
	
	public function send_confirmation_email()
	{
		Caffeine_Email::connect();

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

		return Caffeine_Email::send($to, array(Kohana::config('ecommerce.email_from_address') => Kohana::config('ecommerce.email_from_name')), 'Your order confirmation from ' . Kohana::config('ecommerce.site_name'), $message, true);
	}
	
	public function send_shipped_email()
	{
		Caffeine_Email::connect();
		
		$message = Twig::factory('emails/order_shipped.html');
		$message->sales_order = $this;
		$message->modules = Kohana::config('ecommerce.modules');
		$message->site_name = Kohana::config('ecommerce.site_name');

		$to = array(
			'to' => array($this->customer->email, $this->customer->firstname . ' ' . $this->customer->lastname),
		);

		return Caffeine_Email::send($to, array(Kohana::config('ecommerce.email_from_address') => Kohana::config('ecommerce.email_from_name')), 'Your order from ' . Kohana::config('ecommerce.site_name') . ' has been shipped', $message, true);
	}
	
	
	public function set_invoiced_on_date()
	{
  	if ( ! $this->invoiced_on OR $this->invoiced_on === "0000-00-00 00:00:00")
  	{
    	$this->invoiced_on = date('m/d/Y');
    	$this->save();
  	}
	}
	
	
	public function update_status($status)
	{
		if (in_array($status, self::$statuses[$this->type]))
		{
			$user = Auth::instance()->get_user();
			if (is_object($user) AND $user->loaded())
			{
				$note_text = $user->firstname.' '.$user->lastname.' changed order status from '. ucwords(Inflector::humanize($this->status)).' to '.ucwords(Inflector::humanize($status)).'.';
			}
			else
			{
				$note_text = 'System changed order status from '.ucwords(Inflector::humanize($this->status)).' to '.ucwords(Inflector::humanize($status)).'.';
			}
			
			$this->status = $status;
			
			foreach ($this->items as $sales_order_item)
			  {
  			  $sales_order_item->status = $this->status;
  			  $sales_order_item->save();
			  }
			
			$this->add_note($note_text, TRUE);
			
			
			// If we are controlling stock and setting an order to payment received then we should decrement the stock count of each item
			if (Caffeine::modules('stock_control'))
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
			
			// If we are using reward points then remove any points used and assign points earned
			// once their order has been paid for
			if (Caffeine::modules('reward_points') AND ! $this->reward_points_processed)
			{ 
  			if ($status == 'payment_received')
  			 {
  			   $this->customer->remove_reward_points($this->reward_points_used)->add_reward_points($this->reward_points_earned);
  			 
  			   // Referral points for customer and referrer...
  			  if ($this->customer_referral_code)
  			  {
    			  $referring_customer = Model_Customer::find_by_referral_code($this->customer_referral_code);
    			  
    			  if ($referring_customer->loaded())
    			  {
    			    $reward_points_profile = Model_Reward_Points_Profile::load(1);
    			  
      			  $referring_customer->add_reward_points($reward_points_profile->customer_referral);
      			  $this->customer->add_reward_points($reward_points_profile->new_customer_referral);
    			  }
  			  }
  			  
  			   $this->reward_points_processed = TRUE; 
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
	
	public function send_invoice($copy_to_administrator = FALSE)
	{
		$email = Caffeine_Email::connect();
		
		$site_name = Kohana::config('ecommerce.trade_site_name') != '' ? Kohana::config('ecommerce.trade_site_name') : Kohana::config('ecommerce.site_name');
		$from_address = Kohana::config('ecommerce.commercial_email_from_address') != '' ? Kohana::config('ecommerce.commercial_email_from_address') : Kohana::config('ecommerce.email_from_address');
		$from_name = Kohana::config('ecommerce.commercial_email_from_name') != '' ? Kohana::config('ecommerce.commercial_email_from_name') : Kohana::config('ecommerce.email_from_name');
		
		$content = Twig::factory('emails/invoice.html');
		$content->sales_order = $this;
		$content->site_name = $site_name;
		
		$pdf_template = Twig::factory('admin/sales/orders/generate_invoice');
		$pdf_template->base_url = URL::site();
		$pdf_template->sales_order = $this;
		
    $html2pdf = new HTML2PDF('P','A4','en');
    $html2pdf->WriteHTML($pdf_template->render());

		$message = Swift_Message::newInstance('Your invoice from '.$site_name, $content, 'text/html', 'utf-8');
		$message->setFrom(array($from_address => $from_name))
						->addTo($this->customer->email, $this->customer->firstname.' '.$this->customer->lastname)
						->attach(Swift_Attachment::newInstance($html2pdf->Output('', TRUE), 'Invoice '.$this->id.'.pdf', 'application/pdf'));
						
		if ($copy_to_administrator)
		{
			$copy_to = Kohana::config('ecommerce.copy_trade_area_order_confirmations_to') != '' ? Kohana::config('ecommerce.copy_trade_area_order_confirmations_to') : Kohana::config('ecommerce.copy_order_confirmations_to');
			$message->addTo($copy_to);
		}
						
		$email->send($message);
		
		// If this is the fist time that the invoice has been
		// generated then set invoiced on as now.
		if ( ! $this->invoiced_on )
		{
			$this->update_status('invoice_sent');
			$this->invoiced_on = time();
		}
		
		return $this->save();
	}
	
	public function update($data)
	{
		// Only update the status if it has actually changed
		if ($this->status != $data['status'])
		{
			$this->update_status($data['status']);
		}
		
		if (Caffeine::modules('commercial_sales_orders') AND isset($data['invoiced_on']))
		{
			$this->invoiced_on = ($data['invoiced_on'] != '') ? strtotime(str_replace('/', '-', $data['invoiced_on'])) : NULL;
		}
	
		return $this->save();
	}

	public function update_delivery_option($option)
	{
		if (is_int($option))
		{
			$option = Model_Delivery_Option::load($option);
		}
		
		$this->delivery_option = $option;
		$this->delivery_option_name = $option->name;
		$this->delivery_option_price = $option->retail_price();
		
		return $this->save()->calculate_total();
	}
	
	public function invoice_due_date()
	{
		return $this->invoiced_on + (86400 * $this->invoice_terms);
	}
	
	public static function monthly_completed_total($month = FALSE)
	{
	  $this_month = date('m');
	  
		if ( ! $month)
		{
			$month = date('m');
		}
		
		$year = date('Y');
		//funky shit to stop it resetting in january
		if ($this_month <= 6 AND $month != $this_month AND $month > 6)
			{
			   $last_year = date('Y', strtotime('last year'));
			   
      		$sql = "SELECT SUM(order_total) as total
      						FROM sales_orders
      						WHERE status IN ('payment_received', 'complete', 'invoice_sent', 'invoice_generated', 'dispatched')
      						AND deleted IS NULL
      						AND EXTRACT(MONTH FROM created) = $month
      						AND EXTRACT(YEAR FROM created) = $last_year";
      }
    else
      {
          $sql = "SELECT SUM(order_total) as total
        						FROM sales_orders
        						WHERE status IN ('payment_received', 'complete', 'invoice_sent', 'invoice_generated', 'dispatched')
        						AND deleted IS NULL
        						AND EXTRACT(MONTH FROM created) = $month
        						AND EXTRACT(YEAR FROM created) = $year";
      }
						
		$result = Database::instance()->query(Database::SELECT, $sql, FALSE)->as_array();
		
		return ( ! is_null($result[0]['total'])) ? $result[0]['total'] : 0;
	}
	
	public static function overall_completed_total()
	{
		$sql = "SELECT SUM(order_total) as total
						FROM sales_orders
						WHERE status IN ('payment_received', 'complete', 'invoice_sent', 'invoice_generated', 'dispatched')
						AND deleted IS NULL";
						
		$result = Database::instance()->query(Database::SELECT, $sql, FALSE)->as_array();
		
		return ( ! is_null($result[0]['total'])) ? $result[0]['total'] : 0;
	}
	
	public static function retail_monthly_completed_total($month = FALSE)
	{
		if ( ! $month)
		{
			$month = date('m');
		}
		
		$year = date('Y');
		//funky shit to stop it resetting in january
				if (date('m') < 6 AND $month != date('m'))
    			{
    			$last_year = date('Y', strtotime('last year'));
    			
    			$sql = "SELECT SUM(order_total) as total
    						  FROM sales_orders
    						  WHERE status IN ('payment_received', 'complete', 'invoice_sent', 'invoice_generated', 'dispatched')
    						  AND type = 'retail'
    						  AND deleted IS NULL
    						  AND EXTRACT(MONTH FROM created) = $month
    			        AND EXTRACT(YEAR FROM created) = $last_year";
    			}
        else
    			{
    			 $sql = "SELECT SUM(order_total) as total
    						FROM sales_orders
    						WHERE status IN ('payment_received', 'complete', 'invoice_sent', 'invoice_generated', 'dispatched')
    						AND type = 'retail'
    						AND deleted IS NULL
    						AND EXTRACT(MONTH FROM created) = $month
    						AND EXTRACT(YEAR FROM created) = $year";	
    			}
						
		$result = Database::instance()->query(Database::SELECT, $sql, FALSE)->as_array();
		
		return ( ! is_null($result[0]['total'])) ? $result[0]['total'] : 0;
	}
	
		public static function commercial_monthly_completed_total($month = FALSE)
	{
	  $this_month = 6;
	  
		if ( ! $month)
		{
			$month = date('m');
		}
		
		$year = date('Y');
		//funky shit to stop it resetting in january
		if ($this_month <= 6 AND $month != $this_month AND $month > 6)
			{
			   $last_year = date('Y', strtotime('last year'));
			
      		$sql = "SELECT SUM(order_total) as total
      						FROM sales_orders
      						WHERE status IN ('payment_received', 'complete', 'invoice_sent', 'invoice_generated', 'dispatched')
      						AND type = 'commercial'
      						AND deleted IS NULL
      						AND EXTRACT(MONTH FROM created) = $month
      						AND EXTRACT(YEAR FROM created) = $last_year";
      }
    else
      {
             $sql = "SELECT SUM(order_total) as total
          						FROM sales_orders
          						WHERE status IN ('payment_received', 'complete', 'invoice_sent', 'invoice_generated', 'dispatched')
          						AND type = 'commercial'
          						AND deleted IS NULL
          						AND EXTRACT(MONTH FROM created) = $month
          						AND EXTRACT(YEAR FROM created) = $year";

      }
						
		$result = Database::instance()->query(Database::SELECT, $sql, FALSE)->as_array();
		
		return ( ! is_null($result[0]['total'])) ? $result[0]['total'] : 0;
	}
	
		public static function retail_overall_completed_total()
	{
		$sql = "SELECT SUM(order_total) as total
						FROM sales_orders
						WHERE status IN ('payment_received', 'complete', 'invoice_sent', 'invoice_generated', 'dispatched')
						AND type = 'retail'
						AND deleted IS NULL";
						
		$result = Database::instance()->query(Database::SELECT, $sql, FALSE)->as_array();
		
		return ( ! is_null($result[0]['total'])) ? $result[0]['total'] : 0;
	}
	
		public static function commercial_overall_completed_total()
	{
		$sql = "SELECT SUM(order_total) as total
						FROM sales_orders
						WHERE status IN ('payment_received', 'complete', 'invoice_sent', 'invoice_generated', 'dispatched')
						AND type = 'commercial'
						AND deleted IS NULL";
						
		$result = Database::instance()->query(Database::SELECT, $sql, FALSE)->as_array();
		
		return ( ! is_null($result[0]['total'])) ? $result[0]['total'] : 0;
	}
}