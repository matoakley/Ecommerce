<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Customers extends Controller_Application
{
	function before()
	{
		// Attempt to use SSH if available as we're dealing with log ins
		if(Request::$protocol != 'https' AND IN_PRODUCTION AND ! Kohana::config('ecommerce.no_ssl'))
		{
			$this->request->redirect(URL::site(Request::Instance()->uri, 'https'));
		}
		
		parent::before();
	}

	public function action_index()
	{
		// If user isn't logged in, send them for authentication
		if ( ! $this->auth->logged_in('customer'))
		{
			$this->request->redirect(Route::get('customer_login')->uri());
		}
		
		$this->add_breadcrumb(URL::site(Route::get('customer_dashboard')->uri()), 'Account');
	}
	
	public function action_login()
	{
		// If user is already logged in then redirect to dashboard
		if ($this->auth->logged_in('customer'))
		{
			$this->request->redirect(Route::get('customer_dashboard')->uri());
		}
	
		// Process the log in
		if ($_POST)
		{
			if ($this->auth->login($_POST['login']['email'], $_POST['login']['password']) AND $this->auth->logged_in('customer'))
			{
				$this->request->redirect(Route::get('customer_dashboard')->uri());
			}
			else
			{
				// Force a log out in case the user has authenticated as an admin rather than customer
				$this->auth->logout();
				$this->template->email = $_POST['login']['email'];
				$this->template->login_failed = TRUE;
			}
		}
		
		$this->add_breadcrumb(URL::site(Route::get('customer_dashboard')->uri()), 'Account');
		$this->add_breadcrumb(URL::site(Route::get('customer_login')->uri()), 'Log In');
	}
	
	public function action_logout()
	{
		// Log out and redirect to log in page
		$this->auth->logout();
		$this->request->redirect(Route::get('customer_login')->uri());
	}
	
	public function action_forgotten_password()
	{
		
	}
}