/* Create 'Discount_amount' field in the sales_orders_items table.*/
ALTER TABLE `munchy_seeds_dev`.`sales_order_items` ADD COLUMN `discount_amount` decimal(10,2) NOT NULL AFTER `net_total_price`;