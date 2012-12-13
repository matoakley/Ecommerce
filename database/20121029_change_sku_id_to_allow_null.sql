-- change sku_id to allow null in sales order items
ALTER TABLE `sales_order_items` CHANGE COLUMN `sku_id` `sku_id` int(11);