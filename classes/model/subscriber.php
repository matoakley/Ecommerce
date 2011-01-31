<?php defined('SYSPATH') or die('No direct script access.');

class Model_Subscriber extends Model_Application
{
    public static function initialize(Jelly_Meta $meta)
    {
        $meta->table('subscribers')
            ->fields(array(
                'id' => new Field_Primary,
				'email' => new Field_String,
				'customer' => new Field_BelongsTo,
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

	public static function create($email, $customer_id = NULL)
	{
		// Check if email address already exists as a Subscriber to avoid duplicates
		$subscriber = Jelly::select('subscriber')->where('email', '=', $email)->limit(1)->execute();
		
		// If not, add them...
		if ( ! $subscriber->loaded())
		{
			$subscriber = Jelly::factory('subscriber')->set(array(
				'email' => $email,
				'customer_id' => $customer_id,
			))->save();
		}
		
		return $subscriber;
	}
}