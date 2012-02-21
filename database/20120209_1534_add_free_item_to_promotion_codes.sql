-- Create a table to allow multiple conditions/rewards for promotion codes
CREATE TABLE `promotion_code_rewards` (
	`id` int NOT NULL AUTO_INCREMENT,
	`reward_type` varchar(20) NOT NULL,
	`promotion_code_id` int NOT NULL,
	`basket_minimum_value` float,
	`discount_type` varchar(30),
	`discount_amount` decimal(10,4),
	`discount_unit` varchar(20),
	`sku_id` int,
	`sku_reward_price` decimal(10,4),
	`created` datetime NOT NULL,
	`modified` datetime,
	`deleted` datetime,
	PRIMARY KEY (`id`)
);
-- Add field to promotion_codes for promotions with no timescale
ALTER TABLE `promotion_codes` ADD COLUMN `run_indefinitely` tinyint(1) AFTER `redeemed`, CHANGE COLUMN `start_date` `start_date` datetime DEFAULT NULL AFTER `run_indefinitely`, CHANGE COLUMN `end_date` `end_date` datetime DEFAULT NULL AFTER `start_date`, CHANGE COLUMN `basket_minimum_value` `basket_minimum_value` decimal(10,0) DEFAULT NULL AFTER `end_date`, CHANGE COLUMN `discount_on` `discount_on` varchar(30) NOT NULL AFTER `basket_minimum_value`, CHANGE COLUMN `discount_amount` `discount_amount` decimal(10,0) DEFAULT NULL AFTER `discount_on`, CHANGE COLUMN `discount_unit` `discount_unit` varchar(20) DEFAULT NULL AFTER `discount_amount`, CHANGE COLUMN `status` `status` varchar(25) DEFAULT NULL AFTER `discount_unit`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `status`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;