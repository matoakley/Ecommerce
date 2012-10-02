-- changing customer type id to allow null
ALTER TABLE `customer_types_customers` CHANGE COLUMN `customer_type_id` `customer_type_id` int(11);