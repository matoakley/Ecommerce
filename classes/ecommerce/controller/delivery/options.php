<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Controller_Delivery_Options extends Controller_Application
{
	function action_available_options(){
  	
  	 $this->auto_render = FALSE;
  	
  	Model_Delivery_Option::available_options($_POST['id']);
  	
	}
}