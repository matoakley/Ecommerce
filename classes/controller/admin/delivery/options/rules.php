<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Delivery_Options_Rules extends Ecommerce_Controller_Admin_Delivery_Options_Rules {

	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.delivery_options_rules'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
	}
	
	function action_index()
	{ 
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;

		$search = Model_Delivery_Options_Rule::search(array(), $items);

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items' => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.delivery_options_rules.index', $_SERVER['REQUEST_URI']);

		$this->template->delivery_options_rules = $search['results'];
		$this->template->total_delivery_option_rules = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}
	
  public function action_edit($id = FALSE)
	{
		$delivery_options_rule = Model_Delivery_Options_Rule::load($id);
	
		if ($id AND ! $delivery_options_rule->loaded())
		{
			throw new Kohana_Exception('Delivery Option could not be found.');
		}
		
		$fields = array(
			'delivery_options_rule' => $delivery_options_rule->as_array(),
		);
		$errors = array();
		
		$redirect_to = $this->session->get('admin.delivery_options_rules.index', '/admin/delivery_options_rules');
		$this->template->cancel_url = $redirect_to;
		
		if ($_POST)
		{
			try
			{
				$delivery_options_rule->validate($_POST['delivery_options_rule']);
			}
			catch (Validate_Exception $e)
			{
				$errors['delivery_options_rule'] = $e->array->errors();
			}
			
			if (empty($errors))
			{
				$delivery_options_rule->update($_POST['delivery_options_rule']);
				
				// If 'Save & Exit' has been clicked then lets hit the index with previous page/filters
				if (isset($_POST['save_exit']))
				{
					$this->request->redirect($redirect_to);
				}
				else
				{
					$this->request->redirect('/admin/delivery_options_rules/edit/' . $delivery_options_rule->id);
				}
			}
			else
			{
				$fields['delivery_options_rule'] = $_POST['delivery_options_rule'];
			}	
		}
		
		$this->template->fields = $fields;
		$this->template->errors = $errors;
		$this->template->delivery_options = Jelly::select('delivery_option')->where('deleted', 'IS', NULL)->execute();
		$this->template->delivery_options_rule = $delivery_options_rule;
		$this->template->statuses = Model_delivery_options_rule::$statuses;
	}

	public function action_delete($id = NULL)
	{
		$this->auto_render = FALSE;
		
		$delivery_options_rule = Model_delivery_options_rule::load($id);
		$delivery_options_rule->delete();
		
		$this->request->redirect($this->session->get('admin.delivery_options_rules.index', 'admin/delivery_options_rules'));
	}
}