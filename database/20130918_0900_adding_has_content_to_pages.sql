ALTER TABLE `pages` ADD COLUMN `has_content` tinyint;
UPDATE `pages` SET `has_content` = 1;