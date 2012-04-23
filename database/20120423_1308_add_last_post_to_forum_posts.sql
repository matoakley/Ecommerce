-- Adds a field to record the timestamp of last reply
ALTER TABLE `preowned_cycles_dev`.`forum_posts` ADD COLUMN `last_post` datetime AFTER `text`, CHANGE COLUMN `in_response_to` `in_response_to` int(11) DEFAULT NULL AFTER `last_post`, CHANGE COLUMN `status` `status` varchar(20) DEFAULT NULL AFTER `in_response_to`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `status`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;