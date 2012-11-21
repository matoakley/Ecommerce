ALTER TABLE `users` ADD COLUMN `verification` tinyint(1) NOT NULL;
ALTER TABLE `users` ADD COLUMN `email_verification_id` varchar(255);