-- Mağaza: ürünler, varyantlar, siparişler. Kategoriler collection_items ('shop_categories') içinde.
CREATE TABLE shop_products (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id      INT UNSIGNED NULL,
    name             VARCHAR(200) NOT NULL,
    slug             VARCHAR(190) NOT NULL UNIQUE,
    sku              VARCHAR(64) NULL,
    summary          VARCHAR(500) NULL,
    description      MEDIUMTEXT NULL,
    price            DECIMAL(12,2) NOT NULL DEFAULT 0,
    compare_price    DECIMAL(12,2) NULL,              -- üstü çizili eski fiyat
    stock            INT NULL,                        -- NULL: stok takibi yok (sınırsız)
    images           TEXT NULL,                       -- JSON: medya yolları
    status           ENUM('published','draft') NOT NULL DEFAULT 'draft',
    featured         TINYINT(1) NOT NULL DEFAULT 0,
    meta_description VARCHAR(300) NULL,
    sort_order       INT NOT NULL DEFAULT 0,
    created_at       DATETIME NULL,
    updated_at       DATETIME NULL,
    INDEX (status, category_id),
    INDEX (featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE shop_variants (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id  INT UNSIGNED NOT NULL,
    name        VARCHAR(100) NOT NULL,                -- "Kırmızı / M"
    sku         VARCHAR(64) NULL,
    price       DECIMAL(12,2) NULL,                   -- NULL: ürünün fiyatı
    stock       INT NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    INDEX (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE shop_orders (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    number           VARCHAR(20) NULL UNIQUE,         -- müşterinin gördüğü sipariş no
    token            CHAR(32) NOT NULL UNIQUE,        -- /order/{token} (müşterinin sipariş sayfası)
    status           ENUM('pending','processing','shipped','completed','cancelled','refunded') NOT NULL DEFAULT 'pending',
    payment_method   VARCHAR(20) NOT NULL,            -- bank, cod, iyzico, paytr
    payment_status   ENUM('unpaid','paid','failed','refunded') NOT NULL DEFAULT 'unpaid',
    payment_ref      VARCHAR(100) NULL,               -- ödeme kuruluşunun işlem no
    customer_name    VARCHAR(100) NOT NULL,
    email            VARCHAR(190) NOT NULL,
    phone            VARCHAR(30) NOT NULL,
    address          TEXT NOT NULL,                   -- JSON: city, district, line, zip
    billing          TEXT NULL,                       -- JSON: company, tax_office, tax_number
    note             VARCHAR(1000) NULL,
    admin_note       VARCHAR(1000) NULL,
    cargo_company    VARCHAR(60) NULL,
    tracking_number  VARCHAR(60) NULL,
    subtotal         DECIMAL(12,2) NOT NULL,
    shipping_fee     DECIMAL(12,2) NOT NULL DEFAULT 0,
    payment_fee      DECIMAL(12,2) NOT NULL DEFAULT 0, -- kapıda ödeme ücreti
    total            DECIMAL(12,2) NOT NULL,
    currency         CHAR(3) NOT NULL DEFAULT 'TRY',
    ip               VARCHAR(45) NULL,
    paid_at          DATETIME NULL,
    created_at       DATETIME NOT NULL,
    updated_at       DATETIME NULL,
    INDEX (status, created_at),
    INDEX (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE shop_order_items (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id    INT UNSIGNED NOT NULL,
    product_id  INT UNSIGNED NULL,
    variant_id  INT UNSIGNED NULL,
    name        VARCHAR(300) NOT NULL,                -- sipariş anındaki ad (ürün silinse de kalır)
    sku         VARCHAR(64) NULL,
    price       DECIMAL(12,2) NOT NULL,
    quantity    INT UNSIGNED NOT NULL,
    total       DECIMAL(12,2) NOT NULL,
    INDEX (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
