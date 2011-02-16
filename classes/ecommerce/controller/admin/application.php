<?php defined('SYSPATH') or die('No direct script access.');

abstract class Ecommerce_Controller_Admin_Application extends Controller_Template_Twig {

	public $environment = 'production';

	protected $scripts = array();

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
		
		parent::before();
		
		// Initialise session.
		$this->session = Session::instance();
		
		// Initialise Auth
		$this->auth = Auth::instance();
		
		// Check that our guest is logged in...
		if ( ! $this->auth->logged_in() AND $this->request->uri() != 'admin/login')
		{
			$this->session->set('redirected_from', $this->request->uri());
			$this->request->redirect('/admin/login');
		}
	}
	
	public function after()
	{	
		$this->template->modules = Kohana::config('ecommerce.modules');
	
		$this->template->base_url = URL::base(TRUE, TRUE);
		
		$this->template->scripts = $this->scripts;
		
		$this->template->auth = $this->auth;
		
		parent::after();
	}
}