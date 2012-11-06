CREATE TABLE `comments` (
	`id` int UNSIGNED NOT NULL AUTO_INCREMENT,
	`object` varchar(255),
	`object_id` int UNSIGNED,
	`user_id` int,
	`comment` text,
	`up_vote` tinyint(1),
	`down_vote` tinyint(1),
	`created` datetime NOT NULL,
	`modified` datetime,
	`deleted` datetime,
	PRIMARY KEY (`id`)
) ENGINE=`InnoDB`;