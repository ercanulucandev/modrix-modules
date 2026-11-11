CREATE TABLE newsletter_subscribers (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email            VARCHAR(190) NOT NULL UNIQUE,
    status           ENUM('pending','active','unsubscribed') NOT NULL DEFAULT 'pending',
    token            CHAR(32) NOT NULL,              -- onay ve abonelikten çıkma bağlantısı
    ip               VARCHAR(45) NULL,
    source           VARCHAR(255) NULL,              -- abone olunan sayfa
    created_at       DATETIME NOT NULL,
    confirmed_at     DATETIME NULL,
    unsubscribed_at  DATETIME NULL,
    INDEX (status),
    INDEX (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
