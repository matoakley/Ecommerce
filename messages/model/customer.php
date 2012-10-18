<?php defined('SYSPATH') or die('No direct script access.');

return array(

	'firstname' => array(
		'not_empty' => 'First name cannot be blank.',
	),
	
	'lastname' => array(
		'not_empty' => 'Last name cannot be blank.',
	),
	
	'email' => array(
  	'not_empty' => 'Email address cannot be blank.',
  	'email' => 'Enter a valid email address.',
	),

);