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

	public static function list_all()
	{
		return Jelly::select('brand')->order_by('name')->execute();
	}
	
	public function count_products()
	{
		return count($this->products);
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
		
		return $this->save();
	}

}