-- 27/05/11 - Add promotion code link to sales order table
ALTER TABLE `sales_orders` ADD COLUMN `promotion_code_id` int AFTER `delivery_option_id`, ADD COLUMN `discount_amount` decimal(10,4) AFTER `promotion_code_id`, CHANGE COLUMN `status` `status` varchar(25) NOT NULL AFTER `discount_amount`, CHANGE COLUMN `order_total` `order_total` decimal(10,2) NOT NULL AFTER `status`, CHANGE COLUMN `ip_address` `ip_address` varchar(15) NOT NULL AFTER `order_total`, CHANGE COLUMN `barclays_transaction_status` `barclays_transaction_status` varchar(255) DEFAULT NULL AFTER `ip_address`, CHANGE COLUMN `crm_order_number` `crm_order_number` varchar(50) DEFAULT NULL AFTER `barclays_transaction_status`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `crm_order_number`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;
ALTER TABLE `sales_orders` ADD COLUMN `promotion_code_code` varchar(50) AFTER `promotion_code_id`, CHANGE COLUMN `discount_amount` `discount_amount` decimal(10,0) DEFAULT NULL AFTER `promotion_code_code`, CHANGE COLUMN `status` `status` varchar(25) NOT NULL AFTER `discount_amount`, CHANGE COLUMN `order_total` `order_total` decimal(10,2) NOT NULL AFTER `status`, CHANGE COLUMN `ip_address` `ip_address` varchar(15) NOT NULL AFTER `order_total`, CHANGE COLUMN `barclays_transaction_status` `barclays_transaction_status` varchar(255) DEFAULT NULL AFTER `ip_address`, CHANGE COLUMN `crm_order_number` `crm_order_number` varchar(50) DEFAULT NULL AFTER `barclays_transaction_status`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `crm_order_number`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;

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