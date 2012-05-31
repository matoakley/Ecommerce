-- Table is pretty much a clone of categories
CREATE TABLE `blog_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text,
  `order` int(11) DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  `meta_description` varchar(160) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- HABTM table
CREATE TABLE `blog_categories_blog_posts` (
  `blog_category_id` int(11) NOT NULL,
  `blog_post_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;