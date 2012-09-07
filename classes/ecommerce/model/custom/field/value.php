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
			'document' => new Field_String,
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
		$custom_field_value = Jelly::select('custom_field_value')->where('custom_field_id', '=', $custom_field_id)->where('object_id', '=', $object_id)->load();
		
		if ( ! $custom_field_value->loaded())
		{
			$custom_field_value->object_id = $object_id;
			$custom_field_value->custom_field = $custom_field_id;
		}
		//Uploading files custom field
		if ($_FILES)
		  {
          $directory_parts = array(
      			'documents',
      			'products',
      			date('Y'),
      			date('m'),
      		);
      		$directory = DOCROOT . '/';
      		foreach ($directory_parts as $dir)
      		{
      			$directory .= $dir.'/';
      			if ( ! is_dir($directory))
      			{
      				mkdir($directory);
      			}
      		}
      		$field = $_POST['uploadfield'];
      		
      		$obj = $_POST['uploadobject'];
      		
      		$ext = pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION);
      		
      		$target_path = DOCROOT . "documents/products/" . date('Y') . '/' . date('m') . '/';
      		
      		$target_path = $target_path . basename( $_FILES['document']['name']); 
      		
      		$link_path = "documents/products/" . date('Y') . '/' . date('m') . '/' . $obj . '.' . $ext;
      		     		
      		if (move_uploaded_file($_FILES['document']['tmp_name'], $link_path))
      		{
      		Model_Custom_Field_Value::update($field, $obj, $link_path);
        	echo $target_path;
        	
      		  } 
          }
		
		$custom_field_value->value = $value;
		
		return $custom_field_value->save();
	}
	
	public function delete($key = FALSE)
	{
	   
	   $i = $this->value;
	   
  	 unlink(DOCROOT . $i); 
  
		return parent::delete($key);
		
	}

	
}