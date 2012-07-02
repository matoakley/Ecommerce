-- Create VAT code table
CREATE TABLE `vat_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `value` decimal(10,4) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Add link to products
ALTER TABLE `products` ADD COLUMN `vat_code_id` int AFTER `stock`;