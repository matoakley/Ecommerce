<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Tools extends Controller_Admin_Application
{
	public function action_items_per_page($items = FALSE)
	{
		if ( ! Request::$is_ajax)
		{
			throw new Kohana_Exception('Not found', NULL, 404);
		}
		
		$this->auto_render = FALSE;
		
		if ( ! $items)
		{
			$items = Kohana::config('ecommerce.default_admin_list_options');
		}
		
		$this->session->set('admin_list_option', $items);
		
		echo 'Success!';
	}
}
