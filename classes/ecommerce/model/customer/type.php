<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Customer_Type extends Model_Application
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
				'description' => new Field_Text,
				'customers' => new Field_ManyToMany,
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
	
	/*
	 * Helper method to reduce workload of count in templates
	 */
	public function customer_count()
	{
		return $this->get('customers')->count();
	}
	
	public function update($data)
	{
		$this->name = $data['name'];
		$this->description = $data['description'];
		return $this->save();	
	}
}