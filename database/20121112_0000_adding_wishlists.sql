CREATE TABLE `wish_lists` (
    `id` integer NOT NULL AUTO_INCREMENT,
	`product_id` integer,
	`user_id` integer,
	`public_identifier` varchar(255),
	`deleted` datetime,
     PRIMARY KEY (`id`)
);


ALTER TABLE `users` ADD COLUMN `wish_list_id` varchar(255);

