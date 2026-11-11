-- Blog yazıları
CREATE TABLE posts (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title      VARCHAR(200) NOT NULL,
    slug       VARCHAR(220) NOT NULL UNIQUE,
    body       TEXT NOT NULL,
    status     ENUM('draft','published') NOT NULL DEFAULT 'published',
    views      INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    INDEX (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
