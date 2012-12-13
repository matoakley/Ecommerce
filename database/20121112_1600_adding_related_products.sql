CREATE TABLE `related_products` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`product_id` int(10),
	`related_id` int(10),
	`created` datetime NOT NULL,
	`modified` datetime,
	`deleted` datetime,
	PRIMARY KEY (`id`)
);