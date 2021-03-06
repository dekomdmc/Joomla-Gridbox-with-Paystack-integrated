ALTER TABLE `#__gridbox_store_order_customer_info` ADD `cart_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__gridbox_store_order_customer_info` ADD `order_list` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__gridbox_store_orders_payment` ADD `cart_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__gridbox_store_orders_shipping` ADD `cart_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__gridbox_store_cart` ADD `country` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__gridbox_store_cart` ADD `region` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__gridbox_store_customer_info` CHANGE `order_list` `order_list` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `#__gridbox_store_orders` ADD `tax_mode` varchar(255) NOT NULL DEFAULT 'excl';
ALTER TABLE `#__gridbox_store_orders_shipping` ADD `tax_title` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__gridbox_store_orders_shipping` ADD `tax_rate` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__gridbox_store_order_products` ADD `tax` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__gridbox_store_order_products` ADD `tax_title` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__gridbox_store_order_products` ADD `tax_rate` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__gridbox_store_order_products` ADD `net_price` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__gridbox_store_product_data` ADD `extra_options` text NOT NULL;