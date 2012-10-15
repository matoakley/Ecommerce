-- Change how basket stores usage of reward points
ALTER TABLE `baskets` CHANGE COLUMN `using_reward_points` `reward_points` int(11) DEFAULT NULL;
ALTER TABLE `baskets` CHANGE COLUMN `reward_points` `use_reward_points` tinyint(1) DEFAULT NULL;
ALTER TABLE `sales_orders` CHANGE COLUMN `reward_points` `reward_points_used` int(11) DEFAULT NULL, ADD COLUMN `reward_points_used_value` decimal(10,4) AFTER `reward_points_used`, ADD COLUMN `reward_points_earned` int(11) AFTER `reward_points_used_value`;
ALTER TABLE `baskets` DROP COLUMN `referral_code`;;
ALTER TABLE `sales_orders` ADD COLUMN `reward_points_processed` tinyint(1);
ALTER TABLE `sales_orders` ADD COLUMN `customer_referral_code` varchar(255) AFTER `reward_points_processed`;