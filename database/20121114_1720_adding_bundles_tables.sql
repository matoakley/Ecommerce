
DROP TABLE IF EXISTS `bundles_skus`;
CREATE TABLE `bundles_skus` (
  `product_id` int(11) DEFAULT NULL,
  `sku_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


ALTER TABLE `products` ADD COLUMN `type` varchar(255) DEFAULT 'product';

