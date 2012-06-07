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
				'commercial_only' => new Field_Boolean,
				'tiered_prices' => new Field_HasMany(array(
					'foreign' => 'sku_tiered_price.sku_id',
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
	
	public static $statuses = array(
		'active', 'disabled',
	);
	
	public static function create_default($product)
	{
		$sku = Jelly::factory('sku');
		$sku->product = $product;
		$sku->price = 0;
		$sku->stock = 0;
		$sku->status = 'disabled';
		$sku->commercial_only = FALSE;
		
		return $sku->save();
	}
	
	public static function create_with_options($product, $options)
	{
		$sku_exists = FALSE;
		$sku = NULL;
		
		// Firstly, check that a SKU with these options does not already exist
		foreach($product->skus as $existing_sku)
		{
			if (count($options) == count($existing_sku->product_options))
			{	
				$sku_exists = (count(array_intersect($existing_sku->product_options->as_array('id','id'), $options)) == count($options));
			}
		}
	
		if ( ! $sku_exists)
		{
			$sku = Jelly::factory('sku');
			$sku->product = $product;
			$sku->price = 0;
			$sku->stock = 0;
			$sku->status = 'disabled';
			$sku->commercial_only = FALSE;
			$sku->add('product_options', $options);
			$sku->save();
		}
		
		return $sku;
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
		$this->price = Currency::deduct_tax(str_replace(',', '', $data['price']), Kohana::config('ecommerce.vat_rate'));
		if (isset($data['stock']))
		{
			$this->stock = $data['stock'];
		}
		$this->sku = $data['sku'];
		if (isset($data['status']))
		{
			$this->status = $data['status'];
		}
		$this->commercial_only = isset($data['commercial_only']) ? $data['commercial_only'] : FALSE;
		
		// Update SKUs tiered prices
		if (Kohana::config('ecommerce.modules.tiered_pricing') AND isset($data['tiered_prices']))
		{
			foreach ($data['tiered_prices'] as $price_tier_id => $price)
			{
				Jelly::select('sku_tiered_price')->where('sku_id', '=', $this->id)->where('price_tier_id', '=', $price_tier_id)->load()->update($this->id, $price_tier_id, $price);
			}
		}
		
		return $this->save();
	}

	public function remove_from_stock($quantity = 1)
	{
		$this->stock = ($this->stock - $quantity >= 0) ? $this->stock - $quantity : 0;
		$this->save();
	}
	
	public function name()
	{
		$name = $this->product->name;
		
		foreach ($this->product_options as $option)
		{
			$name .= ' '.$option->value;
		}
	
		return $name;
	}
}