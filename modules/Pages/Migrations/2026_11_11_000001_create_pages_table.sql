-- Sayfalar. Adres (path) üst sayfaların adresleriyle birlikte saklanır: 'kurumsal/ekibimiz'
CREATE TABLE pages (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id        INT UNSIGNED NULL,                      -- üst sayfa (alt sayfa için)
    title            VARCHAR(200) NOT NULL,
    slug             VARCHAR(120) NOT NULL,                  -- adresin son parçası: 'ekibimiz'
    path             VARCHAR(500) NOT NULL,                  -- tam adres: 'kurumsal/ekibimiz'
    body             MEDIUMTEXT NULL,                        -- HTML (Html::sanitize ile temizlenmiş)
    template         VARCHAR(40) NOT NULL DEFAULT 'default', -- sayfa şablonu (modülün ya da temanın)
    image            VARCHAR(255) NULL,                      -- öne çıkan görsel (medya kütüphanesi yolu)
    meta_description VARCHAR(300) NULL,                      -- arama motorları için açıklama
    status           ENUM('draft','published') NOT NULL DEFAULT 'draft',
    sort_order       INT UNSIGNED NOT NULL DEFAULT 0,
    author_id        INT UNSIGNED NULL,
    created_at       DATETIME NULL,
    updated_at       DATETIME NULL,
    UNIQUE (path),
    INDEX (parent_id, sort_order),
    INDEX (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
