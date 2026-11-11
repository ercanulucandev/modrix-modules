DROP TABLE IF EXISTS blog_post_tag;
DROP TABLE IF EXISTS blog_tags;
DROP TABLE IF EXISTS blog_categories;
ALTER TABLE blog_posts DROP INDEX blog_posts_published, DROP INDEX blog_posts_category,
    DROP COLUMN category_id, DROP COLUMN author_id, DROP COLUMN excerpt, DROP COLUMN image, DROP COLUMN meta_description, DROP COLUMN published_at;
