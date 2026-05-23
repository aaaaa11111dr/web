CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(64) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `settings` (
  `name` VARCHAR(80) NOT NULL,
  `value` MEDIUMTEXT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `search_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `keyword` VARCHAR(200) NOT NULL,
  `ip` VARCHAR(64) NOT NULL,
  `user_agent` VARCHAR(255) NULL,
  `result_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` VARCHAR(24) NOT NULL DEFAULT 'ok',
  `message` VARCHAR(255) NULL,
  `proxy_ip` VARCHAR(255) NULL COMMENT '使用的代理IP',
  `search_source` VARCHAR(50) NULL COMMENT '搜索引擎来源: searxng/duckduckgo/google/startpage',
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `page_proxy_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `target_url` VARCHAR(800) NOT NULL,
  `ip` VARCHAR(64) NOT NULL,
  `status_code` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ad_pool` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pool_key` VARCHAR(40) NOT NULL COMMENT 'home|blocked|results_top|results_middle|results_bottom|redirect|resource',
  `title` VARCHAR(120) NULL,
  `image_url` VARCHAR(500) NOT NULL DEFAULT '',
  `link_url` VARCHAR(500) NOT NULL DEFAULT '',
  `ad_type` VARCHAR(20) NOT NULL DEFAULT 'image',
  `embed_code` TEXT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `enabled` TINYINT NOT NULL DEFAULT 1,
  `views` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pool_key` (`pool_key`),
  KEY `idx_enabled` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
