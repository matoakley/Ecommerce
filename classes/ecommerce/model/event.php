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
	
	public static $searchable_fields = array(
		'filtered' => array(
			'status' => array(
				'field' => 'status',
			),
		),
		'search' => array(
			'name',
			'description',
		),
	);
	
	public static function get_events_by_month($month, $limit = NULL, $offset = NULL, $bounding_box = NULL)
	{	
		$year = floor($month / 12);
		$month = ($month % 12 != 0) ? $month % 12 : 12;
	
		$date = mktime(12, 0, 0, $month, 1, date('Y') + $year);
	
		$query = Jelly::select('event')->where(DB::expr('MONTH(start_date)'), '=', date('m', $date))->where(DB::expr('YEAR(start_date)'), '=', date('Y', $date))->where('status', '=', 'active')->order_by('start_date', 'ASC');
		
		if ($bounding_box)
		{
			$query->join('addresses')->on('events.address_id', '=', 'addresses.id')
						->where('latitude', '>',  $bounding_box[0])
						->where('latitude', '<',  $bounding_box[1])
						->where('longitude', '>',  $bounding_box[2])
						->where('longitude', '<',  $bounding_box[3]);
		}
		
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