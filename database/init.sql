-- 初始化脚本：设置管理员账户和默认设置
-- 首先使用数据库
USE wow_search;

-- 插入默认管理员账户（用户名：admin，密码：Admin1234）
-- 密码哈希值是 password_hash('Admin1234', PASSWORD_DEFAULT) 的结果
INSERT INTO `admins` (`id`, `username`, `password_hash`, `created_at`, `updated_at`) 
VALUES (1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- 插入默认设置
INSERT INTO `settings` (`name`, `value`, `updated_at`) VALUES
('site_name', 'Search', NOW()),
('site_notice', '仅限内部人员使用。', NOW()),
('home_title', '{site_name}', NOW()),
('home_description', '内部搜索系统', NOW()),
('home_keywords', '搜索,内部搜索', NOW()),
('result_title', '{q} - {site_name}', NOW()),
('result_description', '{q} 的搜索结果', NOW()),
('result_keywords', '{q},{site_name}', NOW()),
('front_access_enabled', '0', NOW()),
('front_access_password_hash', '', NOW()),
('ip_allowlist_enabled', '0', NOW()),
('ip_allowlist', '', NOW()),
('search_enabled', '1', NOW()),
('google_domain', 'https://www.google.com', NOW()),
('proxy_enabled', '0', NOW()),
('proxy_type', 'http', NOW()),
('proxy_ports', '', NOW()),
('proxy_rotate_seconds', '180', NOW()),
('timeout', '12', NOW()),
('rate_limit_seconds', '2', NOW()),
('page_proxy_enabled', '0', NOW()),
('cache_seconds', '0', NOW()),
('blocked_keywords', '', NOW()),
('pager_countdown_seconds', '20', NOW()),
('redirect_countdown_seconds', '5', NOW()),
('footer_notice', '请用于正规用途', NOW())
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = NOW();
