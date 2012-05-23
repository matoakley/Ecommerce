<?php defined('SYSPATH') or die('No direct access allowed.');

return array
(
	'software_version' => '1.1.8', 

	'vat_rate' => 20,
	'default_delivery_option' => 5,
	
	'site_name' => 'Creative Intent Ecommerce Software',
	'email_from_address' => 'ecommerce@creativeintent.co.uk',
	'email_from_name' => 'Creative Intent Ecommerce Software',
	'copy_order_confirmations_to' => '',
	
	'pagination' => array(
		'products' => 10,
	),
	
	'modules' => array(
		'blog' => FALSE,
		'brands' => FALSE,
		'categories' => FALSE,
		'custom_fields' => FALSE,
		'customer_accounts' => FALSE,
		'delivery_options' => FALSE,
		'events' => FALSE,
		'forums' => FALSE,
		'geocoded_addresses' => FALSE,
		'pages' => FALSE,
		'product_options' => FALSE,
		'products' => FALSE,
		'promotion_codes' => FALSE,
		'sales_orders' => FALSE,
		'snippets' => FALSE,
		'stock_control' => FALSE,
		'stockists' => FALSE,
		'users' => FALSE,
	),
	
	'admin_list_options' => array(10, 25, 50, 100, 'all'),
	'default_admin_list_option' => 25,
	'default_country' => 1,
	'default_promotion_code_length' => 6,
	
	'no_ssl' => FALSE, // Disable SSL redirect on checkout
	
	// Product and Category Image sizing
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
  'blog_image_sizing' => array(
    'width' => 310,
    'height' => 250,
  ),
	
	'default_google_product_category' => '', // Find category in http://www.google.com/support/merchants/bin/answer.py?answer=160081
	
	'cloudmade_api_key' => '', // API key from http://cloudmade.com/ if you plan to use Leaflet.js for maps
	
	'forum_post_name_max_length' => 245,
);
