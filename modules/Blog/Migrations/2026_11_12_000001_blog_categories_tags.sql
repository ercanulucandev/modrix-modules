-- Blog 2.0: kategoriler, etiketler, öne çıkan görsel, özet, SEO açıklaması, ileri tarihli yayın, yazar
CREATE TABLE blog_categories (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(80) NOT NULL,
    slug        VARCHAR(90) NOT NULL UNIQUE,
    description VARCHAR(300) NULL,
    sort_order  INT UNSIGNED NOT NULL DEFAULT 0,
    created_at  DATETIME NULL,
    updated_at  DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE blog_tags (
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(40) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE blog_post_tag (
    post_id INT UNSIGNED NOT NULL,
    tag_id  INT UNSIGNED NOT NULL,
    PRIMARY KEY (post_id, tag_id),
    INDEX (tag_id),
    FOREIGN KEY (tag_id) REFERENCES blog_tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE blog_posts
    ADD category_id      INT UNSIGNED NULL AFTER id,
    ADD author_id        INT UNSIGNED NULL AFTER category_id,
    ADD excerpt          VARCHAR(500) NULL AFTER body,
    ADD image            VARCHAR(255) NULL AFTER excerpt,
    ADD meta_description VARCHAR(300) NULL AFTER image,
    ADD published_at     DATETIME NULL AFTER status,
    ADD INDEX blog_posts_published (status, published_at),
    ADD INDEX blog_posts_category (category_id);

-- Mevcut yazılar oluşturulma tarihinde yayınlanmış sayılır
UPDATE blog_posts SET published_at = created_at WHERE published_at IS NULL;
