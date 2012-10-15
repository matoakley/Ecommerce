CREATE TABLE `product_reviews` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` int UNSIGNED NOT NULL,
	`user_id` int UNSIGNED,
	`rating` tinyint(1),
	`review` text,
	`created` datetime NOT NULL,
	`modified` datetime,
	`deleted` datetime,
	PRIMARY KEY (`id`)
) ENGINE=`InnoDB`;