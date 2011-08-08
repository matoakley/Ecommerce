<?php defined('SYSPATH') or die('No direct access allowed.');

return array
(
	'software_version' => '1.1.0', 

	'vat_rate' => 20,
	'default_delivery_option' => 5,
	
	'site_name' => 'Creative Intent Ecommerce Software',
	'email_from_address' => 'ecommerce@creativeintent.co.uk',
	'email_from_name' => 'Creative Intent Ecommerce Software',
	'copy_order_confirmations_to' => '',
	
	'modules' => array(
		'blog' => TRUE,
		'brands' => TRUE,
		'categories' => TRUE,
		'pages' => TRUE,
		'products' => TRUE,
		'promotion_codes' => TRUE,
		'sales_orders' => TRUE,
		'users' => TRUE,
	),
	'admin_list_options' => array(10, 25, 50, 100, 'all'),
	'default_admin_list_option' => 25,
	'default_country' => 1,
	'default_promotion_code_length' => 6,
	
	// Image sizing
	'image_sizing' => array(
		'thumbnail' => array(
			'width' => 100,
			'height' => 100,
		),
		'full_size' => array(
			'width' => 280,
			'height' => 280,		
		),
	),
	
);