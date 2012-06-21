<?php defined('SYSPATH') or die('No direct script access.');

abstract class Ecommerce_Controller_Admin_Application extends Controller_Template_Twig
{
	public $environment = 'production';

	protected $scripts = array();

	protected $modules = array();
	
	/**
	 * Setup view
	 *
	 * @return void
	 */
	public function before()
	{
		// Attempt to use SSH if available as we're dealing with log ins
		if(Request::$protocol != 'https' AND IN_PRODUCTION AND ! Kohana::config('ecommerce.no_ssl'))
		{
			$this->request->redirect(URL::site(Request::Instance()->uri, 'https'));
		}
	
		if ( ! IN_PRODUCTION)
		{
			$this->environment = 'development';
		}
		
		$this->modules = Kohana::config('ecommerce.modules');
		
		parent::before();
		
		// Initialise session.
		$this->session = Session::instance();
		
		// Initialise Auth
		$this->auth = Auth::instance();
		
		// Check that our guest is logged in as an admin
		if ( ! $this->auth->logged_in('admin') AND $this->request->uri() != 'admin/login')
		{
			$this->session->set('redirected_from', $this->request->uri());
			$this->request->redirect('/admin/login');
		}
		
		$this->list_option = $this->session->get('admin_list_option', Kohana::config('ecommerce.default_admin_list_option'));
		
		// If the request is AJAX then we'll want to spit out a JSON encoded
		// array rather than an HTML template.
		if (Request::$is_ajax)
		{
			$this->auto_render = FALSE;
		}
	}
	
	public function after()
	{	
		$this->template->modules = $this->modules;
	
		$this->template->base_url = URL::base(TRUE, TRUE);
		
		$this->template->scripts = $this->scripts;
		
		$this->template->auth = $this->auth;
		
		$this->template->site_name = Kohana::config('ecommerce.site_name');
		
		$this->template->version_number = Kohana::config('ecommerce.software_version');
		
		// API key when using Leaflet.js for maps
		$this->template->cloudmade_api_key = Kohana::config('ecommerce.cloudmade_api_key');

		
		parent::after();
	}
}