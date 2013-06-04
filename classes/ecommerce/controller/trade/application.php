<?php defined('SYSPATH') or die('No direct script access.');

abstract class Ecommerce_Controller_Trade_Application extends Controller_Template_Twig {

	public $environment = 'production';

	private $breadcrumbs = array();
	
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
		if ( ! Caffeine::modules('trade_area'))
		{
			throw new Kohana_Exception('The "trade_area" module is not enabled.');
		}
	
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
		
		$this->modules = Kohana::config('ecommerce.modules');
		
		// Users must be logged in to trade area
		if ( ! $this->auth->logged_in('trade_area') AND ! in_array(Route::name($this->request->route), array('sign_in', 'sign_up', 'sign_up_received')) AND $this->request->uri != 'users/trade_forgotten_password')
		{
			$this->session->set('redirected_from', $this->request->uri());
			$this->request->redirect(Route::get('sign_in')->uri());
		}
		
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
	
		// Count number of items in session basket
		$this->template->number_of_basket_items = $this->basket->count_items();
		
		// Assign Recently Viewed Products to template
		$this->template->breadcrumbs = $this->build_breadcrumbs();
	
		// Snippet Manager for templates
		$this->template->snippet = Snippet::instance();
		
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
}