<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Comments extends Controller_Application
{
	public function before()
	{
		if ( ! Kohana::config('ecommerce.modules.comments'))
		{
			throw new Kohana_Exception('The Comments module is not enabled');
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
    	$comment = Model_Comment::create($_POST['comment'], $this->auth->get_user());
    }
    catch (Validate_Exception $e)
    {
      $errors['comment'] = $e->array->errors('model/comment');
    }
    
    if (Request::$is_ajax)
    {
      $this->auto_render = FALSE;
      $this->request->headers['Content-Type'] = 'application/json';
      echo json_encode(array(
        'errors' => $errors,
        'comment' => isset($comment) ? $comment->as_array() : NULL,
      ));
    }
	}
}