<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Customers extends Controller_Application
{
	function before()
	{
		// Check that customer accounts module is enabled
		if ( ! Kohana::config('ecommerce.modules.customer_accounts'))
		{
			throw new Kohana_Exception('This module is not enabled');
		}
		
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
		
		$this->template->customer = $this->auth->get_user()->customer;
		
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
				if (isset($_GET['return_url']))
				{
					$this->request->redirect('/'.$_GET['return_url']);
				}
				else
				{
					$this->request->redirect(Route::get('customer_dashboard')->uri());
				}
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
		if ($hash = $this->request->param('reset_hash') AND $email = $this->request->param('email'))
		{
			// Check if email address and hash match our records
			if ($user = Model_Customer::validate_password_reset($email, $hash))
			{
				$this->template->valid_params = TRUE;
				
				if ($_POST)
				{
					// Reset the password!
					try
					{
						$user->change_password($_POST['new_password']);
						$this->auth->login($email, $_POST['new_password']);
						$this->request->redirect(Route::get('customer_dashboard')->uri());
					}
					catch (Validate_Exception $e)
					{
						$this->template->invalid_password = TRUE;
					}
				}
			}	
			else
			{
				$this->template->invalid_params = TRUE;	
			}	
		
			// Show form allowing user to reset password
			
			
			// If POSTed then process the reset
		}
		elseif ( ! $this->request->param())
		{
			if ($_POST)
			{
				try
				{
					Model_Customer::send_forgotten_password_email($_POST['email']);
					$this->template->email_sent = TRUE;
					$this->template->email = $_POST['email'];
				}
				catch (Kohana_Exception $e)
				{
					$this->template->login_failed = TRUE;
					$this->template->email = $_POST['email'];
				}
			}
		}
		
		$this->add_breadcrumb(URL::site(Route::get('customer_dashboard')->uri()), 'Account');
		$this->add_breadcrumb(URL::site(Route::get('customer_reset_password')->uri()), 'Forgotten Password');		
	}
	
	public function action_create_account()
	{
		if ( ! $_POST)
		{
			throw new Kohana_Exception('No data posted');
		}
		
		try
		{
			$customer = Model_Customer::load($_POST['customer_id']);
			$customer->create_account($_POST['password']);
			$this->auth->force_login($customer->user);
			$this->request->redirect(Route::get('customer_dashboard')->uri());
		}
		catch (Kohana_Exception $e)
		{
			$this->request->redirect(Route::get('customer_dashboard')->uri());
		}
	}
	
	public function action_redeem_customer_referral_code()
	{
	  $this->auto_render = FALSE;
	  
  	$code = Model_Customer::redeem_customer_referral_code($_POST);
  	
  	if ($code == "ok")
  	{
    	$basket = Model_Basket::load($_POST['basket']);
    	$basket->referral_code = $_POST['code'];
  	}
  	
  	if ($code != NULL)
  	{
    	echo json_encode($code);
  	}
  	
	}
}