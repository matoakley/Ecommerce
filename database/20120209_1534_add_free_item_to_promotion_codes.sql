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