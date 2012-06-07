<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Forum_Banned_Word extends Model_Application
{	
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->sorting(array('word' => 'ASC'))
			->fields(array(
				'id' => new Field_Primary,
				'word' => new Field_String(array(
					'rules' => array(
						'not_empty' => NULL,
					),
				)),
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
	
	// Returns string with banned words censored out
	public static function censor($string)
	{
		return Text::censor($string, self::list_words());
	}
	
	public static function list_words()
	{
		return Jelly::select('forum_banned_word')->execute()->as_array('id', 'word');
	}
	
	public function update($data)
	{
		$this->word = strtolower($data['word']);
		
		return $this->save();
	}
}