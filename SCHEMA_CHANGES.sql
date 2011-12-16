-- 13/12/12 - Add default shipping columns to Customer
ALTER TABLE `customers` ADD COLUMN `default_billing_address_id` int AFTER `email`, ADD COLUMN `default_shipping_address_id` int AFTER `default_billing_address_id`, CHANGE COLUMN `referred_by` `referred_by` varchar(255) DEFAULT NULL AFTER `default_shipping_address_id`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `referred_by`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;

-- 21/11/11 - Add customer role
INSERT INTO `roles` (`name`, `description`) VALUES ('customer', 'Customers who have chosen to create an account.');

-- Link customers to their user
ALTER TABLE `customers` ADD COLUMN `user_id` int AFTER `id`, CHANGE COLUMN `firstname` `firstname` varchar(255) NOT NULL AFTER `user_id`, CHANGE COLUMN `lastname` `lastname` varchar(255) NOT NULL AFTER `firstname`, CHANGE COLUMN `email` `email` varchar(255) NOT NULL AFTER `lastname`, CHANGE COLUMN `referred_by` `referred_by` varchar(255) DEFAULT NULL AFTER `email`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `referred_by`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;

-- MANUALLY ADD ADMIN ROLE (2) TO EXISTING USERS OR THEY WON'T BE ABLE TO LOG IN

-- Allow 254 characters for username and email address
ALTER TABLE `users` CHANGE COLUMN `email` `email` varchar(254) NOT NULL, CHANGE COLUMN `username` `username` varchar(254) NOT NULL DEFAULT '';

-- Add Snippets table
CREATE TABLE `snippets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(254) NOT NULL,
  `description` varchar(254) DEFAULT NULL,
  `content` text,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;






-- ================================================================================================================================================================================================ --

-- VERSION 1.1.4 UPGRADE


-- 28/10/2011 - Add SKU table
CREATE TABLE `skus` (
	`id` int,
	`product_id` int,
	`price` decimal(10,4) NOT NULL,
	`sku` varchar(255),
	`stock` int,
	`created` datetime,
	`modified` datetime NOT NULL,
	`deleted` datetime NOT NULL,
	PRIMARY KEY (`id`)
);
ALTER TABLE `skus` ADD COLUMN `status` varchar(25) NOT NULL AFTER `stock`, CHANGE COLUMN `created` `created` datetime DEFAULT NULL AFTER `status`, CHANGE COLUMN `modified` `modified` datetime NOT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime NOT NULL AFTER `modified`;
ALTER TABLE `skus` CHANGE COLUMN `id` `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `skus` CHANGE COLUMN `created` `created` datetime NOT NULL, CHANGE COLUMN `modified` `modified` datetime, CHANGE COLUMN `deleted` `deleted` datetime;

CREATE TABLE `product_options_skus` (
	`product_option_id` int NOT NULL,
	`sku_id` int NOT NULL
);

-- Basket items should now link to SKUs, keep legacy columns for backwards compatibility
ALTER TABLE `basket_items` ADD COLUMN `sku_id` int NOT NULL AFTER `basket_id`, CHANGE COLUMN `product_id` `product_id` int(11) NOT NULL AFTER `sku_id`, CHANGE COLUMN `product_options` `product_options` varchar(255) DEFAULT NULL AFTER `product_id`, CHANGE COLUMN `quantity` `quantity` int(11) NOT NULL AFTER `product_options`, CHANGE COLUMN `created` `created` datetime DEFAULT NULL AFTER `quantity`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;
ALTER TABLE `basket_items` CHANGE COLUMN `product_id` `product_id` int(11);

