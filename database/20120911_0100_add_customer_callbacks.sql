-- Create a table for customer callbacks
CREATE TABLE `customer_callbacks` (
	`id` int NOT NULL AUTO_INCREMENT,
	`customer_id` int NOT NULL,
	`user_id` int,
	`date` datetime,
	`notes` text,
	`created` datetime NOT NULL,
	`modified` datetime,
	`deleted` datetime,
	PRIMARY KEY (`id`)
);
ALTER TABLE `customer_callbacks` ADD COLUMN `complete` tinyint(1) NOT NULL AFTER `notes`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `complete`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;