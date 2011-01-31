<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Errors extends Controller_Application {

	function action_404()
	{
		$this->request->status = 404;
		$this->add_breadcrumb('errors/404', 'Page Not Found');
	}

}