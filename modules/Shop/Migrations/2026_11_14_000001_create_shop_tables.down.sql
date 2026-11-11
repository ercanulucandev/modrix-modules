DROP TABLE IF EXISTS shop_order_items;
DROP TABLE IF EXISTS shop_orders;
DROP TABLE IF EXISTS shop_variants;
DROP TABLE IF EXISTS shop_products;
DELETE FROM collection_items WHERE collection = 'shop_categories';
