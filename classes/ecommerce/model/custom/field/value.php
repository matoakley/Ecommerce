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
		$custom_field_value = Jelly::select('custom_field_value')->where('custom_field_id', '=', $custom_field_id)->where('object_id', '=', $object_id)->load();
		
		if ( ! $custom_field_value->loaded())
		{
			$custom_field_value->object_id = $object_id;
			$custom_field_value->custom_field = $custom_field_id;
			$custom_field_value->save();
		}
		
		// Uploading files custom field
		if ($custom_field_value->custom_field->type == 'upload' AND $_FILES['custom_fields']['size'][$custom_field_value->custom_field->id] > 0)
	  {
      $directory_parts = array(
  			'documents',
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
  		
  		$ext = pathinfo($_FILES['custom_fields']['name'][$custom_field_value->custom_field->id], PATHINFO_EXTENSION);
  		
  		// Append timestamp to ID for filename to keep it unique whilst obscure enought to stop
  		// people guessing other potential files on the system
  		$file_path = '/documents/'.date('Y').'/'.date('m').'/'.$custom_field_value->id.time().'.'.$ext;
  		     		
  		if (move_uploaded_file($_FILES['custom_fields']['tmp_name'][$custom_field_value->custom_field->id], DOCROOT.$file_path))
  		{
    		$custom_field_value->value = $file_path;
  		} 
    }
		else
		{
  		$custom_field_value->value = $value;
    }
		
		return $custom_field_value->save();
	}
	
	public function delete($key = FALSE)
	{
  	if ($this->type == 'upload')
  	{
    	unlink(DOCROOT.$this->value); 
    }
    
		return parent::delete($key);
	}

	
}