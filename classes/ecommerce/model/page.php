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
	
	public static $statuses = array(
		'disabled',
		'active',
	);

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
	
	public function update($data)
	{	
		$this->set($data);
		
		return $this->save();
	}
}