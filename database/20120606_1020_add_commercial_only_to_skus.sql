-- Commercial only field to SKUs
ALTER TABLE `skus` ADD COLUMN `commercial_only` tinyint(1) NOT NULL DEFAULT '0' AFTER `status`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `commercial_only`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`;