-- Randevular. Hizmetler ('booking_services') ve personel ('booking_staff') collection_items içinde.
CREATE TABLE booking_appointments (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    token        CHAR(32) NOT NULL UNIQUE,          -- /booking/{token}: müşterinin randevu sayfası
    service_id   INT UNSIGNED NOT NULL,
    service_name VARCHAR(150) NOT NULL,             -- randevu anındaki ad (hizmet silinse de kalır)
    staff_id     INT UNSIGNED NULL,
    staff_name   VARCHAR(100) NULL,
    starts_at    DATETIME NOT NULL,
    ends_at      DATETIME NOT NULL,
    status       ENUM('pending','confirmed','completed','cancelled','no_show') NOT NULL DEFAULT 'confirmed',
    price        DECIMAL(12,2) NULL,
    name         VARCHAR(100) NOT NULL,
    email        VARCHAR(190) NOT NULL,
    phone        VARCHAR(30) NOT NULL,
    note         VARCHAR(1000) NULL,
    admin_note   VARCHAR(1000) NULL,
    ip           VARCHAR(45) NULL,
    created_at   DATETIME NOT NULL,
    updated_at   DATETIME NULL,
    INDEX (starts_at, status),
    INDEX (staff_id, starts_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Örnek hizmet (düzenleyin/silin)
INSERT INTO collection_items (collection, title, data, visible, sort_order, created_at, updated_at)
VALUES ('booking_services', 'Ön görüşme', '{"name":"Ön görüşme","duration":"30","price":"","description":"İhtiyacınızı konuştuğumuz ücretsiz tanışma görüşmesi.","image":""}', 1, 1, NOW(), NOW());
