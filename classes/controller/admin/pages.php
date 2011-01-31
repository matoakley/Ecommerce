<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Pages extends Controller_Admin_Application {

	function action_index()
	{
		$items = 25;
		
		$search = Model_Page::search(array(), $items);
		
		// Pagination
		$this->template->pagination = Pagination::factory(array(
			'total_items'    => $search['count_all'],
			'items_per_page' => $items,
			'auto_hide'	=> false,
		));
		
		$this->template->pages = $search['results'];
		$this->template->total_pages = $search['count_all'];
		$this->template->page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$this->template->items = $items;
	}
	
}