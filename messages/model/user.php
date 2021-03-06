<?php defined('SYSPATH') or die('No direct script access.');

return array(

	'email' => array(
		'not_empty' => 'Email address cannot be blank.',
		'email' => 'Email address is not valid.',
		'unique' => 'Email address is already in use.',
	),
	
	'username' => array(
		'not_empty' => 'Email cannot be blank.',
		'email' => 'Email address is not valid.',
		'unique' => 'Email address is already in use.',
	),
	
	'password' => array(
		'not_empty' => 'Password cannot be blank.',
		'min_length' => 'Password too short.',
	),
	
	'password_confirm' => array(
		'not_empty' => 'Confirm your password.',
		'min_length' => 'Password too short.',
		'matches' => 'Does not match password.',
	),

);