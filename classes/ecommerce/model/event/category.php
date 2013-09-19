<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Event_Category extends Model_Application
{	
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('event_categories')
		  ->sorting(array('order' => 'ASC', 'name' => 'ASC'))
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
				'categories' => new Field_HasMany(array(
					'foreign' => 'event_category.parent_id',
				)),
				'parent' => new Field_BelongsTo(array(
					'foreign' => 'event_category.id',
					'column' => 'parent_id',
				)),
				'order' => new Field_Integer,
				'status' => new Field_String,
				'meta_description' => new Field_String,
				'meta_keywords' => new Field_String,
				'events' => new Field_ManyToMany(array(
					'foreign' => 'event',
					'through' => 'categories_events',
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
	
	public static function build_category_tree($root_category = NULL, $active_only = FALSE)
	{				
		$tree = array();
		
		$tree = Jelly::select('event_category')->where('parent_id', '=', $root_category);
		
		if ($active_only)
		{
			$tree->where('status', '=', 'active');
		}
						
		$tree = $tree->order_by('order')->execute()->as_array();
		
		foreach ($tree as $key => $values)
		{
			$tree[$key]['children'] = Model_Event_Category::build_category_tree($values['id'], $active_only);
			$tree[$key]['num_products'] = count(Jelly::select('event_category', $values['id'])->events);
			$tree[$key]['thumbnail'] = Model_Event_Category::get_static_thumbnail_path($values['id']);
			$tree[$key]['events'] = Jelly::select('event_category', $values['id'])->events;
		}
		
		return $tree;
	}
	
	public function __get($name)
	{
		if ($name == 'thumbnail_path')
		{
			return $this->get_thumbnail_path();
		}
		
		return parent::__get($name);
	}
	
	public function has_children()
	{
		return (bool) count($this->categories);
	}
	
	public function display_active_events($order_by = 'name', $order_dir = 'ASC')
	{
		return $this->get('events')
					->where('status', '=', 'active')
					->order_by($order_by, $order_dir)
					->execute();
	}
	
	/**
	* Passing FALSE, FALSE will return all categories.
	*
	**/
	public static function get_admin_categories($page = 1, $limit = 20)
	{
		$categories = Jelly::select('event_category')
						->order_by('name');
						
		if ($page AND $limit)
		{
			$categories->limit($limit)->offset(($page - 1) * $limit);
		}		

		return $categories->execute();
	}

	public static function get_static_thumbnail_path($id)
	{
		$path = '/images/event_categories/' . $id . '.jpg';
		
		if ( ! file_exists(DOCROOT . $path))
		{
			$path = '/images/event_categories/default_thumb.jpg';
		}
		
		return $path;
	}
	
	public function get_thumbnail_path()
	{
		$path = '/images/event_categories/' . $this->id . '.jpg';
		
		if ( ! file_exists(DOCROOT . $path))
		{
			$path = '/images/event_categories/default_thumb.jpg';
		}
		
		return $path;
	}
	
	public function count_products()
	{
		return count($this->products);
	}
	
	public function update($data)
	{
		$this->name = $data['name'];
		if (isset($data['slug']))
		{
			$this->slug = $data['slug'];
		}
		$this->status = $data['status'];
		$this->description = $data['description'];
		$this->meta_description = $data['meta_description'];
		$this->meta_keywords = $data['meta_keywords'];
		$this->parent = $data['parent'] > 0 ? $data['parent'] : NULL;
		
		return $this->save();
	}
}
