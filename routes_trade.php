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

Route::set('view_category', 'browse/<slug>')->defaults(array(
	'directory' => 'trade',
	'controller' => 'categories',
	'action' => 'view',
));

Route::set('view_product', 'view/<slug>')->defaults(array(
	'directory' => 'trade',
	'controller' => 'products',
	'action' => 'view',
));

Route::set('search', 'search')->defaults(array(
	'directory' => 'trade',
	'controller' => 'products',
	'action' => 'search',
));

Route::set('edit_account', 'account/edit')->defaults(array(
	'directory' => 'trade',
	'controller' => 'users',
	'action' => 'edit_account',
));

Route::set('change_password', 'account/change-password')->defaults(array(
	'directory' => 'trade',
	'controller' => 'users',
	'action' => 'change_password',
));

Route::set('order_history', 'account/orders')->defaults(array(
	'directory' => 'trade',
	'controller' => 'users',
	'action' => 'order_history',	
));

Route::set('blog_view', 'blog/<slug>')->defaults(array(
	'directory' => 'trade',
	'controller' => 'blog',
	'action' => 'view',
)); 

Route::set('view_blog_category', 'blog/category/<slug>')->defaults(array(
	'directory' => 'trade',
	'controller' => 'blog_categories',
	'action' => 'view',
));

Route::set('blog', 'blog')->defaults(array(
	'directory' => 'trade',
	'controller' => 'blog',
	'action' => 'index',
));