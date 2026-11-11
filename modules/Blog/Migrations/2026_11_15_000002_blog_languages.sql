-- Çoklu dil: her yazının dili ve çeviri grubu
ALTER TABLE blog_posts
    ADD lang CHAR(2) NOT NULL DEFAULT 'tr' AFTER id,
    ADD translation_group INT UNSIGNED NULL AFTER lang,
    DROP INDEX slug,
    ADD UNIQUE blog_posts_lang_slug (lang, slug),
    ADD INDEX blog_posts_translation (translation_group);
