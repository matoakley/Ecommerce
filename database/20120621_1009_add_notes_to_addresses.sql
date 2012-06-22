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