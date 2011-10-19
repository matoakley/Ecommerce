<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Admin_Countries extends Controller_Admin_Application {

	public function action_index()
	{
		$items = ($this->list_option != 'all') ? $this->list_option : FALSE;
		
		$search = Model_Country::search(array(), $items);

		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items' => $search['count_all'],
			'items_per_page' => ($items) ? $items : $search['count_all'],
			'auto_hide'	=> false,
			'view' => 'pagination/admin',
		));
		
		// Set URI into session for redirecting back from forms
		$this->session->set('admin.countries.index', $_SERVER['REQUEST_URI']);
		
		$this->template->countries = $search['results'];
		$this->template->total_countries = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}
}