-- Sales order items should now link to SKUs, keep legacy columns for backwards compatibility
ALTER TABLE `sales_order_items` ADD COLUMN `sku_id` int NOT NULL AFTER `sales_order_id`, CHANGE COLUMN `product_id` `product_id` int(11) AFTER `sku_id`, CHANGE COLUMN `product_name` `product_name` varchar(255) NOT NULL AFTER `product_id`, CHANGE COLUMN `product_options` `product_options` text DEFAULT NULL AFTER `product_name`, CHANGE COLUMN `quantity` `quantity` int(11) NOT NULL AFTER `product_options`, CHANGE COLUMN `unit_price` `unit_price` decimal(10,2) NOT NULL AFTER `quantity`, CHANGE COLUMN `total_price` `total_price` decimal(10,2) NOT NULL AFTER `unit_price`, CHANGE COLUMN `vat_rate` `vat_rate` decimal(10,2) NOT NULL AFTER `total_price`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `vat_rate`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;

-- 15/11/11 - Products no longer require a price
ALTER TABLE `products` CHANGE COLUMN `price` `price` decimal(10,4) DEFAULT '0.0000';






-- ================================================================================================================================================================================================ --

-- 19/08/2011 - Add ISO3611-1 column to countries
ALTER TABLE `countries` ADD COLUMN `iso_3_code` varchar(2) AFTER `iso_code`;

-- 09/08/2011 - Change format of price field to decimal for better accuracy WARNING: This will round existing prices!
ALTER TABLE `delivery_options` CHANGE COLUMN `price` `price` decimal(10,4) DEFAULT NULL;

-- 09/08/2011 - Record Delivery Option statically on Sales Orders in case they are edited at a later date
ALTER TABLE `sales_orders` ADD COLUMN `delivery_option_name` varchar(255) AFTER `delivery_option_id`, ADD COLUMN `delivery_option_price` decimal(10,4) AFTER `delivery_option_name`;

-- 09/08/2011 - Making Delivery Options user manageable so need some new fields
ALTER TABLE `delivery_options` ADD COLUMN `status` varchar(255) AFTER `price`, ADD COLUMN `created` datetime NOT NULL AFTER `status`, ADD COLUMN `modified` datetime AFTER `created`, ADD COLUMN `deleted` datetime AFTER `modified`;

-- 08/08/2011 - Add a stock field to Products table
ALTER TABLE `products` ADD COLUMN `stock` int;

-- 05/06/11 - Add sales order items to promotion codes
ALTER TABLE `promotion_codes` ADD COLUMN `discount_on` varchar(30) NOT NULL AFTER `basket_minimum_value`, CHANGE COLUMN `discount_amount` `discount_amount` decimal(10,0) DEFAULT NULL AFTER `discount_on`, CHANGE COLUMN `discount_unit` `discount_unit` varchar(20) DEFAULT NULL AFTER `discount_amount`, CHANGE COLUMN `status` `status` varchar(25) DEFAULT NULL AFTER `discount_unit`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `status`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;

CREATE TABLE `products_promotion_codes` (
	`product_id` int NOT NULL,
	`promotion_code_id` int NOT NULL
);

-- 27/05/11 - Add promotion code link to sales order table
ALTER TABLE `sales_orders` ADD COLUMN `promotion_code_id` int AFTER `delivery_option_id`, ADD COLUMN `discount_amount` decimal(10,4) AFTER `promotion_code_id`, CHANGE COLUMN `status` `status` varchar(25) NOT NULL AFTER `discount_amount`, CHANGE COLUMN `order_total` `order_total` decimal(10,2) NOT NULL AFTER `status`, CHANGE COLUMN `ip_address` `ip_address` varchar(15) NOT NULL AFTER `order_total`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `ip_address`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;
ALTER TABLE `sales_orders` ADD COLUMN `promotion_code_code` varchar(50) AFTER `promotion_code_id`, CHANGE COLUMN `discount_amount` `discount_amount` decimal(10,0) DEFAULT NULL AFTER `promotion_code_code`, CHANGE COLUMN `status` `status` varchar(25) NOT NULL AFTER `discount_amount`, CHANGE COLUMN `order_total` `order_total` decimal(10,2) NOT NULL AFTER `status`, CHANGE COLUMN `ip_address` `ip_address` varchar(15) NOT NULL AFTER `order_total`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `ip_address`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;

-- 26/05/11 - Add promotion code link to basket table
ALTER TABLE `baskets` ADD COLUMN `promotion_code_id` int AFTER `sales_order_id`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `promotion_code_id`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;

