<?php defined('SYSPATH') or die('No direct script access.');

// Admin routes

Route::set('sales_order_generate_delivery_note', 'admin/sales_orders/<sales_order_id>/generate_delivery_note')->defaults(array(
	'directory' => 'admin',
	'controller' => 'sales_orders',
	'action' => 'generate_delivery_note',
));

Route::set('sales_order_generate_invoice', 'admin/sales_orders/<sales_order_id>/generate_invoice')->defaults(array(
	'directory' => 'admin',
	'controller' => 'sales_orders',
	'action' => 'generate_invoice',
));

Route::set('sales_order_generate_receipt', 'admin/sales_orders/<sales_order_id>/generate_receipt')->defaults(array(
	'directory' => 'admin',
	'controller' => 'sales_orders',
	'action' => 'generate_receipt',
));

Route::set('customer_export_to_sage', 'admin/customers/<customer_id>/export_to_sage')->defaults(array(
	'directory' => 'admin',
	'controller' => 'customers',
	'action' => 'export_to_sage',
));

Route::set('admin_add_sales_order_item', 'admin/sales_orders/add_sales_order_line/<customer_id>(/<sku_id>)')->defaults(array(
	'directory' => 'admin',
	'controller' => 'sales_orders',
	'action' => 'add_sales_order_line',
));

Route::set('customer_contact_add', 'admin/customers/<customer_id>/add_contact')->defaults(array(
	'directory' => 'admin',
	'controller' => 'customers',
	'action' => 'add_contact',
));

Route::set('customer_contact_edit', 'admin/customers/<customer_id>/edit_contact/<contact_id>')->defaults(array(
	'directory' => 'admin',
	'controller' => 'customers',
	'action' => 'edit_contact',
));

Route::set('delete_custom_fields_document', 'admin/custom_fields/<field_id>/delete_document/<object_id>')->defaults(array(
	'directory' => 'admin',
	'controller' => 'custom_fields',
	'action' => 'delete_document',
));

Route::set('customer_contact_delete', 'admin/customers/<customer_id>/delete_contact/<contact_id>')->defaults(array(
	'directory' => 'admin',
	'controller' => 'customers',
	'action' => 'delete_contact',
));

Route::set('customer_address_add', 'admin/customers/<customer_id>/add_address')->defaults(array(
	'directory' => 'admin',
	'controller' => 'customers',
	'action' => 'add_address',
));

Route::set('customer_address_edit', 'admin/customers/<customer_id>/edit_address/<address_id>')->defaults(array(
  'directory' => 'admin',
  'controller' => 'customers',
  'action' => 'edit_address',
));

Route::set('customer_address_delete', 'admin/customers/<customer_id>/delete_address/<address_id>')->defaults(array(
	'directory' => 'admin',
	'controller' => 'customers',
	'action' => 'delete_address',
));

Route::set('customer_communication_add', 'admin/customers/<customer_id>/add_communication')->defaults(array(
	'directory' => 'admin',
	'controller' => 'customers',
	'action' => 'add_communication',
));

Route::set('customer_communication_edit', 'admin/customers/<customer_id>/edit_communication/<communication_id>')->defaults(array(
  'directory' => 'admin',
  'controller' => 'customers',
  'action' => 'edit_communication',
));

Route::set('customer_communication_delete', 'admin/customers/<customer_id>/delete_communication/<communication_id>')->defaults(array(
	'directory' => 'admin',
	'controller' => 'customers',
	'action' => 'delete_communication',
));

Route::set('mark_callback_complete', 'admin/customers/<customer_id>/mark_callback_complete/<communication_id>')->defaults(array(
	'directory' => 'admin',
	'controller' => 'customers',
	'action' => 'mark_callback_complete',
));

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

Route::set('edit_reward_points_profiles', 'admin/reward_points_profiles/edit/<profile_id>')->defaults(array(
	'directory' => 'admin',
	'controller' => 'reward_points_profiles',
	'action' => 'edit',
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

/* MOVED TO BOOTSTAP.PHP IN v1.2.2
Route::set('admin', 'admin(/<controller>(/<action>(/<id>)))')->defaults(array(
	'directory'		=> 'admin',
	'controller'	=> 'dashboard',
	'action'		=> 'index',
));
*/
	
// Default Public Routes

Route::set('admin_add_bundle', 'admin/products/add/<bundle>')->defaults(array(
	'controller' => 'admin_products',
	'action' => 'edit',
));

Route::set('admin_add_products', 'admin/products/add')->defaults(array(
	'controller' => 'admin_products',
	'action' => 'edit',
));

Route::set('accept_cookies', 'accept-cookies')->defaults(array(
	'controller' => 'tools',
	'action' => 'accept_cookies',
));

Route::set('public_wish_list_page', 'wish_lists/public/<wish_list_id>')
	->defaults(array(
		'controller' => 'wish_lists',
		'action' => 'public_wish_list_page',
	));

Route::set('add_to_wish_list', 'wish_lists/wish-list-items/<product_slug>/add')
	->defaults(array(
		'controller' => 'wish_lists',
		'action' => 'add_to_wish_lists',
	));
	
Route::set('remove_from_wish_list', 'wish_lists/<product_slug>/remove')
	->defaults(array(
		'controller' => 'wish_lists',
		'action' => 'remove_from_wish_lists',
	));

Route::set('wish_lists', 'wish_lists/wish_list/')
	->defaults(array(
		'controller' => 'wish_lists',
		'action' => 'wish_lists',
	));
	
Route::set('event_delete', 'events/<event_slug>/delete')->defaults(array(
	'controller' => 'events',
	'action' => 'delete',
));

Route::set('event_add', 'events/add')->defaults(array(
	'controller' => 'events',
	'action' => 'add',
));

Route::set('event', 'events')->defaults(array(
	'controller' => 'events',
	'action' => 'index',
));

Route::set('events', 'events')->defaults(array(
	'controller' => 'events',
	'action' => 'index',
));

Route::set('event_view', 'events/<event_slug>')->defaults(array(
	'controller' => 'events',
	'action' => 'view',
));

Route::set('events_index', 'events(/month/<month>)')->defaults(array(
	'controller' => 'events',
	'action' => 'index',
));

Route::set('forum_post_new', 'forums/<category_slug>/new')->defaults(array(
	'controller' => 'forums',
	'action' => 'new_post',
));

Route::set('forum_post_view', 'forums/<category_slug>/<post_slug>')->defaults(array(
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

Route::set('customer_register', 'register')->defaults(array(
  'controller' => 'customers',
  'action' => 'create_account',
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

Route::set('blog_rss', 'blog(/category/<blog_category_slug>)/feed.xml')->defaults(array(
		'controller' => 'blog',
		'action' => 'rss_feed',
	));

Route::set('blog_view', 'blog/<slug>')->defaults(array(
	'controller' => 'blog',
	'action' => 'view',
)); 

Route::set('view_blog_category', 'blog/category/<slug>')->defaults(array(
	'controller' => 'blog_categories',
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

Route::set('basket_from_sales_order', 'basket/create-from-sales-order/<sales_order_id>')->defaults(array(
	'controller' => 'basket',
	'action' => 'create_from_sales_order',
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

Route::set('checkout_login', 'checkout/login')->defaults(array(
	'controller' => 'checkout',
	'action' => 'login',
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
		'human' => FALSE,
	));
	 
Route::set('human-sitemap', 'sitemap')->defaults(array(
	'controller' => 'tools',
	'action' => 'sitemap',
	'human' => TRUE,
));