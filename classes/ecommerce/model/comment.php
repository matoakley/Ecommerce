<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Comment extends Model_Application
{	
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->sorting(array('created' => 'DESC'))
			->fields(array(
				'id' => new Field_Primary,
				'object' => new Field_String(array(
  				'rules' => array(
  					'not_empty' => NULL,
  				),
  			)),
  			'object_id' => new Field_Integer,
  			'user' => new Field_BelongsTo(array(
					'foreign' => 'user.id',
					'column' => 'user_id',
				)),
  			'comment' => new Field_Text,
  			'up_vote' => new Field_Boolean,
  			'down_vote' => new Field_Boolean,
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
	 'active',
	 'awaiting_moderation',
	 'disabled',
  );
	
	public static $objects = array(
		'review',
	);
	
	public static function check_module()
	{
  	if ( ! Caffeine::modules('comments'))
    {
      throw new Kohana_Exception('The Comments module is not enabled.');
    }
	}
	
	public static function _check_valid_object(Validate $array, $field)
	{	
		if ( ! in_array($array[$field], self::$objects))
		{
			$array->error($field, 'valid');
		}
	}
	
  public static function create($object = 'review', $data, $user)
	{
	  
  	if ( ! in_array($object, self::$objects))
	  {
  	  throw new Kohana_Exception('Invalid object.');
	  }

	
  	$comment = Jelly::factory('comment');
  	
  	$comment->object = $object;
  	$comment->object_id = $data['review_id'];
  	$comment->user = $user;
  	$comment->up_vote = isset($data['up_vote']) ? $data['up_vote'] : NULL;
  	$comment->down_vote = isset($data['down_vote']) ? $data['down_vote'] : NULL;
  	$comment->comment = isset($data['comment']) ? $data['comment'] : NULL;
  	
  	  
  	$review = Model_Review::load($comment->object_id);
  	
  	if ($comment->up_vote != FALSE)
  	  {
    	  $review->popularity += 1;
    	  $review->save();
  	  }
    if ($comment->down_vote != FALSE)
  	  {
    	  $review->popularity -= 1;
    	  $review->save();
  	  }
  	
  	$comment->save()->update_status(Caffeine::config('moderate_reviews') ? 'awaiting_moderation' : 'active');

  	return $comment;
	}
	
	public static function comments_to_moderate($limit = 5)
	{
  	return Jelly::select('comment')->where('status', '=', 'awaiting_moderation')->order_by('created', 'ASC')->limit($limit)->execute();
	}
	
	// Return the instace of the object which is reviewed
	public function item()
	{
  	$review = Jelly::select($this->object)->where('id', '=', $this->object_id)->load();
  	$product = Jelly::select($review->object)->where('id', '=', $review->object_id)->load();
  	
  	return $product;
	}
	
	public function item_admin_link()
	{
  	$item = $this->item();
  	
  	switch ($this->object)
  	{
    	case 'product':
    	  return '/admin/products/edit/'.$item->slug;
        break;
        
      default:
        return '#';
        break;
  	}
	}
	
	public function update($data)
	{
	  $this->comment = $data['comment'];
	
  	return $this->update_status($data['status'])->save();
	}
	
	public static function like_dislike($comment, $up, $down)
	{
  	$comment->up_vote = $up;
  	$comment->down_vote = $down;
  	
  	return $comment->save();
	}
}