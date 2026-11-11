ALTER TABLE blog_posts DROP INDEX blog_posts_lang_slug, DROP INDEX blog_posts_translation, ADD UNIQUE slug (slug), DROP lang, DROP translation_group;
