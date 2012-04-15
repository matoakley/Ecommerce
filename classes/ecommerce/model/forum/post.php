<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Forum_Post extends Model_Application
{	
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->fields(array(
				'id' => new Field_Primary,
				'category' => new Field_BelongsTo(array(
					'column' => 'category_id',
					'foreign' => 'forum_category.id',
				)),
				'author' => new Field_BelongsTo(array(
					'column' => 'user_id',
					'foreign' => 'user.id',
				)),
				'name' => new Field_String(array(
					'rules' => array(
						'not_empty' => NULL,
						'max_length' => array(Kohana::config('ecommerce.forum_post_name_max_length')),
					),
				)),
				'slug' => new Field_String,
				'text' => new Field_Text,
				'status' => new Field_String,
				'replies' => new Field_HasMany(array(
					'foreign' => 'forum_post.in_response_to',
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
	
	public function build_thread($number_of_posts, $offset)
	{	
		return Jelly::select('forum_post')->where('id', '=', $this->id)->or_where('in_response_to', '=', $this->id)->limit($number_of_posts)->offset($offset)->execute();
	}
	
	public function calculate_thread_length()
	{
		//TODO: Only count active posts
		
		return count($this->replies) + 1;
	}
}