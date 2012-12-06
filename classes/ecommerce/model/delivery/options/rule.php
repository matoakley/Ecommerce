<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Delivery_Options_Rule extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
  {
		$meta->table('delivery_options_rules')
			->fields(array(
				'id' => new Field_Primary,
				'name' => new Field_String,
				'description' => new Field_String,
				'min_basket' => new Field_Float,
				'delivery_option_id' => new Field_BelongsTo(array(
					'foreign' => 'delivery_option.id',
				)),
				'brand_id' => new Field_Integer,
				'status' => new Field_String,
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
		'filtered' => array(
			'status' => array(
				'field' => 'status',
			),
			'brand_id' => array(
			  'field' => 'brand_id',
		  ),
		),
		'search' => array(
			'name',
			'price',
		),
	);
  
  public static $statuses = array(
	  'active',
		'disabled',
	);
	
	public function update($data)
	{
		$this->name = $data['name'];
		$this->min_basket = $data['min_basket'];
		$this->status = $data['status'];
		$this->delivery_option_id = $data['delivery_option_id'];
		$this->description = $data['description'];
	
		return $this->save();
	}
	
}