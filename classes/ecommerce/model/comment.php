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
  				'callbacks' => array(
  					'valid' => array('Model_Custom_Field', '_check_valid_object')
  				),
  			)),
  			'object_id' => new Field_Integer,
  			'user' => new Field_BelongsTo,
  			'comment' => new Field_Text,
  			'up_vote' => new Field_Boolean,
  			'down_vote' => new Field_Boolean,
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

	public static function create($data, $user)
	{
  	$comment = Jelly::factory('comment');
  	
/*
  	$review->product = $data['product_id'];
  	$review->user = $user;
  	$review->rating = isset($data['rating']) ? $data['rating'] : NULL;
  	$review->review = isset($data['review']) ? $data['review'] : NULL;
*/
  	
  	return $comment->save();
	}
}