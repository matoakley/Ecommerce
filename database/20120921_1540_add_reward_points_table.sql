-- adding reward_points table

CREATE TABLE `reward_points_profiles` (
	`id` int AUTO_INCREMENT,
	`name` varchar(255),
	`points_per_pound` int,
	`redeem_value` decimal(10,4),
	`customer_referral` int,
	`new_customer_referral` int,
	`created` datetime NOT NULL,
	`modified` datetime,
	`deleted` datetime,
	`is_default` tinyint(1),
	PRIMARY KEY (`id`)
);
ALTER TABLE `baskets` ADD COLUMN `using_reward_points` decimal(10,4) AFTER `deleted`;