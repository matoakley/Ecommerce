ALTER TABLE `skus` ADD COLUMN `thumbnail_id` varchar(50);
UPDATE skus SET thumbnail_id = 'NULL';