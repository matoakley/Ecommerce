<?php defined('SYSPATH') or die('No direct script access.');

abstract class Ecommerce_Controller_Application extends Controller_Template_Twig {

	public $environment = 'production';

	private $breadcrumbs = array();
	
	private $recent_products;
	
	protected $basket;

	/**
	 * Setup view
	 *
	 * @return void
	 */
	public function before()
	{
		if ( ! IN_PRODUCTION)
		{
			$this->environment = 'development';
		}
		
		// Initialise session.
		$this->session = Session::instance();
		
		$this->basket = Model_Basket::instance();
		
		// Assigning this before the controller is called will prevent the CURRENT product
		// displaying in the Recently Viewed Products list.
		$this->recent_products = $this->session->get('recent_products', array());
		
		parent::before();
	}
	
	public function after()
	{	
		$this->template->base_url = URL::base(TRUE, TRUE);
		
		// Build category tree for navigation
		$this->template->categories = Model_Category::build_category_tree(NULL, TRUE);
		
		// Set recently viewed products
		$this->template->recent_products = array_reverse($this->recent_products);
	
		// Count number of items in session basket
		$this->template->number_of_basket_items = $this->basket->count_items();
		
		// Assign Recently Viewed Products to template
		$this->template->breadcrumbs = $this->build_breadcrumbs();
		
		// Show Kohana profiler if viewing from home IP address
		// if (Request::$client_ip == '95.172.233.145')
		// {
		// 	$this->template->kohana_profiler =  View::factory('profiler/stats');
		// }
	
		parent::after();
	}
	
	protected function add_breadcrumb($link, $name)
	{
		$this->breadcrumbs[$link] = $name;
	}
	
	private function build_breadcrumbs()
	{		
		return array_merge(array('/' => 'Home'), $this->breadcrumbs);
	}
	
}