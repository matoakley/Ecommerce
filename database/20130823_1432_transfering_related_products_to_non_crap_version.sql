CREATE TABLE `related_products_products` ( `related_product_id` int, `product_id` int );

insert into related_products_products
select related_id, product_id
from related_products
join products
on products.id = product_id
where products.deleted IS NULL
and related_products.deleted IS NULL
and product_id IS NOT NULL
and related_id IS NOT NULL;

-- drop table related_products