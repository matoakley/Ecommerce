<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Customer_Communication extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->sorting(array('created' => 'DESC'))
			->fields(array(
				'id' => new Field_Primary,
				'customer' => new Field_BelongsTo,
				'type' => new Field_String(array(
					'rules' => array(
						'not_empty' => NULL,
					),
				)),
				'title' => new Field_String(array(
					'rules' => array(
						'not_empty' => NULL,
					),
				)),
				'text' => new Field_Text,
				'date' => new Field_Timestamp(array(
					'format' => 'Y-m-d H:i:s',
				)),
				'user' => new Field_BelongsTo,
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
	
	public static $types = array(
		'email',
		'telephone',
		'mail',
		'note',
	);
	
	public static function create_communication_for_customer($customer, $data)
	{
		$communication = Jelly::factory('customer_communication');
		
		$communication->customer = $customer;
		$communication->user = Auth::instance()->get_user();
		
		if ( ! in_array($data['type'], self::$types))
		{
			throw new Kohana_Exception('Unkown Customer Communicaition type');
		}
		$communication->type = $data['type'];
		
		$communication->title = $data['title'];
		$communication->text = $data['text'];
		$communication->date = $data['date'];
		
		return $communication->save();
	}
}