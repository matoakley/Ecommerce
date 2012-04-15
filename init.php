<?php defined('SYSPATH') or die('No direct script access.');

// Admin routes

Route::set('add_promotion_code_reward', 'admin/promotion_codes/<promotion_code_id>/add_reward')->defaults(array(
	'directory' => 'admin',
	'controller' => 'promotion_codes',
	'action' => 'edit_reward',
	'promotion_code_reward_id' => NULL,
));

Route::set('edit_promotion_code_reward', 'admin/promotion_codes/<promotion_code_id>/edit_reward/<promotion_code_reward_id>')->defaults(array(
	'directory' => 'admin',
	'controller' => 'promotion_codes',
	'action' => 'edit_reward',
));

Route::set('delete_promotion_code_reward', 'admin/promotion_codes/<promotion_code_id>/delete_reward/<promotion_code_reward_id>')->defaults(array(
	'directory' => 'admin',
	'controller' => 'promotion_codes',
	'action' => 'delete_reward',
));

Route::set('add_blog_post', 'admin/blog/add_post')->defaults(array(
	'directory'		=> 'admin',
	'controller'	=> 'blog',
	'action'		=> 'edit_post',
));

Route::set('admin_logout', 'admin/logout')->defaults(array(
	'directory'		=> 'admin',
	'controller'	=> 'users',
	'action'		=> 'logout',
));

Route::set('admin_login', 'admin/login')->defaults(array(
	'directory'		=> 'admin',
	'controller'	=> 'users',
	'action'		=> 'login',
));

Route::set('admin_add', 'admin/<controller>/add')->defaults(array(
	'directory' => 'admin',
	'action' => 'edit',
));

Route::set('admin', 'admin(/<controller>(/<action>(/<id>)))')->defaults(array(
	'directory'		=> 'admin',
	'controller'	=> 'dashboard',
	'action'		=> 'index',
));
	
// Default Public Routes
Route::set('forum_post_view', 'forums/post/<post_slug>')->defaults(array(
	'controller' => 'forums',
	'action' => 'view_post',
));

Route::set('forum_view', 'forums/<category_slug>')->defaults(array(
	'controller' => 'forums',
	'action' => 'view_category',
));

Route::set('forums', 'forums')->defaults(array(
	'controller' => 'forums',
	'action' => 'index',
));

Route::set('stockists', 'stockists')->defaults(array(
	'controller' => 'stockists',
	'action' => 'index',
));

Route::set('view_stockist', 'stockists/<slug>')->defaults(array(
	'controller' => 'stockists',
	'action' => 'view',
));

Route::set('contact_form', 'tools/send-contact-form')->defaults(array(
	'controller' => 'tools',
	'action' => 'send_contact_form',
));

Route::set('customer_reset_password', 'forgotten-password(/<reset_hash>/<email>)', array(
	'email' => '.*', // Allow dots in email address
))->defaults(array(
	'controller' => 'customers',
	'action' => 'forgotten_password',
));

Route::set('customer_dashboard', 'account')->defaults(array(
	'controller' => 'customers',
	'action' => 'index',
)); 

Route::set('customer_login', 'login')->defaults(array(
	'controller' => 'customers',
	'action' => 'login',
)); 

Route::set('customer_logout', 'logout')->defaults(array(
	'controller' => 'customers',
	'action' => 'logout',
)); 

Route::set('blog_view', 'blog/<slug>')->defaults(array(
	'controller' => 'blog',
	'action' => 'view',
)); 

Route::set('blog', 'blog')->defaults(array(
	'controller' => 'blog',
	'action' => 'index',
)); 

Route::set('view_category', 'browse/<slug>')->defaults(array(
	'controller' => 'categories',
	'action' => 'view',
));

Route::set('view_product', 'view/<slug>')->defaults(array(
	'controller' => 'products',
	'action' => 'view',
));

Route::set('view_brand', 'brands/<slug>')->defaults(array(
	'controller' => 'brands',
	'action' => 'view',
));

Route::set('brands', 'brands')->defaults(array(
	'controller' => 'brands',
	'action' => 'index',
));

Route::set('add_basket_item', 'basket/add_item(/<product_id>/<quantity>)')->defaults(array(
	'controller' => 'basket',
	'action' => 'add_item',
));

Route::set('adjust_basket_item', 'basket/adjust_item(/<basket_id>/<quantity>)')->defaults(array(
	'controller' => 'basket',
	'action' => 'adjust_item',
));

Route::set('basket', 'basket')->defaults(array(
	'controller' => 'basket',
	'action' => 'view',
));

Route::set('checkout', 'checkout')->defaults(array(
	'controller' => 'checkout',
	'action' => 'index',
));
	
Route::set('media', 'media(/<file>)', array('file' => '.+'))->defaults(array(
	'controller' => 'media',
	'action'     => 'serve',
	'file'       => NULL,
));
	
Route::set('view_page', 'pages/<slug>')->defaults(array(
	'controller' => 'pages',
	'action' => 'view',
));

Route::set('view_static_page', 'pages/static/<slug>')->defaults(array(
	'controller' => 'pages',
	'action' => 'static',
));

Route::set('search', 'search')->defaults(array(
	'controller' => 'products',
	'action' => 'search',
));

Route::set('sitemap_with_ext', 'sitemap.xml(<gzip>)', array('gzip' => '\.gz'))->defaults(array(
		'controller' => 'tools',
		'action' => 'sitemap',
	));
	 
Route::set('sitemap_no_ext', 'sitemap')->defaults(array(
	'controller' => 'tools',
	'action' => 'sitemap',
));