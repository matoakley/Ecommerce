<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Event extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->fields(array(
				'id' => new Field_Primary,
				
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
}