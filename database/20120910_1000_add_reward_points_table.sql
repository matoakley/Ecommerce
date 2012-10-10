-- CREATE REWARD POINTS TABLE TO HOLD VARIABLES

CREATE TABLE `reward_points` (
	`id` int NOT NULL AUTO_INCREMENT,
	`points_per` int,
	`pounds` int,
	`created` datetime,
	`modified` datetime,
	`deleted` datetime,
	PRIMARY KEY (`id`)
);