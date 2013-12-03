ALTER TABLE `roles` ADD COLUMN `created` datetime;
ALTER TABLE `roles` ADD COLUMN `modified` datetime;
ALTER TABLE `roles` ADD COLUMN `deleted` datetime;
Alter TABLE `events` ADD COLUMN `event_type` varchar(255);