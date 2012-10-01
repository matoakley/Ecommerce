-- CREATE REWARDS POINTS IN CUSTOMERS
ALTER TABLE `customers` ADD COLUMN `reward_points` int(11);
-- CREATE REWARDS POINTS IN sales orders
ALTER TABLE `sales_orders` ADD COLUMN `reward_points` int(11);