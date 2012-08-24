-- Record date on which invoice was generated
ALTER TABLE `sales_orders` ADD COLUMN `invoiced_on` datetime;