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
		
		$this->template->customer = $this->auth->get_user()->get('customer')->load();
		
		if (Caffeine::modules('reviews'))
		{
  			$this->template->customer_reviews = $this->get_customer_reviews();
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
	
		$ajax_data = array();
	
		// Process the log in
		if ($_POST)
		{  
		  $user_login = $this->auth->login($_POST['login']['email'], $_POST['login']['password']);
		  $user_logged_in = $this->auth->logged_in('customer');
		  $user = $this->auth->get_user();
		  $verified = TRUE;
		  
		  if (Caffeine::modules('email_verification') AND $user_logged_in)
		  {
  		  $verified = $user->verification;
		  }
		  
			if ($user_login == TRUE AND $user_logged_in == TRUE AND $verified == TRUE)
			{
			  // If customer is logging in, clear any referral codes from basket 
    		if (Caffeine::modules('reward_points'))
    		{
      		$this->basket->reset_referral_code();
    		}
    		
    		if (Request::$is_ajax)
    		{
      	  $ajax_data['user'] = $this->auth->get_user()->as_array();
    		}
    		else
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
			}
			else
			{
				// Force a log out in case the user has authenticated as an admin rather than customer
				$this->auth->logout();
				$this->template->email = $_POST['login']['email'];
				$this->template->login_failed = TRUE;
				
				$ajax_data['error'] = TRUE;
			}
		}
		elseif (Request::$is_ajax)
		{
  		throw new Kohana_Exception('No data POSTed.');
		}
		
		if (Request::$is_ajax)
		{
  		echo json_encode($ajax_data);
  		exit;
		}
		
		$this->add_breadcrumb(URL::site(Route::get('customer_dashboard')->uri()), 'Account');
		$this->add_breadcrumb(URL::site(Route::get('customer_login')->uri()), 'Log In');
	}
	
	public function action_logout()
	{
		// Log out and redirect to log in page
		$this->auth->logout();
		
		// If customer has been logged in and added reward 
		// points to their basket, we should remove them now
		if (Caffeine::modules('reward_points'))
		{
  		$this->basket->reset_reward_points();
		}
		
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
	
	public function action_reset_trade_password()
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
						$this->request->redirect(Route::get('shop')->uri());
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
		
		$this->add_breadcrumb(URL::site(Route::get('shop')->uri()), 'Shop');
		$this->add_breadcrumb(URL::site(Route::get('customer_trade_reset_password')->uri()), 'Reset Password');		
	}

	
	public function action_create_account()
	{
	  $fields = array();
	  $errors = array();
	 
	  if ($_POST)
	  {
	    $customer = isset($_POST['customer_id']) ? Model_Customer::load($_POST['customer_id']) : Model_Customer::load();
	  
	    if ( ! $customer->loaded())
	    {
	      try
	      {
  	      $customer->validate($_POST); 
	      }
	      catch (Validate_Exception $e)
	      {
  	      $errors['customer'] = $e->array->errors('model/customer');
	      }
	    }
	  
	    $user = Model_User::load();
	    try
	    {
  	    $user->validate($_POST);
	    }
	    catch (Validate_Exception $e)
	    {
  	    $errors['user'] = $e->array->errors('model/user');
	    }
	  
	    if (empty($errors))
	    { 
	      /* email verification */
	      if (Caffeine::modules('email_verification'))
	        {
      	     if ( ! $customer->loaded())
    	        {
      	        $customer = Model_Customer::create($_POST);
    	        }
      	        
          	   $customer->create_account($_POST['password'], isset($_POST['username']) ? $_POST['username'] : isset($_POST['username']));
          	   
            	 $customer->send_email_verification($customer->user);
            	 $this->request->redirect(Route::get('email_verification')->uri());
            }
            else
              {
                 try
                     {
              	        if ( ! $customer->loaded())
                  	        {
                    	        $customer = Model_Customer::create($_POST);
                  	        }
	        
                  	   $customer->create_account($_POST['password'], isset($_POST['username']) ? $_POST['username'] : isset($_POST['username']));
              
                  	   $this->auth->force_login($customer->user);
                  	   $this->request->redirect(Route::get('customer_dashboard')->uri());
                  	 }
            	  catch (Kohana_Exception $e)
            	  {
          			  $this->request->redirect(Route::get('customer_dashboard')->uri());
          		  }
              }
  	    }
	    else
	    {
  	    $fields = $_POST;
	    }
    }
    
    $this->template->fields = $fields;
    $this->template->errors = $errors;
    
		$this->add_breadcrumb(URL::site(Route::get('customer_dashboard')->uri()), 'Account');
		$this->add_breadcrumb(URL::site(Route::get('customer_register')->uri()), 'Register');		
	}	
	
	public function action_activate_account()
	{
	 
  	$id = $this->request->param('email_verification_id');
  	
  	$user = Jelly::select('user')->where('email_verification_id', '=', $id)->load();
  	
  	if ($user->loaded())
  	  {
  	    $user->verification = TRUE;
  	    $user->save();
  	  }
    
    $this->request->redirect(Route::get('customer_dashboard')->uri());
  }
  
  public function get_customer_reviews()
  {
    $user_id = $this->auth->get_user()->id;
    return $reviews = Jelly::select('review')->where('user_id', '=', $user_id)->execute();
  }
  
  public function action_trade_forgotten_password()
	{
	 // If user is already logged in then send them to the trade homepage
		if ($this->auth->logged_in('trade'))
		{
			$this->request->redirect(Route::get('default')->uri());
		}
		
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
						$this->request->redirect(Route::get('default')->uri());
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
					Model_Customer::send_trade_forgotten_password_email($_POST['email']);
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
		
		$this->add_breadcrumb(URL::site(Route::get('default')->uri()), 'Home');
		$this->add_breadcrumb(URL::site(Route::get('trade_customer_reset_password')->uri()), 'Forgotten Password');		
	}

}