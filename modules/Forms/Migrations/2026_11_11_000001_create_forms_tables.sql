-- Formlar ve gelen mesajlar
CREATE TABLE forms (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    slug       VARCHAR(60) NOT NULL UNIQUE,          -- sayfalarda: [form iletisim]
    fields     MEDIUMTEXT NOT NULL,                  -- JSON: [{ name, label, type, required, placeholder, options, width, help }]
    settings   TEXT NOT NULL,                        -- JSON: { email_to, subject, success_message, button_text, store }
    created_at DATETIME NULL,
    updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE form_entries (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    form_id    INT UNSIGNED NOT NULL,
    data       MEDIUMTEXT NOT NULL,                  -- JSON: { alan: { label, value } }
    ip         VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    page_url   VARCHAR(500) NULL,                    -- formun gönderildiği sayfa
    is_read    TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    INDEX (form_id, created_at),
    INDEX (form_id, ip, created_at),
    FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Hazır iletişim formu
INSERT INTO forms (name, slug, fields, settings, created_at, updated_at) VALUES (
    'İletişim formu', 'iletisim',
    '[{"name":"ad","label":"Adınız soyadınız","type":"text","required":true,"placeholder":"","options":[],"width":"half","help":""},{"name":"eposta","label":"E-posta","type":"email","required":true,"placeholder":"ornek@eposta.com","options":[],"width":"half","help":""},{"name":"telefon","label":"Telefon","type":"tel","required":false,"placeholder":"","options":[],"width":"half","help":""},{"name":"konu","label":"Konu","type":"select","required":false,"placeholder":"","options":["Bilgi almak istiyorum","Teklif istiyorum","Destek","Diğer"],"width":"half","help":""},{"name":"mesaj","label":"Mesajınız","type":"textarea","required":true,"placeholder":"","options":[],"width":"full","help":""},{"name":"onay","label":"Kişisel verilerimin işlenmesini kabul ediyorum.","type":"checkbox","required":true,"placeholder":"","options":[],"width":"full","help":""}]',
    '{"email_to":"","subject":"Yeni iletişim mesajı","success_message":"Teşekkürler! Mesajınız bize ulaştı, en kısa sürede dönüş yapacağız.","button_text":"Gönder","store":true}',
    NOW(), NOW()
);