-- 25/05/11 - Add table for Promotional Codes
CREATE TABLE `promotion_codes` (
  `id` int(11) NOT NULL auto_increment,
  `code` varchar(20) default NULL,
  `description` varchar(255) default NULL,
  `max_redemptions` int(11) default NULL,
  `redeemed` int(11) NOT NULL default '0',
  `start_date` datetime default NULL,
  `end_date` datetime default NULL,
  `basket_minimum_value` decimal(10,0) default NULL,
  `discount_amount` decimal(10,0) default NULL,
  `discount_unit` varchar(20) default NULL,
  `status` varchar(25) default NULL,
  `created` datetime NOT NULL,
  `modified` datetime default NULL,
  `deleted` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- 14/05/2011 - Slug can be null to allow for product duplication
ALTER TABLE `products` CHANGE COLUMN `slug` `slug` varchar(255);

-- 19/04/2011 - Update new unit price field in sales_order_items
UPDATE `sales_order_items` SET `unit_price` = (`total_price` / `quantity`);

-- 19/04/2011 - Add unit price to sales_order_items
ALTER TABLE `sales_order_items` ADD COLUMN `unit_price` decimal(10,2) NOT NULL AFTER `quantity`, CHANGE COLUMN `total_price` `total_price` decimal(10,2) NOT NULL AFTER `unit_price`, CHANGE COLUMN `vat_rate` `vat_rate` decimal(10,2) NOT NULL AFTER `total_price`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `vat_rate`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;

-- 19/04/2011 - Update new product name field in sales_order_items
UPDATE `sales_order_items` SET `product_name` = (SELECT `name` FROM `products` WHERE `products`.`id` = `product_id`);

--19/04/2011 - Add product name and product options fied to sales_order_items
ALTER TABLE `sales_order_items` ADD COLUMN `product_name` varchar(255) NOT NULL AFTER `product_id`, ADD COLUMN `product_options` text AFTER `product_name`, CHANGE COLUMN `quantity` `quantity` int(11) NOT NULL AFTER `product_options`, CHANGE COLUMN `total_price` `total_price` decimal(10,2) NOT NULL AFTER `quantity`, CHANGE COLUMN `vat_rate` `vat_rate` decimal(10,2) NOT NULL AFTER `total_price`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `vat_rate`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;

-- 19/04/2011 - Add product options field to basket_items
ALTER TABLE `basket_items` ADD COLUMN `product_options` text AFTER `product_id`, CHANGE COLUMN `quantity` `quantity` int(11) NOT NULL AFTER `product_options`, CHANGE COLUMN `created` `created` datetime DEFAULT NULL AFTER `quantity`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;

-- 18/04/2011 - Add product_options table
CREATE TABLE `product_options` (
	`id` int NOT NULL AUTO_INCREMENT,
	`product_id` int NOT NULL,
	`key` varchar(255) NOT NULL,
	`value` varchar(255) NOT NULL,
	`status` varchar(25) NOT NULL,
	`created` datetime NOT NULL,
	`modified` datetime,
	`deleted` datetime,
	PRIMARY KEY (`id`)
);

-- 18/04/2011 - Add table for Sales Order Notes
CREATE TABLE `sales_order_notes` (
	`id` int NOT NULL AUTO_INCREMENT,
	`sales_order_id` int NOT NULL,
	`user_id` int NOT NULL,
	`text` text NOT NULL,
	`created` datetime NOT NULL,
	`modified` datetime,
	`deleted` datetime,
	PRIMARY KEY (`id`)
);
ALTER TABLE `sales_order_notes` CHANGE COLUMN `user_id` `user_id` int(11), ADD COLUMN `is_system` tinyint(1) NOT NULL DEFAULT '0' AFTER `user_id`, CHANGE COLUMN `text` `text` text NOT NULL AFTER `is_system`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `text`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;

-- 13/04/2011 14:10 - Add telephone field to address table
ALTER TABLE `addresses` ADD COLUMN `telephone` varchar(20) AFTER `country_id`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `telephone`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;