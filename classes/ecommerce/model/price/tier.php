<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Price_Tier extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->sorting(array('name' => 'ASC'))
			->fields(array(
				'id' => new Field_Primary,
				'name' => new Field_String(array(
					'rules' => array(
						'not_empty' => NULL,
					),
				)),
				'description' => new Field_String,
				'customers' => new Field_HasMany,
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
	
	public function update($data)
	{
		$this->name = $data['name'];
		$this->description = $data['description'];
	
		return $this->save();
	}
	
	public function delete($key = NULL)
	{
		// Loop through and delete all prices associated to tier
		
		return parent::delete($key);
	}
}