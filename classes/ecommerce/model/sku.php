<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Customers purchase SKUs within the system. A Product may have 1 - n SKUs.
 *
 * @package    Ecommerce
 * @author     Matt Oakley
 */
class Ecommerce_Model_Sku extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('skus')
		      ->sorting(array('price' => 'ASC'))
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
				'stock_status' => new Field_String,
				'thumbnail' => new Field_BelongsTo(array(
					'foreign' => 'product_image.id',
					'column' => 'thumbnail_id',
					'on_copy' => 'copy',
				)),
				'status' => new Field_String,
				'commercial_only' => new Field_Boolean,
				'tiered_prices' => new Field_HasMany(array(
					'foreign' => 'sku_tiered_price.sku_id',
				)),
				'weight' => new Field_Float(array(
					'places' => 4,
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
	
	public static function list_all()
	{
		return Jelly::select('sku')
							->join('products')->on('products.id', '=', 'skus.product_id')
							->where('products.status', '=', 'active')
							->where('skus.status', '=', 'active')
							->order_by('products.name', 'ASC')
							->execute();
	}
	
	/**
	 * Calculates the VAT rate for the product, taking into account whether VAT codes
	 * module has been enabled
	 *
	 * @author  Matt Oakley
	 * @return  float
	 */
	public function vat_rate()
	{
		// If we are using custom VAT codes module then calculate retail cost based upon this...else use default value from config.
		return Caffeine::modules('vat_codes') ? $this->product->vat_code->value : Kohana::config('ecommerce.vat_rate');
	}
	
	/**
	 * Returns the Retail Price of a product after adding VAT.
	 *
	 * @author  Matt Oakley
	 * @return  float
	 */
	public function retail_price($ignore_tiered_pricing = FALSE)
	{
		if (Caffeine::modules('tiered_pricing') AND ! $ignore_tiered_pricing AND Auth::instance()->logged_in('trade_area'))
		{
			return $this->price_for_tier(Auth::instance()->get_user()->customer->price_tier);
		}
  		return $this->price = Currency::add_tax($this->price, $this->vat_rate());
	}
	
	public function update($data)
	{ 
	  if (isset($data['VAT']))
	  {
  		$this->price = Currency::deduct_tax(str_replace(',', '', $data['price']), $this->vat_rate());
  	}
	  else 
	  {
  		$this->price = $data['price'];
		}  
		 
		$this->sku = $data['sku'];
		if (isset($data['status']))
		{
			$this->status = $data['status'];
		}
		
		if (Caffeine::modules('commercial_sales_orders'))
		{
			$this->commercial_only = isset($data['commercial_only']) ? $data['commercial_only'] : FALSE;
		}
		
		if (Caffeine::modules('stock_control') AND isset($data['stock']))
		{
			$this->stock = $data['stock'];
		}
		if (isset($data['stock_status']))
		{
  		$this->stock_status = $data['stock_status'];
		}
		if (isset($data['thumbnail']['id']))
		{
  		$this->thumbnail = $data['thumbnail']['id'];
		}
		
		if (Caffeine::modules('product_weights'))
		{
			$this->weight = $data['weight'];
		}
		
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
	
	/**
	 * Fetch the price that the for this SKU and Price Tier combination.
	 *
	 * @author  Matt Oakley
	 * @param   Model_Price_Tier   	Tier to fetch price for
	 * @return  float								price
	 */
	public function price_for_tier($tier)
	{
		$tiered_price = $this->get('tiered_prices')->where('price_tier_id', '=', $tier->id)->load();
		if ($tiered_price->loaded() AND $tiered_price->price > 0)
		{
			return $tiered_price->retail_price();
		}
		else
		{
			return $this->retail_price(TRUE);
		}
	}
	
	/**
	 * Fetch the net price that the for this SKU and Price Tier combination.
	 *
	 * @author  Matt Oakley
	 * @param   Model_Price_Tier   	Tier to fetch price for
	 * @return  float								price
	 */
	public function net_price_for_tier($tier)
	{
		$tiered_price = $this->get('tiered_prices')->where('price_tier_id', '=', $tier->id)->load();
		if ($tiered_price->loaded() AND $tiered_price->price > 0)
		{
			return $tiered_price->price;
		}
		else
		{
			return $this->price;
		}
	}
}