<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Event_Log extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
	   $meta->table('event_logs')
	       ->fields(array(
				'id' => new Field_Primary,
				'change' => new Field_String,
				'event' => new Field_BelongsTo(array(
				  'column' => 'event',
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
	
	public static function create($event = NULL, $change = 'An event was changed')
	{
  	$log = Jelly::factory('event_log');
  	$log->change = $change;
  	
  	if ($event != NULL && $event->loaded())
  	{
    	$log->event = $event;
  	}
  	
  	$log->save();
	}
}