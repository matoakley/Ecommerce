<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Page extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('pages')
			->fields(array(
				'id' => new Field_Primary,
				'name' => new Field_String,
				'slug' => new Field_String,
				'body' => new Field_Text,
				'meta_description' => new Field_String,
				'meta_keywords' => new Field_String,
				'template' => new Field_String,
				'status' => new Field_String,
				'parent' => new Field_BelongsTo(array(
					'foreign' => 'page.id',
					'column' => 'parent_id',
				)),
				'featured_image' => new Field_String(array(
          'in_db' => FALSE,
        )),
				'pages' => new Field_HasMany(array(
					'foreign' => 'page.parent_id',
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
		'disabled',
		'active',
	);

	public static $searchable_fields = array(
		'filtered' => array(
			'status' => array(
				'field' => 'status',
			),
		),
		'search' => array(
			'name',
			'body',
		),
	);
	
	public function __get($field)
  {
    if ($field == 'featured_image')
    {
      return $this->get_featured_image();
    }

    return parent::__get($field);
  }

	public static function build_page_tree($root_page = NULL, $active_only = FALSE)
	{				
		$tree = array();
		
		$tree = Jelly::select('page')->where('parent_id', '=', $root_page);
		
		if ($active_only)
		{
			$tree->where('status', '=', 'active');
		}
						
		$tree = $tree->order_by('order')->execute()->as_array();
		
		foreach ($tree as $key => $values)
		{
			$tree[$key]['children'] = self::build_page_tree($values['id'], $active_only);
		}
		
		return $tree;
	}

	public static function get_admin_pages($page = 1, $limit = 20)
	{
		$pages = Jelly::select('page')
						->limit($limit)
						->offset(($page - 1) * $limit)
						->order_by('name')
						->execute();
		
		return $pages;
	}
	
	public static function get_by_slug($slug, $include_disabled = FALSE)
	{
		$page = Jelly::select('page')->where('slug', '=', $slug);
		
		if ( ! $include_disabled)
		{
			$page->where('status', '=', 'active');
		}
		
		return $page->limit(1)->execute();
	}
	
	public function has_children()
	{
		return (bool) count($this->pages);
	}
	
	public function update($data)
	{	
		if (array_key_exists('parent', $data))
		{
			$data['parent'] = ($data['parent'] > 0) ? $data['parent'] : NULL;
		}
		
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
		$file_path = '/images/pages/' . $this->id . '.jpg';
		
		if ( ! file_exists(DOCROOT . $file_path))
		{
			$file_path = '/images/pages/default.jpg';
		}
		
		return $file_path;
	}

	public function upload_image($tmp_file)
	{
		// Let's get to work on resizing this image
		$image = Image::factory($tmp_file);
		
		// Full Size first
		$image_size = Kohana::config('ecommerce.page_image_sizing');
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
		
		$directory = DOCROOT . 'images/pages';
		if ( ! is_dir($directory))
		{
			mkdir($directory);
		}
		
		$image->save($directory . DIRECTORY_SEPARATOR . $this->id . '.jpg');
	}

}