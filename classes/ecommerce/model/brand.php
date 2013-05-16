<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Brand extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('brands')
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
				'website' => new Field_String,
				'status' => new Field_String,
				'meta_description' => new Field_String,
				'meta_keywords' => new Field_String,
				'thumbnail' => new Field_String(array(
					'in_db' => FALSE,
				)),
				'featured_image' => new Field_String(array(
          'in_db' => FALSE,
        )),
				'products' => new Field_HasMany(array(
					'foreign' => 'product.brand_id',
				)),
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
	
	public function __get($field)
  {
    if ($field == 'featured_image')
    {
      return $this->get_featured_image();
    }
    elseif ($field == 'thumbnail')
		{
			return $this->get_thumbnail_path($this->id);
		}

    return parent::__get($field);
  }
	
	public static function list_all()
	{
		return Jelly::select('brand')->order_by('name')->execute();
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
	public static function get_admin_brands($page = 1, $limit = 20)
	{
		$brands = Jelly::select('brand')
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
	
	public function get_featured_image()
	{
		$file_path = '/images/brands/' . $this->id . '.jpg';
		
		if ( ! file_exists(DOCROOT . $file_path))
		{
			$file_path = '/images/brands/default.jpg';
		}
		
		return $file_path;
	}

	public function upload_image($tmp_file)
	{
		// Let's get to work on resizing this image
		$image = Image::factory($tmp_file);
		
		// Full Size first
		$image_size = Kohana::config('ecommerce.blog_image_sizing');
		if ($image_size['width'] > 0 AND $image_size['height'] > 0)
		{
			$image->resize($image_size['width'], $image_size['height'], Image::INVERSE);
			// Crop it for good measure
			$image->crop($image_size['width'], $image_size['height']);
		}
		elseif ($image_size['width'] == 0)
		{
			$image->resize(NULL, $image_size['height']);
		}
		else
		{
			$image->resize($image_size['width'], NULL);
		}
		
		$directory = DOCROOT . 'images/brands';
		if ( ! is_dir($directory))
		{
			mkdir($directory);
		}
		
		$image->save($directory . DIRECTORY_SEPARATOR . $this->id . '.jpg');
	}
}