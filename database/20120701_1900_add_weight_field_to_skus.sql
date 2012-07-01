-- Add weight field to SKUs table
ALTER TABLE `skus` ADD COLUMN `weight` float(10,4) AFTER `deleted`;