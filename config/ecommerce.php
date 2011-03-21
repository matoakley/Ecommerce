<?php defined('SYSPATH') or die('No direct access allowed.');

return array
(
	'vat_rate' => 20,
	'default_delivery_option' => 5,
	'site_name' => 'Ecommerce',
	'modules' => array(
		'blog' => TRUE,
		'brands' => TRUE,
		'categories' => TRUE,
		'pages' => TRUE,
		'products' => TRUE,
		'sales_orders' => TRUE,
		'users' => TRUE,
	),
	'admin_list_options' => array(10, 25, 50, 100, 'all'),
	'default_admin_list_option' => 25,
	'default_country' => 1,
);