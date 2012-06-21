-- Add notes field to addresses
ALTER TABLE `addresses` DROP COLUMN `notes`, ADD COLUMN `notes` varchar(255) AFTER `deleted`;

-- Add line 3 to addresses
ALTER TABLE `addresses` DROP COLUMN `line_3`, ADD COLUMN `line_3` varchar(255) AFTER `notes`;