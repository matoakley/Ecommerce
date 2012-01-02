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
	
	public static $searchable_fields = array(
		'filtered' => array(
		),
		'search' => array(
		),
	);
	
	public static function get_posts_by_author($user_id, $limit = 5)
	{
		return Jelly::select('blog_post')->where('user_id', '=', $user_id)->order_by('created', 'DESC')->limit($limit)->execute();
	}

	public function body_summary()
	{
		return Text::limit_words($this->body, 100, ' &hellip;');
	}
	
	public function update($data)
	{
		$this->name = $data['name'];
		
		if (isset($data['slug']))
		{
			$this->slug = $data['slug'];
		}

		$this->body = $data['body'];
		$this->status = $data['status'];
		
		if ($this->status == 'active' AND $this->published_on == NULL)
		{
			$this->publised_on = time();
		}
		
		$this->meta_description = $data['meta_description'];
		$this->meta_keywords = $data['meta_keywords'];
		
		if ( ! $this->author->loaded())
		{
			$this->author = Auth::instance()->get_user()->id;
		}
		
		return $this->save();
	}
}