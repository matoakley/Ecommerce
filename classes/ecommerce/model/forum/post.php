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
						'max_length' => array(Kohana::config('ecommerce.forum_post_name_max_length')),
					),
				)),
				'slug' => new Field_String,
				'text' => new Field_Text(array(
					'rules' => array(
						'not_empty' => NULL,
					),
				)),
				'status' => new Field_String,
				'last_post' => new Field_Timestamp(array(
					'format' => 'Y-m-d H:i:s',
				)),
				'replies' => new Field_HasMany(array(
					'foreign' => 'forum_post.in_response_to',
				)),
				'in_response_to' => new Field_BelongsTo(array(
          'foreign' => 'forum_post.id',
          'column' => 'in_response_to',
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
	
	public static function create_new_post($data, $category, $user)
	{
		$post = Jelly::factory('forum_post');
		
		$post->category = $category;
		$post->author = $user;
		
		// Filter the text for naughty words
		$post->name = Model_Forum_Banned_Word::censor($data['name']);
		$post->text = Model_Forum_Banned_Word::censor($data['text']);
		
		// Generate a slug for the post
		$post->slug = Text::slugify($post->name.'-'.Text::random());
		
		$post->status = 'active';
		
		$post->last_post = time();
		
		return $post->save();
	}
	
	public static function hottest_posts($hours = 24, $limit = NULL, $offset = NULL)
	{
		$timestamp = date('Y-m-d H:i:s', strtotime('- '.$hours.' hours'));
	
		$posts = Jelly::select('forum_post')
								->join('forum_post_views')
								->on('forum_post_views.forum_post_id', '=', 'forum_posts.id')
								->where('in_response_to', 'IS', NULL)
								->where('forum_post_views.created', '>', $timestamp)
								->group_by('forum_post_views.forum_post_id')
								->order_by(DB::expr('COUNT(forum_post_views.id)'), 'DESC');
		
		if ($limit)
		{
			$posts->limit($limit);
		}

		if ($offset)
		{
			$posts->limit($offset);
		}
				
		return $posts->execute();
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
	
	public function add_reply($data, $user)
	{
		$reply_post = Jelly::factory('forum_post');
		
		$reply_post->author = $user;
		
		// Filter the text for naughty words
		$reply_post->text = Model_Forum_Banned_Word::censor($data['text']);
		
		$reply_post->in_response_to = $this;
		$reply_post->status = 'active';
		
		$reply_post->save();
		
		$this->last_post = time();
		
		return $this->save();
	}
}