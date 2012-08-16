<?php defined('SYSPATH') or die('No direct script access.');

Route::set('sign_in', 'sign-in')->defaults(array(
	'directory' => 'trade',
	'controller' => 'users',
	'action' => 'sign_in',
));

Route::set('sign_out', 'sign-out')->defaults(array(
	'directory' => 'trade',
	'controller' => 'users',
	'action' => 'sign_out',
));

Route::set('sign_up', 'sign-up')->defaults(array(
	'directory' => 'trade',
	'controller' => 'users',
	'action' => 'sign_up',
));

Route::set('sign_up_received', 'sign-up-requested')->defaults(array(
	'directory' => 'trade',
	'controller' => 'users',
	'action' => 'sign_up_requested',
));

Route::set('view_category', 'trade/browse/<slug>')->defaults(array(
	'directory' => 'trade',
	'controller' => 'categories',
	'action' => 'view',
));

Route::set('view_product', 'trade/view/<slug>')->defaults(array(
	'directory' => 'trade',
	'controller' => 'products',
	'action' => 'view',
));