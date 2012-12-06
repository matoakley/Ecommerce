CREATE TABLE `delivery_options_rules` (
	`id` integer NOT NULL AUTO_INCREMENT,
	`name` varchar(255),
	`description` varchar(255),
	`min_basket` float,
	`min_qty` integer,
	PRIMARY KEY (`id`)
);

ALTER TABLE `delivery_options_rules` ADD COLUMN `status` varchar(255), ADD COLUMN `created` datetime AFTER `status`, ADD COLUMN `modified` datetime AFTER `created`, ADD COLUMN `deleted` datetime AFTER `modified`;
ALTER TABLE `delivery_options_rules` CHANGE COLUMN `min_qty` `delivery_option_id` int(11) DEFAULT NULL;

ALTER TABLE `delivery_options` ADD COLUMN `default` tinyint(1) DEFAULT '0';