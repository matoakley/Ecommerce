<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Custom_Field_Value extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->fields(array(
			'id' => new Field_Primary,
			'custom_field' => new Field_BelongsTo,
			'object_id' => new Field_Integer,
			'value' => new Field_Text,
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
	
	public static function update($custom_field_id, $object_id, $value)
	{
		$custom_field_value = Jelly::select('custom_field_value')->where('custom_field_id', '=', $custom_field_id)->load();
		
		if ( ! $custom_field_value->loaded())
		{
			$custom_field_value->object_id = $object_id;
			$custom_field_value->custom_field = $custom_field_id;
		}
		
		$custom_field_value->value = $value;
		
		return $custom_field_value->save();
	}
}