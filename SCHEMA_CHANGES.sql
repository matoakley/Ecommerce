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