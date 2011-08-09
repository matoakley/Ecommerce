<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Promotion_Codes extends Controller_Admin_Application {

	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.promotion_codes'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
	}

	function action_index()
	{		
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;

		$search = Model_Promotion_Code::search(array(), $items);

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items' => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.promotion_codes.index', $_SERVER['REQUEST_URI']);
		
		$this->template->promotion_codes = $search['results'];
		$this->template->total_promotion_codes = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}
	
	function action_edit($id = FALSE)
	{
		$promotion_code = Model_Promotion_Code::load($id);
	
		if ($id AND ! $promotion_code->loaded())
		{
			throw new Kohana_Exception('Promotion Code could not be found.');
		}
		
		$redirect_to = $this->session->get('admin.promotion_codes.index', 'admin/promotion_codes');
		$this->template->cancel_url = $redirect_to;
		
		$fields = array();
		$fields['promotion_code'] = $promotion_code->as_array();
		
		$errors = array();
		
		if ($_POST)
		{
			$start_date = DateTime::CreateFromFormat('d/m/Y H:i', $_POST['valid_from_date'].' '.$_POST['valid_from_hour'].':'.$_POST['valid_from_minute']);
			$end_date = DateTime::CreateFromFormat('d/m/Y H:i', $_POST['valid_to_date'].' '.$_POST['valid_to_hour'].':'.$_POST['valid_to_minute']);
		
			$_POST['promotion_code']['start_date'] = $start_date->format('U');
			$_POST['promotion_code']['end_date'] = $end_date->format('U');
			
			try
			{
				$promotion_code->validate($_POST['promotion_code']);
			}
			catch (Validate_Exception $e)
			{
				$errors['promotion_code'] = $e->array->errors();
			}
					
			if (empty($errors))
			{
				$promotion_code->update($_POST['promotion_code']);
				
				// If 'Save & Exit' has been clicked then lets hit the index with previous page/filters
				if (isset($_POST['save_exit']))
				{
					$this->request->redirect($redirect_to);
				}
				else
				{
					$this->request->redirect('/admin/promotion_codes/edit/' . $promotion_code->id);
				}
			}
			else
			{
				$fields['promotion_code'] = $_POST['promotion_code'];
			}
		}
		
		$this->template->fields = $fields;
		$this->template->errors = $errors;
		
		$this->template->promotion_code = $promotion_code;
		$this->template->statuses = Model_Promotion_Code::$statuses;
	}

	public function action_auto_generate()
	{
		$this->auto_render = FALSE;
		echo Model_Promotion_Code::generate_unique_code();
		exit;
	}
	
	public function action_delete($id = NULL)
	{
		$this->auto_render = FALSE;
		
		$promotion_code = Model_Promotion_Code::load($id);
		$promotion_code->delete();
		
		$this->request->redirect($this->session->get('admin.promotion_codes.index', 'admin/promotion_codes'));
	}
}