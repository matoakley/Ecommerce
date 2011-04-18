<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Sales_Order_Note extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->sorting(array('created' => 'DESC'))
			->fields(array(
				'id' => new Field_Primary,
				'sales_order' => new Field_BelongsTo,
				'user' => new Field_BelongsTo,
				'is_system' => new Field_Boolean(array(
					'default' => FALSE,
				)),
				'text' => new Field_Text,
				'created' =>  new Field_Timestamp(array(
					'auto_now_create' => TRUE,
					'format' => 'Y-m-d H:i:s',
					'pretty_format' => 'd/m/Y H:i',
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
	
	public static function add_note($sales_order = FALSE, $text = FALSE, $is_system = FALSE)
	{
		if ( ! $text OR ! $sales_order->loaded())
		{
			throw new Kohana_Exception('Invalid sales order or note text.');
		}
		
		$note = Jelly::factory('sales_order_note');
		
		$note->sales_order = $sales_order->id;
		
		$note->text = $text;
		$note->is_system = $is_system;
		
		if ( ! $is_system)
		{
			$note->user = Auth::instance()->get_user()->id;
		}
		
		return $note->save();
	}
}