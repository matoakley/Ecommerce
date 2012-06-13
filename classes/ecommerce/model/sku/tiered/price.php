<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Sku_Tiered_Price extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->fields(array(
			'id' => new Field_Primary,
			'sku' => new Field_BelongsTo,
			'price_tier' => new Field_BelongsTo,
			'price' => new Field_Float(array(
				'places' => 4,
				'rules' => array(
					'not_empty' => NULL,
				),
			)),
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
	
	public function update($sku_id, $price_tier_id, $price)
	{
		$this->sku = $sku_id;
		$this->price_tier = $price_tier_id;
		$this->price = Currency::deduct_tax(str_replace(',', '', $price), Kohana::config('ecommerce.vat_rate'));
		return $this->save();
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
}