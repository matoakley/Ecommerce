<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Forum_Post_View extends Model_Application
{	
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->fields(array(
				'id' => new Field_Primary,
				'forum_post' => new Field_BelongsTo,
				'ip_address' => new Field_String,
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
	
	public static function record($forum_post, $ip_address = NULL)
	{
		// Check that this is not a duplicate view (within 1 hour to stop refresh spamming)
		$timestamp = date('Y-m-d H:i:s', strtotime('- 1 hour'));
		$view = Jelly::select('forum_post_view')->where('forum_post_id', '=', $forum_post->id)->where('ip_address', '=', $ip_address)->where('created', '>', $timestamp)->load();
	
		if ($view->loaded())
		{		
			return $view;
		}
		
		$view->forum_post = $forum_post;
		$view->ip_address = $ip_address;
		
		return $view->save();
	}
}