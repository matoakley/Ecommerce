<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Customer_Types extends Controller_Admin_Application
{
	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.crm'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
	}
	
	public function action_index()
	{
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;

		$search = Model_Customer_Type::search(array(), $items);

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items'  => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.customer_types.index', $_SERVER['REQUEST_URI']);
		
		$this->template->customer_types = $search['results'];
		$this->template->total_customer_types = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}
	
	public function action_edit()
	{
		$customer_type = Model_Customer_Type::load($this->request->param('id'));
	
		if ($this->request->param('id') AND ! $customer_type->loaded())
		{
			throw new Kohana_Exception('Customer Type could not be found.');
		}
		
		$redirect_to = $this->session->get('admin.customer_types.index', '/admin/customer_types');
		$this->template->cancel_url = $redirect_to;
		
		$fields = array(
			'customer_type' => $customer_type->as_array(),
		);
		$errors = array();
		
		if ($_POST)
		{
			try
			{
				$customer_type->update($_POST['customer_type']);
								
				// If 'Save & Exit' has been clicked then lets hit the index with previous page/filters
				if (isset($_POST['save_exit']))
				{
					$this->request->redirect($redirect_to);
				}
				else
				{
					$this->request->redirect('/admin/customer_types/edit/' . $customer_type->id);
				}
			}
			catch (Validate_Exception $e)
			{
				$errors['customer_type'] = $e->array->errors();
				$fields['customer_type'] = $_POST['customer_type'];
			}
		}
		
		$this->template->fields = $fields;
		$this->template->errors = $errors;
		
		$this->template->customer_type = $customer_type;
	}
	
	public function action_delete()
	{
		$this->auto_render = FALSE;
		
		$customer_type = Model_Customer_Type::load($this->request->param('id'));
		
		if ( ! $customer_type->loaded())
		{
			throw new Kohana_Exception('Customer Type could not be found.');
		}
		
		$customer_type->delete();
		
		$this->request->redirect( $this->session->get('admin.customer_types.index', 'admin/customer_types'));
	}
}