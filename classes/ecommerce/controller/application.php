<?php defined('SYSPATH') or die('No direct script access.');

abstract class Ecommerce_Controller_Application extends Controller_Template_Twig {

	public $environment = 'production';

	private $breadcrumbs = array();
	
	private $recent_products;
	
	protected $basket;
	
	protected $modules = array();
	
	protected $auth;

	protected $config;

	/**
	 * Setup view
	 *
	 * @return void
	 */
	public function before()
	{
		$this->config = Kohana::config('ecommerce');
		
		Cookie::$salt = $this->config['cookie_salt'].$this->config['site_name'];
		Cookie::$expiration = Date::YEAR;
			
		if ( ! IN_PRODUCTION)
		{
			$this->environment = 'development';
		}
		
		// Initialise session.
		$this->session = Session::instance();
		
		$this->auth = Auth::instance();
		
		$this->basket = Model_Basket::instance();
		
		// Assigning this before the controller is called will prevent the CURRENT product
		// displaying in the Recently Viewed Products list.
		$this->recent_products = $this->session->get('recent_products', array());
		
		$this->modules = Kohana::config('ecommerce.modules');
		
		parent::before();		
	}
	
	public function after()
	{	
		$this->template->show_cookie_warning = ! Cookie::get('cookies_accepted');
	
		$this->template->base_url = URL::base(TRUE, TRUE);
		$this->template->site_name = Kohana::config('ecommerce.site_name');
		
		$this->template->modules = $this->modules;
		
		$this->template->auth = $this->auth;
		
		// Build category tree for navigation
		$this->template->categories = Model_Category::build_category_tree(NULL, TRUE);
		
		$this->template->all_brands = Jelly::select('brand')->where('status', '=', 'active')->execute();
		
		// Set recently viewed products
		$this->template->recent_products = array_reverse($this->recent_products);
	
		// Count number of items in session basket
		$this->template->number_of_basket_items = $this->basket->count_items();
		
		// Assign Recently Viewed Products to template
		$this->template->breadcrumbs = $this->build_breadcrumbs();
	
		// Snippet Manager for templates
		$this->template->snippet = Snippet::instance();
		
		// API key when using Leaflet.js for maps
		$this->template->cloudmade_api_key = Kohana::config('ecommerce.cloudmade_api_key');
		
		$this->template->basket = $this->basket;
	
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
	
	public function requires_login()
	{
		if ( ! $this->auth->logged_in('login'))
		{
			$this->request->redirect(Route::get('login')->uri(array('get' => '?return_url='.$this->request->uri)));
		}
	}
	
}