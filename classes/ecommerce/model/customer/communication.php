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
	
	public static $types = array(
		'email',
		'telephone',
		'mail',
	);
}