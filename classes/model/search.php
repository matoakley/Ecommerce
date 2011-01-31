<?php defined('SYSPATH') or die('No direct script access.');

class Model_Search extends Model_Application
{
    public static function initialize(Jelly_Meta $meta)
    {
        $meta->table('searches')
            ->fields(array(
                'id' => new Field_Primary,
				'search_term' => new Field_String,
				'number_of_results' => new Field_Integer,
				'ip_address' => new Field_String,
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
 
	public static function record($search_term, $number_of_results)
	{
		$search = Jelly::factory('search');
		$search->ip_address = $_SERVER['REMOTE_ADDR'];
		$search->search_term = $search_term;
		$search->number_of_results = $number_of_results;
		return $search->save();
	}
    
}