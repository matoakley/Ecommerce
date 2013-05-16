<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Price_Tiers extends Controller_Admin_Application
{
	function before()
	{
		if ( ! Kohana::config('ecommerce.modules.tiered_pricing'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
	
		parent::before();
	}
	
	public function action_index()
	{
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;

		$search = Model_Price_Tier::search(array(), $items);

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items'  => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.price_tiers.index', $_SERVER['REQUEST_URI']);
		
		$this->template->price_tiers = $search['results'];
		$this->template->total_price_tiers = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}
	
	public function action_edit()
	{
		$tier = Model_Price_Tier::load($this->request->param('id'));
	
		if ($this->request->param('id') AND ! $tier->loaded())
		{
			throw new Kohana_Exception('Price Tier could not be found.');
		}
	
		$redirect_to = $this->session->get('admin.price_tiers.index', '/admin/price_tiers');
		$this->template->cancel_url = $redirect_to;
	
		$fields = array(
			'tier' => $tier->as_array(),
		);
		$errors = array();
		
		if ($_POST)
		{
			try
			{
				$tier->validate($_POST['tier']);
			}
			catch (Validate_Exception $e)
			{
				$errors['tier'] = $e->array->errors();
			}
			
			if (empty($errors))
			{
				$tier->update($_POST['tier']);
			
				// If 'Save & Exit' has been clicked then lets hit the index with previous page/filters
				if (isset($_POST['save_exit']))
				{
					$this->request->redirect($redirect_to);
				}
				else
				{
					$this->request->redirect('/admin/price_tiers/edit/'.$tier->id);
				}
			}
			else
			{
				$fields = $_POST;
			}
		}
		
		$this->template->fields = $fields;
		$this->template->errors = $errors;
		
		$this->template->tier = $tier;
	}
	
	public function action_delete()
	{
	  $this->auto_render = FALSE;
	  
	  $price_tier = Model_Price_Tier::load();
    $price_tier->delete($this->request->param('id'));
    
    $this->request->redirect('/admin/price_tiers/');
	}
}