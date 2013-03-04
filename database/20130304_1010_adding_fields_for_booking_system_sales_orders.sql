ALTER TABLE `sales_orders` ADD COLUMN `party_details` longblob AFTER `type`, ADD COLUMN `deposit_date` datetime AFTER `party_details`, ADD COLUMN `deposit_amount` decimal(10,4) AFTER `deposit_date`, ADD COLUMN `deposit_status` varchar(255) AFTER `deposit_amount`, ADD COLUMN `remaining_date` datetime AFTER `deposit_status`, ADD COLUMN `remaining_amount` decimal(10,4) AFTER `remaining_date`, ADD COLUMN `remaining_status` varchar(255) AFTER `remaining_amount`, ADD COLUMN `damages_date` datetime AFTER `remaining_status`, ADD COLUMN `damages_amount` decimal(10,4) AFTER `damages_date`, ADD COLUMN `damages_status` varchar(255) AFTER `damages_amount`;