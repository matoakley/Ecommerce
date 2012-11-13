<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Review extends Model_Application
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
  			'popularity' => new Field_Integer,
  			'user' => new Field_BelongsTo,
  			'rating' => new Field_Integer,
  			'review' => new Field_Text,
  			'status' => new Field_Text,
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
	 'active',
	 'awaiting_moderation',
	 'disabled',
  );
	
	public static $objects = array(
		'product',
	);
	
	public static $searchable_fields = array(
		'filtered' => array(
			'status' => array(
				'field' => 'status',
			),
		),
		'search' => array(
			'review',
		),
	);

	
	public static function check_module()
	{
  	if ( ! Caffeine::modules('reviews'))
    {
      throw new Kohana_Exception('The Reviews module is not enabled.');
    }
	}
	
	public static function _check_valid_object(Validate $array, $field)
	{	
		if ( ! in_array($array[$field], self::$objects))
		{
			$array->error($field, 'valid');
		}
	}
	
	public static function create($object, $data, $user)
	{
	  if ( ! in_array($object, self::$objects))
	  {
  	  throw new Kohana_Exception('Invalid object.');
	  }
	
  	$review = Jelly::factory('review');
  	
  	$review->object = $object;
  	$review->object_id = $data['product_id'];
  	$review->user = $user;
  	$review->rating = isset($data['rating']) ? $data['rating'] : NULL;
  	$review->review = isset($data['review']) ? $data['review'] : NULL;
  	$review->popularity = 0;
  	
  	$review->save()->update_status(Caffeine::config('moderate_reviews') ? 'awaiting_moderation' : 'active');

  	return $review;
	}
	
	public static function get_average_rating($object)
	{
  	$total = 0;
  	
  	foreach ($object->reviews() as $review)
  	{
    	$total += $review->rating;
  	}
  	
  	return $total > 0 ? $total / count($object->reviews()) : 0;
	}
	
	public static function reviews_to_moderate($limit = 5)
	{
  	return Jelly::select('review')->where('status', '=', 'awaiting_moderation')->order_by('created', 'ASC')->limit($limit)->execute();
	}
	
	// Return the instace of the object which is reviewed
	public function item()
	{
  	return Jelly::select($this->object)->where('id', '=', $this->object_id)->load();
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
	  $this->rating = $data['rating'];
	  $this->review = $data['review'];
	
  	return $this->update_status($data['status'])->save();
	}
}