-- Create a table to record views of forum posts
CREATE TABLE `forum_post_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `forum_post_id` int(11) NOT NULL,
  `ip_address` varchar(20) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;