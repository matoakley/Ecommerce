<?php defined('SYSPATH') or die('No direct script access.');

// Only include XLS and PDF libraries if not CLI as missing
// $_SERVER['SERVER_NAME'] causes error.
if ( ! Kohana::$is_cli)
{
	// Load the HTML2PDF class autoloader
	require Kohana::find_file('vendor', 'html2pdf/html2pdf.class');
	// Load the PHPExcel class autoloader
	require Kohana::find_file('vendor', 'phpexcel/PHPExcel');
}

Caffeine::$is_trade = IS_TRADE;

if ( ! Caffeine::$is_trade)
{
	require MODPATH.'ecommerce/routes'.EXT;
}
else
{
	require MODPATH.'ecommerce/routes_trade'.EXT;
}
//enabled the aacl module for permissions based on user roles.
Kohana::modules($modules + array(
    'aacl'      => MODPATH.'aacl',      
));