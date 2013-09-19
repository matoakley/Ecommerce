-- ----------------------------
--  Table structure for `event_categories`
-- ----------------------------
DROP TABLE IF EXISTS `event_categories`;
CREATE TABLE `event_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `slug` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `parent_id` int(11) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `meta_description` varchar(160) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=latin1;


-- ----------------------------
--  Table structure for `categories_events`
-- ----------------------------
DROP TABLE IF EXISTS `categories_events`;
CREATE TABLE `categories_events` (
  `event_category_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;