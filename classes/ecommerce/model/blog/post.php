<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Blog_Post extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('blog_posts')
			->fields(array(
				'id' => new Field_Primary,
				'author' => new Field_BelongsTo(array(
					'column' => 'user_id',
					'foreign' => 'user.id',
				)),
				'name' => new Field_String,
				'slug' => new Field_String,
				'body' => new Field_Text,
				'published_on' => new Field_Timestamp(array(
					'format' => 'Y-m-d H:i:s',
				)),
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

	public function body_summary()
	{
		return Text::limit_words($this->body, 100, ' &hellip;');
	}
}