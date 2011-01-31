<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Users extends Controller_Admin_Application {

	function action_index()
	{			
		$items = 25;

		$search = Model_User::search(array(), $items);

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items'    => $search['count_all'],
			'items_per_page' => $items,
			'auto_hide'	=> false,
		));
		
		// Model_Product::get_admin_products($page, $items)
		$this->template->users = $search['results'];
		$this->template->total_users = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}
	
	function action_edit($id = FALSE)
	{
		$user = Model_User::load($id);
	
		if ($id AND ! $user->loaded())
		{
			throw new Kohana_Exception('User could not be found.');
		}
		
		if ($_POST)
		{
			try
			{				
				$user->update($_POST['user']);	
				$this->request->redirect('/admin/users');
			}
			catch (Validate_Exception $e)
			{
				$this->template->errors = $e->array->errors();
				echo Kohana::debug($e->array->errors());
			}
		}
		
		$this->template->user = $user;
		$this->template->roles = Model_Role::list_all();
	}
	
	function action_login()
	{
		if ($_POST)
		{
			if ($this->auth->login($_POST['email'], $_POST['password']))
			{
				$this->request->redirect($this->session->get_once('redirected_from', ''));
			}
			else
			{
				$this->template->error = TRUE;
			}
		}
	}
	
	function action_logout()
	{
		$this->auto_render = FALSE;

		if ($this->auth->logout())
		{
			$this->request->redirect('admin');
		}
	}
	
}