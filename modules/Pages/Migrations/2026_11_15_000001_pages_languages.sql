-- Çoklu dil: her sayfanın dili ve çeviri grubu (aynı sayfanın diğer dillerdeki halleri)
ALTER TABLE pages
    ADD lang CHAR(2) NOT NULL DEFAULT 'tr' AFTER id,
    ADD translation_group INT UNSIGNED NULL AFTER lang,
    DROP INDEX path,
    ADD UNIQUE pages_lang_path (lang, path),
    ADD INDEX pages_translation (translation_group);
