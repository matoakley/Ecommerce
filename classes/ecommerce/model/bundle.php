<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Bundle extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('bundles')
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
				'status' => new Field_String,
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
	
	public static $searchable_fields = array(
		'filtered' => array(
			'status' => array(
				'field' => 'status',
			),
		),
		'search' => array(
			'name',
			'description',
		),
	);


	public static $statuses = array(
		'active', 'disabled'
	);

	public static function list_all()
	{
		return Jelly::select('product')
		        ->where('type', '=', 'bundle')
						->order_by('name');
	}
	
	public function count_products()
	{
		return count($this->products);
	}
	
	/**
	 * Make a class#member API link using an array of matches from [Kodoc::$regex_class_member]
	 *
	 * @param   int   $category
	 * @return  Database_Result
	 */
	public static function find_by_category($category)
	{
		return DB::select('brands.*')
									->from('brands')
									->distinct(TRUE)
									->join('products')->on('products.brand_id', '=', 'brands.id')
									->join('categories_products')->on('categories_products.product_id', '=', 'products.id')
									->where('categories_products.category_id', '=' , $category)							
									->order_by('brands.name')
									->execute();
	}

	/**
	* Passing FALSE, FALSE will return all brands.
	*
	**/
	public static function get_admin_bundles($page = 1, $limit = 20)
	{
		$brands = Jelly::select('product')
		        ->where('type', '=', 'bundle')
						->order_by('name');
						
		if ($page AND $limit)
		{
			$brands->limit($limit)->offset(($page - 1) * $limit);
		}		
		
		return $brands->execute();
	}
	
	public static function get_thumbnail_path($id)
	{
		$path = '/images/brands/' . $id . '.jpg';
		
		if ( ! file_exists(DOCROOT . $path))
		{
			$path = '/images/brands/default_thumb.jpg';
		}
		
		return $path;
	}
	
	public function __get($name)
	{
		if ($name == 'thumbnail')
		{
			return $this->get_thumbnail_path($this->id);
		}
		
		return parent::__get($name);
	}
	
	public function update($data)
	{		
		$this->set($data);
		$this->save();
		
		// Ping sitemap to search engines to alert them of content change
		if (IN_PRODUCTION AND $this->status == 'active')
		{
			Sitemap::ping(URL::site(Route::get('sitemap_index')->uri()), TRUE);
		}
		
		return $this;
	}

}