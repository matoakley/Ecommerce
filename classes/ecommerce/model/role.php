<?php defined('SYSPATH') or die ('No direct script access.');

class Ecommerce_Model_Role extends Model_Auth_Role
{
	public static function list_all()
	{
		return Jelly::select('role')->execute();
	}
}