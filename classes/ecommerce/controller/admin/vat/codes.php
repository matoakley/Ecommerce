<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Vat_Codes extends Controller_Admin_Application
{
	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.vat_codes'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
	}
	
	public function action_index()
	{
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;
		
		$search = Model_Vat_Code::search(array(), $items);
		
		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items' => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.vat_codes.index', $_SERVER['REQUEST_URI']);
		
		$this->template->vat_codes = $search['results'];
		$this->template->total_vat_codes = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}
	
	public function action_edit()
	{
		$vat_code = Model_Vat_Code::load($this->request->param('id'));
	
		if ($this->request->param('id') AND ! $vat_code->loaded())
		{
			throw new Kohana_Exception('VAT Code could not be found.');
		}
		
		$redirect_to = $this->session->get('admin.vat_codes.index', '/admin/vat_codes');
		$this->template->cancel_url = $redirect_to;
		
		$fields = array(
			'vat_code' => $vat_code->as_array(),
		);
		$errors = array();
		
		if ($_POST)
		{
			try
			{
				$vat_code->update($_POST['vat_code']);
								
				// If 'Save & Exit' has been clicked then lets hit the index with previous page/filters
				if (isset($_POST['save_exit']))
				{
					$this->request->redirect($redirect_to);
				}
				else
				{
					$this->request->redirect('/admin/vat_codes/edit/'.$vat_code->id);
				}
			}
			catch (Validate_Exception $e)
			{
				$this->template->errors['vat_code'] = $e->array->errors();
				$fields = $_POST;
			}
		}
		
		$this->template->fields = $fields;
		$this->template->errors = $errors;
		
		$this->template->vat_code = $vat_code;
	}

	public function action_delete($id = NULL)
	{
		$this->auto_render = FALSE;
		
		$vat_code = Model_Vat_Code::load($id);
		$vat_code->delete();
		
		$this->request->redirect($this->session->get('admin.vat_codes.index', 'admin/vat_codes'));
	}
}