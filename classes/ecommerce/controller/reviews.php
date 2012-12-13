<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Reviews extends Controller_Application
{
	public function before()
	{
		if ( ! Kohana::config('ecommerce.modules.reviews'))
		{
			throw new Kohana_Exception('The Reviews module is not enabled');
		}
		
		parent::before();
	}
	
	public function action_add()
	{
  	if ( ! $_POST)
  	{
    	throw new Kohana_Exception('No data posted');
  	}
  	
  	if ( ! $this->auth->logged_in('customer'))
  	{
    	throw new Kohana_Exception('User is not logged in.');
  	}
  	
  	$errors = array();
  	
  	try
  	{
    	$review = Model_Review::create($_POST['object'], $_POST['review'], $this->auth->get_user());
    }
    catch (Validate_Exception $e)
    {
      $errors['review'] = $e->array->errors('model/review');
    }
    
    if (Request::$is_ajax)
    {
      $this->auto_render = FALSE;
      $this->request->headers['Content-Type'] = 'application/json';
      echo json_encode(array(
        'errors' => $errors,
        'review' => isset($review) ? $review->as_array() : NULL,
      ));
    }
	}
}