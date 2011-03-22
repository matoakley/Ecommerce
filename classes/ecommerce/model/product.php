<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Product extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('products')
			->sorting(array('name' => 'ASC'))
			->fields(array(
				'id' => new Field_Primary,
				'name' => new Field_String(array(
					'rules' => array(
					'not_empty' => NULL,
				),
				)),
				'slug' => new Field_String(array(
					'unique' => TRUE,
					'rules' => array(
					'not_empty' => NULL,
				),
				)),
				'description' => new Field_Text,
				'price' => new Field_Float(array(
					'places' => 4,
				)),
				'sku' => new Field_String,
				'categories' => new Field_ManyToMany(array(
					'foreign' => 'category',
					'through' => 'categories_products',
				)),
				'brand' => new Field_BelongsTo(array(
					'foreign' => 'brand.id',
				)),
				'status' => new Field_String,
				'meta_description' => new Field_String,
				'meta_keywords' => new Field_String,
				'images' => new Field_HasMany(array(
					'foreign' => 'product_image.product_id',
				)),
				'default_image' => new Field_BelongsTo(array(
					'foreign' => 'product_image.id',
					'column' => 'default_image_id',
				)),
				'thumbnail' => new Field_BelongsTo(array(
					'foreign' => 'product_image.id',
					'column' => 'thumbnail_id',
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
		'active', 'disabled'
	);
	
	public static $searchable_fields = array(
		'filtered' => array(
			'category' => array(
				'join' => array(
					'categories_products' => array('product.id', 'categories_products.product_id'),
					'categories' => array('category.id', 'categories_products.category_id'),
				),
				'field' => 'category.id',
			),
			'brand' => array(
				'field' => 'brand',
			),
			'status' => array(
				'field' => 'status',
			),
		),
		'search' => array(
			'name',
			'description',
		),
	);

	public function display_meta_description()
	{
		// If a meta description has not been set then we'll build one from the description.
		// Not ideal, but it's better than nothing!
		if ( ! is_null($this->meta_description) AND $this->meta_description != '')
		{
			$meta_description = $this->meta_description;
		}
		else
		{
			$meta_description = Text::limit_chars(strip_tags($this->description), 160, ' &hellip;', TRUE);
		}
		
		return $meta_description;
	}

	/**
	 * Returns the Retail Price of a product after adding VAT.
	 *
	 * @return  float
	 */
	public function retail_price()
	{
		return number_format($this->price + ($this->price * (Kohana::config('ecommerce.vat_rate') / 100)), 2);
	}

	/**
	 * Deducts the VAT from the price provided. Use to convert Retail Price into Raw Price when saving.
	 *
	 * @param   mixed  $price
	 * @return  float
	 */
	public static function deduct_tax($price = 0)
	{
		return $price / ((Kohana::config('ecommerce.vat_rate') / 100) + 1);
	}
	
	public static function get_admin_products($page = 1, $limit = 20)
	{
		$products = Jelly::select('product')
						->limit($limit)
						->offset(($page - 1) * $limit)
						->order_by('name')
						->execute();
		
		return $products;
	}
	
	public static function update_price($id, $price)
	{
		return self::load($id)->set(array('price' => self::deduct_tax($price)))->save();
	}
	
	/**
	 * Handles processing of data before saving when a product is edited or created.
	 *
	 * @param   array  $data
	 * @return  $this
	 */
	public function update($data)
	{
		if (array_key_exists('brand', $data))
		{
			$data['brand'] = ($data['brand'] > 0) ? $data['brand'] : NULL;
		}
		
		if (array_key_exists('price', $data))
		{
			$data['price'] = self::deduct_tax($data['price']);
		}
		
		$this->name = $data['name'];
		$this->slug = (isset($data['slug'])) ? $data['slug'] : $this->slug;
		$this->description = $data['description'];
		$this->price = $data['price'];
		$this->sku = $data['sku'];
		$this->status = $data['status'];
		$this->meta_keywords = $data['meta_keywords'];
		$this->meta_description = $data['meta_description'];
		$this->default_image = $data['default_image'];
		$this->thumbnail = $data['thumbnail'];
		$this->brand = $data['brand'];
		
		// Clear down and save categories.
		$this->remove('categories', $this->categories);
		
		if (array_key_exists('categories', $data))
		{
			$this->add('categories', $data['categories']);
		}
		
		return $this->save();
	}

	public function set_default_image($image_id = FALSE)
	{
		// If no image is specified then pick the first	
		if ( ! $image_id AND (bool) $this->images->count())
		{
			$image_id = $this->images->current()->id;
		}
		else
		{
			$image_id = NULL;
		}
		
		$this->default_image = $image_id;
		return $this->save();
	}
	
	public function set_thumbnail($image_id = FALSE)
	{
		// If no image is specified then pick the first	
		if ( ! $image_id AND (bool) $this->images->count())
		{
			$image_id = $this->images->current()->id;
		}
		else
		{
			$image_id = NULL;
		}
		
		$this->thumbnail = $image_id;
		return $this->save();
	}	

}