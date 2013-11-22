<?php defined('SYSPATH') or die('No direct access allowed.');

class Ecommerce_Model_Auth_Role extends Model_Auth_Role {

	public static function initialize(Jelly_Meta $meta)
	{
		$meta->name_key('name')
			->fields(array(
			'id' => new Field_Primary,
			'name' => new Field_String(array(
				'unique' => TRUE,
				'rules' => array(
					'max_length' => array(32),
					'not_empty' => NULL
				)
			)),
			'description' => new Field_Text,
			'users' => new Field_ManyToMany
		));
	}
	
	public function let_echo()
	{
  	echo Kohana::debug('woohoo');exit;
	}
} // End Auth Role Model