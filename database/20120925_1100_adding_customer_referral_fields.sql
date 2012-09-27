-- adding customer referral fields

ALTER TABLE `customers` ADD COLUMN `customer_referral_code` varchar(16) AFTER `reward_points`;

ALTER TABLE `baskets` ADD COLUMN `customer_referral_code` varchar(16) AFTER `using_reward_points`;

ALTER TABLE `baskets` ADD COLUMN `referral_code` integer(11) AFTER `referral_code`;