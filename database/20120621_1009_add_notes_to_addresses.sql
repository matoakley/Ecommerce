-- Add notes field to addresses
ALTER TABLE `addresses` DROP COLUMN `notes`, ADD COLUMN `notes` varchar(255) AFTER `deleted`;

-- Add line 3 to addresses
ALTER TABLE `addresses` DROP COLUMN `line_3`, ADD COLUMN `line_3` varchar(255) AFTER `notes`;

-- Add parent/child relationship to customers
ALTER TABLE `customers` ADD COLUMN `customer_id` int AFTER `price_tier_id`;

-- Add telephone and position field for CRM contacts
ALTER TABLE `customers` ADD COLUMN `telephone` varchar(50) AFTER `customer_id`, ADD COLUMN `position` varchar(50) AFTER `telephone`;

-- Add customer ref as a permanent field
ALTER TABLE `customers` ADD COLUMN `account_ref` varchar(50) AFTER `position`;

-- Add ref field to sales_orders and field to track admin user that enters order
ALTER TABLE `sales_orders` ADD COLUMN `ref` varchar(50) AFTER `type`;
ALTER TABLE `sales_orders` ADD COLUMN `user_id` int AFTER `ref`;

-- Store NET prices against Sales Order Items
ALTER TABLE `sales_order_items` CHANGE COLUMN `unit_price` `unit_price` decimal(10,4) NOT NULL, CHANGE COLUMN `total_price` `total_price` decimal(10,4) NOT NULL, CHANGE COLUMN `vat_rate` `vat_rate` decimal(10,4) NOT NULL, ADD COLUMN `net_unit_price` decimal(10,4) AFTER `deleted`, ADD COLUMN `net_total_price` decimal(10,4) AFTER `net_unit_price`;

-- Record order subtotal and vat total. Also, store order total with 4 decimal places.
ALTER TABLE `sales_orders` CHANGE COLUMN `order_total` `order_total` decimal(10,4) NOT NULL, ADD COLUMN `order_vat` decimal(10,4) AFTER `user_id`, ADD COLUMN `order_subtotal` decimal(10,4) AFTER `order_vat`;