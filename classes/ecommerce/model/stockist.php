<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Stockist extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->sorting(array('name' => 'ASC'))
			->fields(array(
				'id' => new Field_Primary,
				'name' => new Field_String(array(
					'rules' => array(
						'not_empty' => NULL,		
					),
				)),
				'slug' => new Field_String(array(
					'unique' => TRUE,
					'callbacks' => array(
						'slug_valid' => array('Model_Stockist', '_is_slug_valid'),
					),
				)),
				'description' => new Field_Text,
				'website' => new Field_String,
				'address' => new Field_BelongsTo,
				'status' => new Field_String,
				'meta_description' => new Field_String,
				'meta_keywords' => new Field_String,
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
			'status' => array(
				'field' => 'status',
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
				$is_duplicate = (bool) Jelly::select('stockist')->where('slug', '=', $array['slug'])->where('deleted', 'IS', NULL)->count();
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
	
	/****** Public Methods ******/
	
	public function update($data)
	{	
		$this->name = $data['name'];
		if (isset($data['slug']))
		{
			$this->slug = $data['slug'];
		}
		$this->address = $data['address'];
		$this->status = $data['status'];
		$this->description = $data['description'];
		$this->website = $data['website'];
		$this->meta_description = $data['meta_description'];
		$this->meta_keywords = $data['meta_keywords'];
		$this->save();
		
		// Ping sitemap to search engines to alert them of content change
		if (IN_PRODUCTION AND $this->status == 'active')
		{
			Sitemap::ping(URL::site(Route::get('sitemap_index')->uri()), TRUE);
		}
		
		return $this;
	}
	
	public function tidied_website()
	{
		if (empty($this->website))
		{
			return FALSE;
		}
	
		$url = $this->website;
		
		if (substr($url, 0, 7) != 'http://' AND substr($url, 0, 8) != 'https://')
		{
			$url = 'http://'.$url;
		}
	
		return $url;
	}
}