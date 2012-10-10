<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Delivery_Option extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
  {
		$meta->table('delivery_options')
			->fields(array(
				'id' => new Field_Primary,
				'name' => new Field_String(array(
					'rules' => array(
						'not_empty' => NULL,
					),
				)),
				'price' => new Field_Float(array(
					'places' => 4,
					'rules' => array(
						'not_empty' => NULL,
					),
				)),
				'status' => new Field_String,
				'featured' => new Field_Boolean,
				'customer_selectable' => new Field_Boolean,
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
  
  public static $statuses = array(
	  'active',
		'disabled',
	);

	public static $searchable_fields = array(
		'filtered' => array(
			'status' => array(
				'field' => 'status',
			),
		),
		'search' => array(
			'name',
			'price',
		),
	);

	public static function available_options()
	{
		return Jelly::select('delivery_option')->where('status', '=', 'active')->execute();
	}

	/**
	 * Returns the Retail Price of a product after adding VAT.
	 *
	 * @return  float
	 */
	public function retail_price()
	{
		return Currency::add_tax($this->price, Kohana::config('ecommerce.vat_rate'));
	}
	
	public function update($data)
	{
		$this->name = $data['name'];
		$this->price = Currency::deduct_tax($data['price'], Kohana::config('ecommerce.vat_rate'));
		$this->status = $data['status'];
		$this->featured = isset($data['featured']);
		$this->customer_selectable = isset($data['customer_selectable']);
	
		return $this->save();
	}
}