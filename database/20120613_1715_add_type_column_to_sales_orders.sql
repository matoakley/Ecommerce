-- Add type to sales orders
ALTER TABLE `sales_orders` ADD COLUMN `type` varchar(25) NOT NULL AFTER `deleted`;
ALTER TABLE `sales_orders` CHANGE COLUMN `delivery_option_id` `delivery_option_id` int(11);

-- Add archived timestamp and name to addresses
ALTER TABLE `addresses` ADD COLUMN `archived` datetime AFTER `telephone`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `archived`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;
ALTER TABLE `addresses` ADD COLUMN `name` varchar(254) AFTER `archived`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `name`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;