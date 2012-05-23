<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Custom_Field extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->fields(array(
			'id' => new Field_Primary,
			'name' => new Field_String(array(
				'rules' => array(
					'not_empty' => NULL,
				),
			)),
			'object' => new Field_String(array(
				'rules' => array(
					'not_empty' => NULL,
				),
				'callbacks' => array(
					'valid' => array('Model_Custom_Field', '_check_valid_object')
				),
			)),
			'show_editor' => new Field_Boolean,
			'values' => new Field_HasMany(array(
				'foreign' => 'custom_field_value.custom_field_id',
			)),
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
	
	public static $objects = array(
		'category',
		'customer',
		'product',
	);
	
	public static function _check_valid_object(Validate $array, $field)
	{	
		if ( ! in_array($array[$field], self::$objects))
		{
			$array->error($field, 'valid');
		}
	}
	
	public function update($data)
	{
		$this->name = $data['name'];
		$this->object = $data['object'];
		$this->show_editor = isset($data['show_editor']);
		return $this->save();
	}
	
	public function value_for_object_id($object_id)
	{
		return $this->get('values')->where('object_id', '=', $object_id)->load()->value;
	}
}