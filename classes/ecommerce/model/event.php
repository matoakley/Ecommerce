<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Event extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
	   $meta->table('events')
	       ->fields(array(
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
				'end_date' => new Field_Timestamp(array(
					'format' => 'Y-m-d H:i:s',
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
	public static $statuses = array(
		'active', 'disabled'
	);

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
	
	public static function event_validator($data)
	{
		$validator = Validate::factory($data)
											->filter(TRUE, 'trim')
											->rule('name', 'not_empty')
											->rule('description', 'not_empty')
											->rule('start_date', 'not_empty')
											->rule('end_date', 'not_empty');
		
		if ( ! $validator->check())
		{
			throw new Validate_Exception($validator);
		}
		
		return TRUE;
	}
	
	public static function get_events_by_month($month, $limit = NULL, $offset = NULL)
	{	
		$year = floor($month / 12);
		$month = ($month % 12 != -1) ? $month % 12 : 12;
	
		$date = mktime(12, 0, 0, $month, 1, date('Y') + $year);
	
		$query = Jelly::select('event')->where(DB::expr('MONTH(start_date)'), '=', date('m', $date))->where(DB::expr('YEAR(start_date)'), '=', date('Y', $date))->where('status', '=', 'active')->order_by('start_date', 'ASC');
		
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
	
	public function update($data)
	{	
  	   $errors = array();
			if (isset($_POST['event']))
			{
				try
				{
					//event_validator($_POST['event']);
				}
				catch (Validate_Exception $e)
				{
					$errors['event'] = $e->array->errors();
				}
		

		$this->name = $data['name'];
		if (isset($data['slug']))
		{
			$this->slug = $data['slug'];
		}
		if (isset($data['status']))
		{
		$this->status = $data['status'];
		}
		$this->address = $data['address'];
		$this->start_date = $data['start_date'];
		$this->end_date = $data['end_date'];
		$this->description = $data['description'];
		$this->save();
		
		// Ping sitemap to search engines to alert them of content change
		if (IN_PRODUCTION AND $this->status == 'active')
		{
			Sitemap::ping(URL::site(Route::get('sitemap_index')->uri()), TRUE);
		}
		
		return $this;
	}
 }
}