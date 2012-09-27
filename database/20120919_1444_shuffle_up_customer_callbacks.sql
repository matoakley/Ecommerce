-- Drop old table as we'll store them in Customer Communications instead
DROP TABLE IF EXISTS `customer_callbacks`;

-- Add new columns to Customer Communications
ALTER TABLE `customer_communications` ADD COLUMN `callback_on` datetime AFTER `date`, ADD COLUMN `callback_user_id` int AFTER `callback_on`, ADD COLUMN `callback_completed_on` datetime AFTER `callback_user_id`, ADD COLUMN `callback_completed_user_id` int AFTER `callback_completed_on`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `callback_completed_user_id`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;