ALTER TABLE `customers` ADD COLUMN `D_O_B` date AFTER `customer_referral_code`;
ALTER TABLE `reviews` ADD COLUMN `popularity` integer AFTER `deleted`;