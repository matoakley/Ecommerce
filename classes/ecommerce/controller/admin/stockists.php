<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Stockists extends Controller_Admin_Application
{
	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.stockists'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
	}
	
	public function action_index()
	{
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;
		
		$search = Model_Stockist::search(array(), $items);
		
		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items' => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.stockists.index', $_SERVER['REQUEST_URI']);
		
		$this->template->stockists = $search['results'];
		$this->template->total_stcokists = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}

	public function action_edit($id = FALSE)
	{
		$stockist = Model_Stockist::load($id);
	
		if ($id AND ! $stockist->loaded())
		{
			throw new Kohana_Exception('Stockist could not be found.');
		}
		
		$fields = array(
			'stockist' => $stockist->as_array(),
			'address' => $stockist->address->as_array(),
		);
		$errors = array();
		
		$redirect_to = $this->session->get('admin.stockists.index', 'admin/stockists');
		$this->template->cancel_url = $redirect_to;
		
		if ($_POST)
		{
			try
			{
				$stockist->validate($_POST['stockist']);
			}
			catch (Validate_Exception $e)
			{
				$errors['stockist'] = $e->array->errors();
			}
		
			try
			{
				$stockist->address->validate($_POST['address']);
			}
			catch (Validate_Exception $e)
			{
				$errors['address'] = $e->array->errors();
			}
			
			if (empty($errors))
			{
				$stockist->address->update($_POST['address']);
				$_POST['stockist']['address'] = $stockist->address;
				$stockist->update($_POST['stockist']);
				
				// If 'Save & Exit' has been clicked then lets hit the index with previous page/filters
				if (isset($_POST['save_exit']))
				{
					$this->request->redirect($redirect_to);
				}
				else
				{
					$this->request->redirect('/admin/stockists/edit/' . $stockist->id);
				}
			}
			else
			{
				$fields['stockist'] = $stockist->as_array();
				$fields['address'] = $stockist->address->as_array(); 
			}
		}
		
		$this->template->fields = $fields;
		$this->template->errors = $errors;
		
		$this->template->stockist = $stockist;
		$this->template->statuses = Model_Stockist::$statuses;
	}
	
	public function action_delete($id = NULL)
	{
		$this->auto_render = FALSE;
		
		$brands = Model_Stockist::load($id);
		$brands->delete();
		
		$this->request->redirect($this->session->get('admin.stockists.index', 'admin/stockists'));
	}

}