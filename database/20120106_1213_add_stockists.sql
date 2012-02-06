-- Create a new base table for stockists
CREATE TABLE `stockists` (
	`id` int NOT NULL AUTO_INCREMENT,
	`created` datetime NOT NULL,
	`modified` datetime,
	`deleted` datetime,
	PRIMARY KEY (`id`)
);
-- Add fields for name and link to address table
ALTER TABLE `stockists` ADD COLUMN `name` varchar(254) NOT NULL AFTER `id`, ADD COLUMN `address_id` int AFTER `name`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `address_id`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;
-- Add SEO fields and status
ALTER TABLE `stockists` ADD COLUMN `slug` varchar(254) AFTER `name`, CHANGE COLUMN `address_id` `address_id` int(11) DEFAULT NULL AFTER `slug`, ADD COLUMN `description` text AFTER `address_id`, ADD COLUMN `meta_description` varchar(160) AFTER `description`, ADD COLUMN `meta_keywords` varchar(254) AFTER `meta_description`, ADD COLUMN `status` varchar(20) NOT NULL AFTER `meta_keywords`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `status`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;
-- Add field for website
ALTER TABLE `munchy_seeds_dev`.`stockists` ADD COLUMN `website` varchar(254) AFTER `description`, CHANGE COLUMN `meta_description` `meta_description` varchar(160) DEFAULT NULL AFTER `website`, CHANGE COLUMN `meta_keywords` `meta_keywords` varchar(254) DEFAULT NULL AFTER `meta_description`, CHANGE COLUMN `status` `status` varchar(20) NOT NULL AFTER `meta_keywords`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `status`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;
-- Adapt addresses table to accept non-customer addresses too
ALTER TABLE `addresses` CHANGE COLUMN `customer_id` `customer_id` int(11), CHANGE COLUMN `is_delivery` `is_delivery` tinyint(1);