<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Wish_List extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('wish_lists')
		->sorting(array('user_id' => 'ASC'))
			->fields(array(
			  'id' => new Field_Primary,
				'user' => new Field_BelongsTo(array(
					'foreign' => 'user.id',
					'column' => 'user_id',
				)),
				'product' => new Field_BelongsTo(array(
					'foreign' => 'product.id',
					'column' => 'product_id',
				)),
				'public_identifier' => new Field_String,
				'deleted' => new Field_Timestamp(array(
					'format' => 'Y-m-d H:i:s',
				)),
						));
	}

	public function add_watch_item($user, $product)
	{
	  
	  if (! $user->wish_list_id)
	    {
  	    $user->generate_wish_list_id();
	    }
	  $user = Jelly::select('user')->where('id', '=', $user->id)->load();
	  
		$wish_list_item = Jelly::factory('wish_list');
		
		$wish_list_item->user = $user;
		$wish_list_item->product = $product;
		$wish_list_item->public_identifier = $user->wish_list_id;
		
		$wish_list_item->save();
	}
	
	public static function remove_watch_item($user, $product)
	{
	  
	  $wish_list_item = Jelly::select('wish_list')->where('user_id', '=', $user->id)->where('product_id', '=', $product->id)->load();
	  
	  if (! $wish_list_item->loaded())
	  {
  	  	throw new Kohana_Exception("Wish List item not found");
	  }
	  
	  $wish_list_item->delete();
	}
	
  public static function get_users_wish_list_items($user = NULL)
	{
	  if ($user != NULL) 
	   {
  	   $user_id = $user->id;
      	
      	$wish_list_items = Jelly::select('wish_list')->where('user_id', '=', $user_id)->execute();
      	
      	return $wish_list_items;
     }
	}
}