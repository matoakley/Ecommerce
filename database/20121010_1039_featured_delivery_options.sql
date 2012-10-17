-- Add columns to store featured options and to hide options from basket and set all selectable by default
ALTER TABLE `delivery_options` ADD COLUMN `featured` tinyint(1), ADD COLUMN `customer_selectable` tinyint(1);
UPDATE `delivery_options` SET `customer_selectable` = 1;