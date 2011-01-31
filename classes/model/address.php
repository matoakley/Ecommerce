<?php defined('SYSPATH') or die('No direct script access.');

class Model_Address extends Model_Application
{
    public static function initialize(Jelly_Meta $meta)
    {
        $meta->table('addresses')
            ->fields(array(
                'id' => new Field_Primary,
				'customer' => new Field_BelongsTo,
				'is_delivery' => new Field_Boolean,
                'line_1' => new Field_String(array(
					'rules' => array(
						'not_empty' => NULL,
					),
				)),
				'line_2' => new Field_String,
				'town' => new Field_String(array(
					'rules' => array(
						'not_empty' => NULL,
					),
				)),
				'county' => new Field_String(array(
					'rules' => array(
						'not_empty' => NULL,
					),
				)),
				'postcode' => new Field_String,
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

	public static function create($data, $customer_id, $is_delivery = FALSE)
	{
		$data['customer'] = $customer_id;
		$data['is_delivery'] = $is_delivery;
		return Jelly::factory('address')->set($data)->save();
	}
}