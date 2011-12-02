<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Snippet extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('snippets')
			->fields(array(
				'id' => new Field_Primary,
				'name' => new Field_String(array(
					'rules' => array(
						'not_empty' => NULL,
					),
				)),
				'description' => new Field_String,
				'content' => new Field_Text,
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
	
	public static function show($name)
	{
		return Jelly::select('snippet')->where('name', '=', $name)->limit(1)->execute();
	}
	
	public function update($data)
	{
		$this->name = $data['name'];
		$this->description = $data['description'];
		$this->content = $data['content'];
		$this->save();
	}
}