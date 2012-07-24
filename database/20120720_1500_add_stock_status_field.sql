ALTER TABLE `skus` ADD COLUMN `stock_status` varchar(50);
UPDATE skus SET stock_status = 'in_stock';