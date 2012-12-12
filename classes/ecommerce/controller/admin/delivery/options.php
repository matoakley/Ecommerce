<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Delivery_Options extends Controller_Admin_Application {

	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.delivery_options'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
	}
	
	function action_index()
	{
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;

		$search = Model_Delivery_Option::search(array(), $items);

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items' => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.delivery_options.index', $_SERVER['REQUEST_URI']);

		$this->template->delivery_options = $search['results'];
		$this->template->total_options = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
		$this->template->default_delivery_option = Jelly::select('delivery_option')->where('default', '=', 1)->load();
	}
	
  public function action_edit($id = FALSE)
	{
		$delivery_option = Model_Delivery_Option::load($id);
	
		if ($id AND ! $delivery_option->loaded())
		{
			throw new Kohana_Exception('Delivery Option could not be found.');
		}
		
		$fields = array(
			'delivery_option' => $delivery_option->as_array(),
		);
		$errors = array();
		
		$fields['delivery_option']['price'] = $delivery_option->retail_price();
		
		if ( ! $delivery_option->loaded())
		{
  		$fields['delivery_option']['customer_selectable'] = TRUE;
		}
		
		$redirect_to = $this->session->get('admin.delivery_options.index', '/admin/delivery_options');
		$this->template->cancel_url = $redirect_to;
		
		if ($_POST)
		{
			try
			{
				$delivery_option->validate($_POST['delivery_option']);
			}
			catch (Validate_Exception $e)
			{
				$errors['delivery_option'] = $e->array->errors();
			}
			
			if (empty($errors))
			{
				$delivery_option->update($_POST['delivery_option']);
				
				// If 'Save & Exit' has been clicked then lets hit the index with previous page/filters
				if (isset($_POST['save_exit']))
				{
					$this->request->redirect($redirect_to);
				}
				else
				{
					$this->request->redirect('/admin/delivery_options/edit/' . $delivery_option->id);
				}
			}
			else
			{
				$fields['delivery_option'] = $_POST['delivery_option'];
			}	
		}
		
		$this->template->fields = $fields;
		$this->template->errors = $errors;
		
		$this->template->delivery_option = $delivery_option;
		$this->template->statuses = Model_Delivery_Option::$statuses;
	}

	public function action_delete($id = NULL)
	{
		$this->auto_render = FALSE;
		
		$delivery_option = Model_Delivery_Option::load($id);
		$delivery_option->delete();
		
		$this->request->redirect($this->session->get('admin.delivery_options.index', 'admin/delivery_options'));
	}
	
	public function action_default_delivery_option()
	{
  	$delivery_option = Model_Delivery_Option::load($_POST['default']);
  	$delivery_option->default = 1;
  	$delivery_option->save();
  	
  	$other_options = Jelly::select('delivery_option')->where('id', '<>', $delivery_option->id)->execute();
  	
  	foreach ($other_options as $other)
  	  {
    	  $other->default = 0;
    	  $other->save();
  	  }
	}
}