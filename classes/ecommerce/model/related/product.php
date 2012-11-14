<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Related_Product extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('related_products')
		->sorting(array('id' => 'ASC'))
			->fields(array(
			  'id' => new Field_Primary,
				'product' => new Field_BelongsTo(array(
					'foreign' => 'product.id',
					'column' => 'product_id',
				)),
				'related_id' => new Field_BelongsTo(array(
					'foreign' => 'product.id',
					'column' => 'related_id',
				)),
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

	public static function add_to_related_products($product_id, $related_id)
	{
	  $product = Jelly::select('product')->where('id', '=', $product_id)->load();
	  $related = Jelly::select('product')->where('id', '=', $related_id)->load();
	  
		$related_product = Jelly::factory('related_product');
		
		$related_product->product = $product;
		$related_product->related_id = $related;
		
		$related_product->save();
	}
	
	public static function remove_from_related_products($product_id, $related_id)
	{
	  $related_product = Jelly::select('related_product')->where('product_id', '=', $product_id)->where('related_id', '=', $related_id)->load();
	  
		$related_product->delete();
	}
	
	public static function get_related_products($product_id)
	{
  	$related_product_items = Jelly::select('related_product')->where('product_id', '=', $product_id)->execute();

    $related_products = array();
  
  //for each of them load the model             
  foreach ($related_product_items->as_array() as $key => $id)
    {
      $related_products[$key] = Jelly::select('product')->where('id', '=', intval($id['related_id']))->load();
      
    }

      return $related_products;
	}
	
	/*
	
  public static function get_users_wish_list_items($user = NULL)
	{
	  if ($user != NULL) 
	   {
  	   $user_id = $user->id;
      	
      	$wish_list_items = Jelly::select('wish_list')->where('user_id', '=', $user_id)->execute();
      	
      	return $wish_list_items;
     }
	}
*/
}