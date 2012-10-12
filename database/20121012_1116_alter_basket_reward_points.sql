-- Change how basket stores usage of reward points
ALTER TABLE `baskets` CHANGE COLUMN `using_reward_points` `reward_points` int(11) DEFAULT NULL;
ALTER TABLE `deluxe_nutrition_dev`.`baskets` CHANGE COLUMN `reward_points` `use_reward_points` tinyint(1) DEFAULT NULL;