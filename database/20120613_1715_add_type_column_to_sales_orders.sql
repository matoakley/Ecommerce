ALTER TABLE `sales_orders` ADD COLUMN `type` varchar(25) NOT NULL AFTER `deleted`;
ALTER TABLE `sales_orders` CHANGE COLUMN `delivery_option_id` `delivery_option_id` int(11);