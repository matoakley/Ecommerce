<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Forum_Category extends Model_Application
{	
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->fields(array(
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
					'foreign' => 'category.parent_id',
				)),
				'parent' => new Field_BelongsTo(array(
					'foreign' => 'category.id',
					'column' => 'parent_id',
				)),
				'order' => new Field_Integer,
				'status' => new Field_String,
				'meta_description' => new Field_String,
				'meta_keywords' => new Field_String,
				'posts' => new Field_HasMany(array(
					'foreign' => 'forum_post.category_id',
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
		
		$tree = Jelly::select('forum_category')->where('parent_id', '=', $root_category);
		
		if ($active_only)
		{
			$tree->where('status', '=', 'active');
		}
						
		$tree = $tree->order_by('order')->execute()->as_array();
		
		foreach ($tree as $key => $values)
		{
			$tree[$key]['children'] = self::build_category_tree($values['id'], $active_only);
		}
		
		return $tree;
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