-- Create 'Discount_amount' field in the sales_orders_items table
ALTER TABLE `sales_order_items` ADD COLUMN `discount_amount` decimal(10,2);