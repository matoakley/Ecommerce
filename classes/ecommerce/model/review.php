<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Review extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->fields(array(
			'id' => new Field_Primary,
			'product' => new Field_BelongsTo,
			'user' => new Field_BelongsTo,
			'rating' => new Field_Integer,
			'review' => new Field_Text,
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
	
	public static function get_average_rating($product)
	{
  	$total = 0;
  	
  	foreach ($product->reviews as $review)
  	{
    	$total += $review->rating;
  	}
  	
  	return $total > 0 ? $total / count($product->reviews) : 0;
	}
}