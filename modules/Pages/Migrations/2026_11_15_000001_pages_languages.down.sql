ALTER TABLE pages DROP INDEX pages_lang_path, DROP INDEX pages_translation, ADD UNIQUE path (path), DROP lang, DROP translation_group;
