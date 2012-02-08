-- Add latitude and longitude fields to address table
ALTER TABLE `addresses` ADD COLUMN `latitude` decimal(10,6) AFTER `country_id`, ADD COLUMN `longitude` decimal(10,6) AFTER `latitude`, CHANGE COLUMN `telephone` `telephone` varchar(20) DEFAULT NULL AFTER `longitude`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `telephone`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;
-- Create a table to quese geocode requests so as not to hold up user
CREATE TABLE `address_geocode_requests` (
	`id` int NOT NULL AUTO_INCREMENT,
	`address_id` int NOT NULL,
	`status` varchar(20) NOT NULL,
	`response` text,
	`created` datetime NOT NULL,
	`modified` datetime,
	`deleted` datetime,
	PRIMARY KEY (`id`)
);