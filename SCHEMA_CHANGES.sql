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