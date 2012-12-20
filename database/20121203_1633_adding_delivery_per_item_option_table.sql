-- adding field to brands

ALTER TABLE `brands` ADD COLUMN `delivery_per_item` tinyint(1) DEFAULT '0';