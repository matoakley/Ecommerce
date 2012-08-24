<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Vat_Code extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->sorting(array('code' => 'ASC'))
			->fields(array(
				'id' => new Field_Primary,
				'code' => new Field_String(array(
					'rules' => array(
						'not_empty' => NULL,		
					),
				)),
				'description' => new Field_String,
				'value' => new Field_Float(array(
					'places' => 4,
				)),
				'products' => new Field_HasMany,
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

	public static $searchable_fields = array(
		'filtered' => array(),
		'search' => array(
			'code',
		),
	);
	
	public function update($data)
	{
		$this->code = $data['code'];
		$this->description = $data['description'];
		$this->value = $data['value'];
		$this->save();
	}
}