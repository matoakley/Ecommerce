-- ----------------------------
--  Table structure for `forum_banned_words`
-- ----------------------------
DROP TABLE IF EXISTS `forum_banned_words`;
CREATE TABLE `forum_banned_words` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;