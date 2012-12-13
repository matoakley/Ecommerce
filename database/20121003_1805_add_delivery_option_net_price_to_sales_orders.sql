ALTER TABLE `sales_orders` ADD COLUMN `delivery_option_net_price` decimal(10,4) AFTER `delivery_option_name`;
-- Sales order total should be allowed to be NULL as this can be set later
ALTER TABLE `sales_orders` CHANGE COLUMN `order_total` `order_total` decimal(10,4);