<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Customer_Communication extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->sorting(array('created' => 'DESC'))
			->fields(array(
				'id' => new Field_Primary,
				'customer' => new Field_BelongsTo,
				'type' => new Field_String(array(
					'rules' => array(
						'not_empty' => NULL,
					),
				)),
				'title' => new Field_String(array(
					'rules' => array(
						'not_empty' => NULL,
					),
				)),
				'text' => new Field_Text,
				'date' => new Field_Timestamp(array(
					'format' => 'Y-m-d H:i:s',
				)),
				'callback_on' => new Field_Timestamp(array(
				  'format' => 'Y-m-d H:i:s',
				)),
				'callback_assigned_to' => new Field_BelongsTo(array(
				  'foreign' => 'user.id',
          'column' => 'callback_user_id',
				)),
				'callback_completed_on' => new Field_Timestamp(array(
				  'format' => 'Y-m-d H:i:s',
				)),
				'callback_completed_by' => new Field_BelongsTo(array(
				  'foreign' => 'user.id',
          'column' => 'callback_completed_user_id',
				)),
				'user' => new Field_BelongsTo,
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
	
	public static $types = array(
		'email',
		'telephone',
		'mail',
		'note',
	);
	
	public static function create_communication_for_customer($customer, $data)
	{
  	$communication = Jelly::factory('customer_communication');
		
		$communication->customer = $customer;
		$communication->user = Auth::instance()->get_user();
		
		if ( ! in_array($data['type'], self::$types))
		{
			throw new Kohana_Exception('Unknown Customer Communication type');
		}
		$communication->type = $data['type'];
		
		$communication->title = $data['title'];
		$communication->text = $data['text'];
		$communication->date = $data['date'];
		
		$communication->callback_on = $data['callback_on'];
		$communication->callback_assigned_to = $data['callback_assigned_to'];
		
		return $communication->save();
	}
	
	public function update()
	{
  	if (isset($_POST['text']))
  	{
      $this->text = $_POST['text'];
    }

    if (isset($_POST['title']))
    {	
      $this->title = $_POST['title'];
    }
  	
  	return $this->save();
	}
	
	public function mark_callback_complete()
	{
  	$this->callback_completed_on = time();
  	$this->callback_completed_by = Auth::instance()->get_user();
  	return $this->save();
	}
}