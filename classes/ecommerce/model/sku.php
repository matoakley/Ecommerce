<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Sku extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('skus')
			->fields(array(
				'id' => new Field_Primary,
				'product' => new Field_BelongsTo,
				'sku' => new Field_String,
				'product_options' => new Field_ManyToMany,
				'price' => new Field_Float(array(
					'places' => 4,
					'rules' => array(
						'not_empty' => NULL,
					),
				)),
				'stock' => new Field_Integer(array(
					'default' => 0,
				)),				
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
	
	public static $statuses = array(
		'active', 'disabled',
	);
	
	public static function create_with_options($product, $options)
	{
		$sku = Jelly::factory('sku');
		$sku->product = $product;
		$sku->price = 0;
		$sku->stock = 0;
		$sku->status = 'disabled';
		$sku->add('product_options', $options);
		
		return $sku->save();
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
		$this->price = Currency::deduct_tax($data['price'], Kohana::config('ecommerce.vat_rate'));
		$this->stock = $data['stock'];
		$this->sku = $data['sku'];
		$this->status = $data['status'];
		
		return $this->save();
	}

	public function remove_from_stock($quantity = 1)
	{
		$this->stock = ($this->stock - $quantity >= 0) ? $this->stock - $quantity : 0;
		$this->save();
	}
}