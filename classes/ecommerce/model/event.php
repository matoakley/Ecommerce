<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Event extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->fields(array(
				'id' => new Field_Primary,
				'name' => new Field_String(array(
					'rules' => array(
						'max_length' => array(100),
						'not_empty' => NULL,
					),
				)),
				'slug' => new Field_String,
				'description' => new Field_Text(array(
					'rules' => array(
						'not_empty' => NULL,
					),
				)),
				'start_date' => new Field_Timestamp(array(
					'format' => 'Y-m-d H:i:s',
				)),
				'address' => new Field_BelongsTo,
				'status' => new Field_String,
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
	
	public static function get_events_by_month($month, $limit = NULL, $offset = NULL)
	{
		$query = Jelly::select('event')->where(DB::expr('MONTH(start_date)'), '=', $month)->where('status', '=', 'active')->order_by('start_date', 'ASC');
		
		if ($limit)
		{
			$query->limit($limit);
		}
		
		if ($offset)
		{
			$query->offset($offset);
		}		
	
		return $query->execute();
	}
}