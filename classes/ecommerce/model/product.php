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
					'on_copy' => 'copy',
				)),
				'slug' => new Field_String(array(
					'on_copy' => 'clear',
					'callbacks' => array(
						'slug_valid' => array('Model_Product', '_is_slug_valid'),
					),
				)),
				'description' => new Field_Text(array(
					'on_copy' => 'copy',
				)),
				'price' => new Field_Float(array(  // Legacy Field, should not be used after v1.1.3
					'places' => 4,
				)),
				'sku' => new Field_String,  // Legacy Field, should not be used after v1.1.3
				'categories' => new Field_ManyToMany(array(
					'foreign' => 'category',
					'through' => 'categories_products',
					'on_copy' => 'copy',
				)),
				'bundle_items' => new Field_ManyToMany(array(
					'foreign' => 'sku',
					'through' => 'bundles_skus',
					'on_copy' => 'copy',
				)),
				'brand' => new Field_BelongsTo(array(
					'foreign' => 'brand.id',
					'on_copy' => 'copy',
				)),
				'status' => new Field_String(array(
					'on_copy' => 'copy',
				)),
				'type' => new Field_String,
				'meta_description' => new Field_String(array(
					'on_copy' => 'copy',
				)),
				'meta_keywords' => new Field_String(array(
					'on_copy' => 'copy',
				)),
				'images' => new Field_HasMany(array(
					'foreign' => 'product_image.product_id',
					'on_copy' => 'clear',
				)),
				'default_image' => new Field_BelongsTo(array(
					'foreign' => 'product_image.id',
					'column' => 'default_image_id',
					'on_copy' => 'copy',
				)),
				'thumbnail' => new Field_BelongsTo(array(
					'foreign' => 'product_image.id',
					'column' => 'thumbnail_id',
					'on_copy' => 'copy',
				)),
				'bundle_items' => new Field_ManyToMany(array(
					'foreign' => 'sku',
					'through' => 'bundles_skus',
					'on_copy' => 'copy',
				)),
				'skus' => new Field_HasMany(array(
					'foreign' => 'sku.product_id',
				)),
				'related_products' => new Field_ManyToMany(array(
					'foreign' => 'product',
					'through' => array(
                    'model'   => 'related_products_products',
                    'columns' => array('related_product_id', 'product_id'),
                ),
				)),
				'product_options' => new Field_HasMany(array(
					'on_copy' => 'clone',
				)),
				'vat_code' => new Field_BelongsTo,
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
				'stock' => new Field_Integer,  // Legacy Field, should not be used after v1.1.3
				'duplicating' => new Field_Boolean(array(
					'in_db' => FALSE,
					'default' => FALSE,
				)),
			));
	}

	public static $statuses = array(
		'active', 'disabled',
	);
	
	public static $inputs = array(
		'application/pdf', '.doc', '.xls', '.csv', 'image/*',
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
			'vat_code' => array(
				'field' => 'vat_code',
			),
			'status' => array(
				'field' => 'status',
			),
			'type' => array(
				'field' => 'type',
			),
		),
		'search' => array(
			'name',
			'description',
		),
	);

		/****** Validation Callbacks ******/
	
	public static function _is_slug_valid(Validate $array, $field)
	{
		$valid = TRUE;
		
		// Is slug set (unless duplicating...)
		if ( ! isset($array['duplicating']))
		{	
			if ( ! isset($array['slug']) OR $array['slug'] == '')
			{
				$valid = FALSE;
			}
			else
			{ 
				// Is slug a duplicate?
				$is_duplicate = (bool) Jelly::select('product')
                        				->where('slug', '=', $array['slug'])
                        				->where('deleted', 'IS', NULL)->count();
				
				if ($is_duplicate)
				{
					$valid = FALSE;
				}
			}
		}
		
		if ( ! $valid)
		{
			$array->error('slug', 'Slug is a required field.');
		}
	}
	
	

	public static function list_all()
	{
	 
		return Jelly::select('product')
							->where('products.status', '=', 'active')
							->where('products.deleted', 'IS', NULL)
							->order_by('name', 'ASC')
							->execute();
	}


	
	/****** Public Functions ******/
	
	public static function most_popular_products($num_products = 5)
	{
		$sql = "SELECT skus.id AS sku_id, products.id AS product_id, sales_order_items.product_name, SUM(sales_order_items.quantity) AS sold
						FROM products
						JOIN skus ON products.id = skus.product_id
						JOIN sales_order_items ON (skus.id = sales_order_items.sku_id OR products.id = sales_order_items.product_id)
						JOIN sales_orders ON sales_order_items.sales_order_id = sales_orders.id
						WHERE sales_orders.status = 'complete'
						AND products.deleted IS NULL
						AND sales_orders.deleted IS NULL
						AND sales_order_items.deleted IS NULL
						GROUP BY sales_order_items.product_name
						ORDER BY SUM(sales_order_items.quantity) DESC
						LIMIT $num_products";
						
		return Database::instance()->query(Database::SELECT, $sql, FALSE);
	}
	
		public static function top_selling_products($items = 5)
	{
  	$sql = "SELECT products.*
						FROM products
						JOIN skus ON products.id = skus.product_id
						JOIN sales_order_items ON (skus.id = sales_order_items.sku_id OR products.id = sales_order_items.product_id)
						JOIN sales_orders ON sales_order_items.sales_order_id = sales_orders.id
						WHERE sales_orders.status = 'complete'
						AND products.deleted IS NULL
						AND sales_orders.deleted IS NULL
						AND sales_order_items.deleted IS NULL
						GROUP BY sales_order_items.product_name
						ORDER BY SUM(sales_order_items.quantity) DESC
						LIMIT $items";
						
		//return Database::instance()->query(Database::SELECT, $sql, FALSE);
	
	return Database::instance()->query(Database::SELECT, $sql, 'Model_Product');
	                           
	}

	public static function newest_products($num_products = 5)
	{
		return Jelly::select('product')->where('status', '=', 'active')->order_by('created', 'DESC')->limit($num_products)->execute();
	}

	public function summarise_sku_price($is_admin = FALSE)
	{
		$summary = '';
		
		// If this is a call from the admin then we want all SKUs, else just the ones that are live to the public and non-commercial
		$skus = $is_admin ? $this->get('skus')->execute() : $this->get('skus')->where('commercial_only', '=', 0)->where('status', '=', 'active')->execute();
		
		if (count($skus) > 1)
		{
			$multiple_prices = FALSE;
			$min_price = $skus->current()->price;
			
			foreach ($skus as $sku)
			{
				if ($sku->price < $min_price)
				{
					$multiple_prices = TRUE;
					$min_price = $sku->price;
				}
				elseif ($sku->price > $min_price)
				{
					$multiple_prices = TRUE;
				}
				
				$summary = ($multiple_prices) ? 'From ' : '';
				$summary .= '&pound;'.number_format(Currency::add_tax($min_price, $sku->vat_rate()), 2);
			}
		}
		else
		{
			// Only one SKU so set its price!
			$summary = '&pound;'.number_format($skus->current()->retail_price(), 2);
		}
		
		return $summary;
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
	
	/**
	 * Handles processing of data before saving when a product is edited or created.
	 *
	 * @param   array  $data
	 * @return  $this
	 */
	public function update($data)
	{	
		if (isset($data['stock']))
		{
			$this->stock = $data['stock'];
		}	
		$this->name = $data['name'];
		$this->slug = (isset($data['slug'])) ? $data['slug'] : $this->slug;
		$this->description = $data['description'];
		$this->status = $data['status'];
		$this->meta_keywords = isset($data['meta_keywords']) ? $data['meta_keywords'] : '';
		$this->meta_description = isset($data['meta_description']) ? $data['meta_description'] : '';
		$this->default_image = isset($data['default_image']) ? $data['default_image'] : NULL;
		$this->thumbnail = isset($data['thumbnail']) ? $data['thumbnail'] : NULL;
		$this->brand = isset($data['brand']) ? $data['brand'] : NULL;
		$this->type = isset($data['type']) ? $data['type'] : 'product';
		
		// Clear down and save categories.
		$this->remove('categories', $this->categories);
		
		if (isset($data['categories']))
		{
			$this->add('categories', $data['categories']);
		}
		
		if (Kohana::config('ecommerce.modules.vat_codes'))
		{
			$this->vat_code = $data['vat_code'];
		}
		
		// Ping sitemap to search engines to alert them of content change
		if (IN_PRODUCTION AND $this->status == 'active')
		{
			$sitemap_ping = Sitemap::ping(URL::site(Route::get('sitemap_index')->uri()), TRUE);
		}
		//echo Kohana::debug($this);exit;
		$this->save();
		
		// If there are no SKUs set for this product then it must
		// be a new product so create a default SKU.
		
		if (! count($this->skus))
		{  
			Model_Sku::create_default($this);
		}
		
		return $this;
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

	public function get_options()
	{
		$options = Jelly::select('product_option')
									->where('product_id', '=', $this->id)
									->execute()->as_array('id', 'key');

		return array_values(array_unique($options));
	}
	
	public function get_admin_option_values($option_name)
	{
		return Jelly::select('product_option')
							->where('product_id', '=', $this->id)
							->where('key', '=', $option_name)
							->order_by('list_order', 'ASC')
							->execute();
	}
	
	public function get_option_values($option_name)
	{
		$options =  Jelly::select('product_option')
		          ->distinct(true)
							->join('product_options_skus')
							->on('product_option_id', '=', 'product_option.id')
							->join('skus')
							->on('sku_id', '=', 'skus.id')
							->where('product_options.product_id', '=', $this->id)
							->where('key', '=', $option_name)
							->where('sku.status', '=', 'active')
							->where('sku.deleted', '=', NULL)
							->where('product_option.deleted', '=', NULL)
							->order_by('list_order', 'ASC')
							->execute();
		
		return $options;
	}
	
	public function get_product_reviews($items, $offset = NULL, $order = 'created', $direction = 'ASC')
	{
		return Jelly::select('review')
							->where('object_id', '=', $this->id)
							->where('status', '=', 'active')
							->order_by($order, $direction)
							->limit($items)
							->offset($offset)
							->execute();
	}
	
	public function active_skus()
	{
		if (IS_TRADE)
		{
  		return $this->get('skus')
								->where('status', '=', 'active')
								->where('show_in_commercial', '=', 1)
								->execute();
		}
		else 
		{
  		return $this->get('skus')
								->where('status', '=', 'active')
								->where('show_in_retail', '=', 1)
								->execute();
		}
	}
	
	// Alias of active_skus()
	public function retail_skus()
	{
		return $this->active_skus();
	}
	
	public function total_stock()
	{
		$stock = 0;
		
		foreach ($this->skus as $sku)
		{
			$stock += $sku->stock;
		}
		
		return $stock;
	}
	
	public function remove_options($key)
	{
		$options = $this->get('product_options')->where('key', '=', $key)->execute();
		foreach ($options as $option)
		{
			$option->delete();
		}
	}
	
	//function to get all bundles not products
		public function get_admin_bundles()
	{
  	$bundles = Jelly::select('product')
		        ->where('type', '=', 'bundle')
						->order_by('name')
						->execute()->as_array();
						
		return $bundles;
	}
	
	//function to get all products excluding bundles.
	public function get_all_products()
	{
  	$products = Jelly::select('product')
  	        ->where('type', '=', 'product')
						->order_by('name')
						->execute()->as_array();
						
		return $products;
	}
	
	public static function add_to_bundle($product_id, $sku_id)
	{	
		$bundle = Model_Product::load($product_id);
		$bundle->add('bundle_items', $sku_id);
		$bundle->save();
	}
	
		public static function remove_from_bundle($product_id, $sku_id)
	{	
		$bundle = Model_Product::load($product_id);
		$bundle->remove('bundle_items', $sku_id);
		$bundle->save();
	}
	
	// Override standard delete to handle orphaned related product
	public function delete($key = FALSE)
	{			
		//go through and delete all skus
		foreach ($this->skus as $sku)
		{
  		$sku->delete();
		}
		
		//go through and delete all product options
		foreach ($this->product_options as $option)
		{
  		$option->delete();
		}
		
		//go through and delete all images
		foreach ($this->images as $image)
		{
  		$image->delete();
		}
		
		parent::delete($key);
	}
	
	public function add_to_related_products($data)
	{
  	$this->add('related_products', array($data));
  	$this->save();
	}
	
	public function remove_from_related_products($data)
	{
  	$this->remove('related_products', array($data));
  	$this->save();
	}
}