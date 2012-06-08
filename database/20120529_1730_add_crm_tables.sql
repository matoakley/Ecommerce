-- Add company field.
ALTER TABLE `customers` ADD COLUMN `company` varchar(255) AFTER `lastname`, CHANGE COLUMN `email` `email` varchar(255) NOT NULL AFTER `company`, CHANGE COLUMN `referred_by` `referred_by` varchar(255) DEFAULT NULL AFTER `email`, CHANGE COLUMN `created` `created` datetime NOT NULL AFTER `referred_by`, CHANGE COLUMN `modified` `modified` datetime DEFAULT NULL AFTER `created`, CHANGE COLUMN `deleted` `deleted` datetime DEFAULT NULL AFTER `modified`, CHANGE COLUMN `default_billing_address_id` `default_billing_address_id` int(11) DEFAULT NULL AFTER `deleted`, CHANGE COLUMN `default_shipping_address_id` `default_shipping_address_id` int(11) DEFAULT NULL AFTER `default_billing_address_id`;

ALTER TABLE `paddy_and_scotts`.`customers` ADD COLUMN `status` varchar(20) NOT NULL DEFAULT 'active' AFTER `default_shipping_address_id`;

-- Lots of CRM tables

-- ----------------------------
--  Table structure for `customer_types`
-- ----------------------------
CREATE TABLE `customer_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

SET FOREIGN_KEY_CHECKS = 1;


SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `customer_customer_type`
-- ----------------------------
CREATE TABLE `customer_types_customers` (
  `customer_id` int(11) NOT NULL,
  `customer_type_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------
--  Table structure for `customer_communications`
-- ----------------------------
CREATE TABLE `customer_communications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  `text` text,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;