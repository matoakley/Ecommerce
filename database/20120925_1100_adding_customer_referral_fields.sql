-- adding customer referral fields

ALTER TABLE `customers` ADD COLUMN `customer_referral_code` varchar(16) ;

ALTER TABLE `baskets` ADD COLUMN `customer_referral_code` varchar(16) ;

ALTER TABLE `baskets` ADD COLUMN `referral_code` integer(11);