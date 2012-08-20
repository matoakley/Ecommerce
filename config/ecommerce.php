<?php defined('SYSPATH') or die('No direct access allowed.');

return array
(
	'software_version' => '1.2.2', 

	'vat_rate' => 20,
	'default_delivery_option' => 5,
	'default_web_customer_type' => 1, // When using CRM customer types
	'default_commercial_customer_type' => 2,
	
	'site_name' => 'Creative Intent Ecommerce Software',
	'email_from_address' => 'ecommerce@creativeintent.co.uk',
	'email_from_name' => 'Creative Intent Ecommerce Software',
	'copy_order_confirmations_to' => '',
	
	'default_invoice_terms' => 30,
	
	'pagination' => array(
		'products' => 10,
		'blog_posts' => 10,
		'crm_customer_items' => 20,
	),
	
	'modules' => array(
		'blog' => FALSE,
		'blog_categories' => FALSE,
		'brands' => FALSE,
		'categories' => FALSE,
		'commercial_sales_orders' => FALSE,
		'crm' => FALSE,
		'custom_fields' => FALSE,
		'customer_accounts' => FALSE,
		'dashboard_enhanced_sales_orders' => FALSE,
		'delivery_options' => FALSE,
		'events' => FALSE,
		'forums' => FALSE,
		'geocoded_addresses' => FALSE,
		'pages' => FALSE,
		'product_options' => FALSE,
		'product_weights' => FALSE,
		'products' => FALSE,
		'promotion_codes' => FALSE,
		'sage_exports' => FALSE,
		'sales_orders' => FALSE,
		'snippets' => FALSE,
		'stock_control' => FALSE,
		'stockists' => FALSE,
		'tiered_pricing' => FALSE,
		'trade_area' => FALSE,
		'users' => FALSE,
		'vat_codes' => FALSE,
		
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
	
	'cloudmade_api_key' => 'af4f31e0445f463ebe783a749812d374', // Generic API key from http://cloudmade.com/ if you plan to use Leaflet.js for maps
	
	'forum_post_name_max_length' => 245,
	
	'cookie_salt' => 'YasUr4LYWG4e87Tg8yIJZb6iAjssQokzdW1Z9uSqe4UD6IMgj83M',
);
