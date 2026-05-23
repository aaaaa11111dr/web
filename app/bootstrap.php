<?php
declare(strict_types=1);

const APP_NAME = '内部搜索系统';
const APP_ROOT = __DIR__ . '/..';
const DB_FILE = APP_ROOT . '/storage/db.php';
const INSTALL_SQL = APP_ROOT . '/database/install.sql';
const UPLOAD_DIR = APP_ROOT . '/uploads';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('wow_search_system');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    $sessionPath = APP_ROOT . '/storage/sessions';
    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0777, true);
    }
    session_save_path($sessionPath);
    session_start();
}

function e(?string $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function brand_text(?string $value): string
{
    $text = (string)$value;
    $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
    if ($chars === false) $chars = str_split($text);
    $classes = ['brand-c-blue', 'brand-c-red', 'brand-c-yellow', 'brand-c-blue', 'brand-c-green', 'brand-c-red'];
    $html = '<span class="brand-text" role="text">';
    foreach ($chars as $i => $char) $html .= '<span class="' . $classes[$i % count($classes)] . '">' . e($char) . '</span>';
    return $html . '</span>';
}
function now(): string { return date('Y-m-d H:i:s'); }
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16)); return $_SESSION['csrf']; }
function check_csrf(): void { if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['_csrf'] ?? ''))) { http_response_code(419); exit('CSRF 校验失败'); } }
function installed(): bool { return is_file(DB_FILE); }
function redirect(string $url): void { header('Location: ' . $url); exit; }

function db_config(): array
{
    if (!installed()) return [];
    $cfg = include DB_FILE;
    return is_array($cfg) ? $cfg : [];
}

function pdo(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $sqliteFile = APP_ROOT . '/storage/database.sqlite';
        try {
            $pdo = new PDO(
                "sqlite:" . $sqliteFile,
                null,
                null,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            // 启用外键约束
            $pdo->exec("PRAGMA foreign_keys = ON");
        } catch (PDOException $e) {
            die("数据库连接失败: " . $e->getMessage());
        }
    }
    return $pdo;
}

function require_install(): void
{
    if (!installed()) redirect('/install.php');
}

function add_column_if_missing(string $table, string $column, string $definition): void
{
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) return;
    try {
        $stmt = pdo()->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
        $stmt->execute([$table, $column]);
        if ((int)$stmt->fetchColumn() === 0) pdo()->exec("ALTER TABLE `{$table}` ADD COLUMN {$definition}");
    } catch (Throwable $e) {
        try { pdo()->exec("ALTER TABLE `{$table}` ADD COLUMN {$definition}"); } catch (Throwable $ignored) {}
    }
}

function auto_migrate(): void
{
    if (!installed()) return;
    try {
        pdo()->exec("CREATE TABLE IF NOT EXISTS `ad_pool` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `pool_key` VARCHAR(40) NOT NULL,
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        add_column_if_missing('ad_pool', 'ad_type', "`ad_type` VARCHAR(20) NOT NULL DEFAULT 'image' AFTER `link_url`");
        add_column_if_missing('ad_pool', 'embed_code', "`embed_code` TEXT NULL AFTER `ad_type`");
        add_column_if_missing('ad_pool', 'updated_at', "`updated_at` DATETIME NULL AFTER `created_at`");
        // 搜索日志表新增字段
        add_column_if_missing('search_logs', 'proxy_ip', "`proxy_ip` VARCHAR(255) NULL COMMENT '使用的代理IP' AFTER `message`");
        add_column_if_missing('search_logs', 'search_source', "`search_source` VARCHAR(50) NULL COMMENT '搜索引擎来源' AFTER `proxy_ip`");
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    } catch (Throwable $e) {}
}

// Run auto-migration
auto_migrate();

function write_db_config(array $cfg): void
{
    $safe = [
        'host' => $cfg['host'],
        'port' => (int)$cfg['port'],
        'database' => $cfg['database'],
        'username' => $cfg['username'],
        'password' => $cfg['password'],
        'installed_at' => date('c'),
    ];
    $body = "<?php\nreturn " . var_export($safe, true) . ";\n";
    $tmp = DB_FILE . '.tmp';
    if (file_put_contents($tmp, $body, LOCK_EX) === false) throw new RuntimeException('数据库配置写入失败，请检查 storage 权限。');
    rename($tmp, DB_FILE);
}

function run_install_sql(PDO $pdo): void
{
    $sql = file_get_contents(INSTALL_SQL);
    if ($sql === false) throw new RuntimeException('安装 SQL 文件不存在。');
    $pdo->exec($sql);
}

function default_settings(): array
{
    return [
        'site_name' => 'Search',
        'site_notice' => '仅限内部人员使用。',
        'home_title' => '{site_name}',
        'home_description' => '内部搜索系统',
        'home_keywords' => '搜索,内部搜索',
        'result_title' => '{q} - {site_name}',
        'result_description' => '{q} 的搜索结果',
        'result_keywords' => '{q},{site_name}',
        'front_access_enabled' => '0',
        'front_access_password_hash' => '',
        'ip_allowlist_enabled' => '0',
        'ip_allowlist' => '',
        'search_enabled' => '1',
        'google_domain' => 'https://www.google.com',
        'proxy_enabled' => '0',
        'proxy_type' => 'http',
        'proxy_ports' => '',
        'proxy_rotate_seconds' => '180',
        'timeout' => '12',
        'rate_limit_seconds' => '2',
        'page_proxy_enabled' => '0',
        'cache_seconds' => '0',
        'blocked_keywords' => '',
        'pager_countdown_seconds' => '20',
        'redirect_countdown_seconds' => '5',
        'footer_notice' => '请用于正规用途',
        'updated_at' => date('c'),
    ];
}

function get_settings(): array
{
    $settings = default_settings();
    $stmt = pdo()->query('SELECT `name`, `value` FROM `settings`');
    foreach ($stmt->fetchAll() as $row) $settings[$row['name']] = (string)$row['value'];
    return $settings;
}

function set_setting(string $name, string $value): void
{
    $stmt = pdo()->prepare('INSERT OR REPLACE INTO settings (name, value, updated_at) VALUES (?, ?, ?)');
    $stmt->execute([$name, $value, now()]);
}

function save_settings(array $data): void
{
    $pdo = pdo();
    $pdo->beginTransaction();
    try {
        foreach ($data as $k => $v) set_setting((string)$k, (string)$v);
        set_setting('updated_at', date('c'));
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function is_admin(): bool { return !empty($_SESSION['admin_id']); }
function require_admin(): void { if (!is_admin()) redirect('/goolehome.php'); }

function client_ip(): string { return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; }
function user_agent(): string { return mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255); }
function bool_setting(array $s, string $key): bool { return !empty($s[$key]) && $s[$key] !== '0'; }
function int_setting(array $s, string $key, int $min, int $max, int $default): int
{
    $value = isset($s[$key]) ? (int)$s[$key] : $default;
    return max($min, min($max, $value));
}

function render_tdk_template(string $template, array $settings, string $query = ''): string
{
    return trim(strtr($template, [
        '{site_name}' => (string)($settings['site_name'] ?? ''),
        '{q}' => $query,
    ]));
}

// -- Ad Pool --
function get_ad_pool(string $poolKey): array
{
    static $cache = [];
    if (isset($cache[$poolKey])) return $cache[$poolKey];
    try {
        $stmt = pdo()->prepare('SELECT * FROM `ad_pool` WHERE `pool_key` = ? AND `enabled` = 1 ORDER BY `sort_order`');
        $stmt->execute([$poolKey]);
        $ads = $stmt->fetchAll();
    } catch (Throwable $e) { $ads = []; }
    $cache[$poolKey] = $ads;
    return $ads;
}

function get_all_ads(): array
{
    try {
        return pdo()->query('SELECT * FROM `ad_pool` ORDER BY `pool_key`, `sort_order`')->fetchAll();
    } catch (Throwable $e) { return []; }
}

function pick_random_ad(string $poolKey): ?array
{
    $ads = get_ad_pool($poolKey);
    if (!$ads) return null;
    // Weighted random: least-viewed gets higher chance
    $total = 0; $weights = [];
    $minViews = min(array_column($ads, 'views'));
    foreach ($ads as $ad) {
        $w = 1 + ($ad['views'] - $minViews + 1) * 0.5;
        $w = (int)max(1, round(1 / $w * 100));
        $weights[] = $w; $total += $w;
    }
    if ($total <= 0) $total = 1;
    $rand = mt_rand(1, (int)$total); $sum = 0;
    foreach ($ads as $i => $ad) { $sum += $weights[$i]; if ($rand <= $sum) { $picked = $ad; break; } }
    $picked = $picked ?? $ads[0];
    // Increment views
    try { pdo()->prepare('UPDATE `ad_pool` SET `views` = `views` + 1 WHERE `id` = ?')->execute([(int)$picked['id']]); } catch (Throwable $e) {}
    return $picked;
}

function render_ad_pool(string $poolKey, string $label = '广告'): string
{
    $ad = pick_random_ad($poolKey);
    if (!$ad) return '';
    $type = (string)($ad['ad_type'] ?? 'image');
    if ($type === 'code') {
        $html = trim((string)($ad['embed_code'] ?? ''));
        if ($html !== '') $html = '<div class="ad-code-embed">' . $html . '</div>';
    } elseif (!empty($ad['image_url'])) {
        $img = e($ad['image_url']);
        $link = !empty($ad['link_url']) ? safe_external_http_url((string)$ad['link_url']) : '';
        $title = !empty($ad['title']) ? e($ad['title']) : $label;
        $image = '<img src="' . $img . '" alt="' . $title . '" loading="lazy">';
        $html = $link !== ''
            ? '<a class="ad-img-link" href="' . e($link) . '" target="_blank" rel="nofollow noopener">' . $image . '</a>'
            : '<span class="ad-img-link">' . $image . '</span>';
    } else {
        $html = '';
    }
    if ($html === '') return '';
    return '<section class="ad-slot ad-slot-' . e($type) . '" aria-label="' . e($label) . '">' . $html . '</section>';
}

function save_ad(?int $id, string $poolKey, string $title, string $imageUrl, string $linkUrl, int $sortOrder, bool $enabled, string $adType = 'image', string $embedCode = ''): void
{
    $adType = $adType === 'code' ? 'code' : 'image';
    if ($id) {
        pdo()->prepare('UPDATE `ad_pool` SET `pool_key`=?,`title`=?,`image_url`=?,`link_url`=?,`ad_type`=?,`embed_code`=?,`sort_order`=?,`enabled`=?,`updated_at`=? WHERE `id`=?')
            ->execute([$poolKey, $title, $imageUrl, $linkUrl, $adType, $embedCode, $sortOrder, $enabled ? 1 : 0, now(), $id]);
    } else {
        pdo()->prepare('INSERT INTO `ad_pool` (`pool_key`,`title`,`image_url`,`link_url`,`ad_type`,`embed_code`,`sort_order`,`enabled`,`created_at`,`updated_at`) VALUES (?,?,?,?,?,?,?,?,?,?)')
            ->execute([$poolKey, $title, $imageUrl, $linkUrl, $adType, $embedCode, $sortOrder, $enabled ? 1 : 0, now(), now()]);
    }
}

function delete_ad(int $id): void
{
    $stmt = pdo()->prepare('SELECT `image_url` FROM `ad_pool` WHERE `id` = ?');
    $stmt->execute([$id]);
    $ad = $stmt->fetch();
    pdo()->prepare('DELETE FROM `ad_pool` WHERE `id` = ?')->execute([$id]);
    if ($ad && !empty($ad['image_url']) && str_starts_with($ad['image_url'], '/uploads/')) {
        $f = APP_ROOT . $ad['image_url'];
        if (is_file($f)) @unlink($f);
    }
}

function scalar_query(string $sql): int
{
    try { return (int)pdo()->query($sql)->fetchColumn(); } catch (Throwable $e) { return 0; }
}

function get_site_stats(): array
{
    $today = date('Y-m-d');
    $sevenDaysAgo = date('Y-m-d', strtotime('-6 days'));
    
    $stats = [
        'total_searches' => scalar_query('SELECT COUNT(*) FROM search_logs'),
        'today_searches' => scalar_query("SELECT COUNT(*) FROM search_logs WHERE created_at >= '{$today}'"),
        'total_search_ips' => scalar_query('SELECT COUNT(DISTINCT ip) FROM search_logs'),
        'today_search_ips' => scalar_query("SELECT COUNT(DISTINCT ip) FROM search_logs WHERE created_at >= '{$today}'"),
        'total_proxy_views' => scalar_query('SELECT COUNT(*) FROM page_proxy_logs'),
        'today_proxy_views' => scalar_query("SELECT COUNT(*) FROM page_proxy_logs WHERE created_at >= '{$today}'"),
        'top_keywords' => [],
        'top_keywords_today' => [],
        'top_keywords_7d' => [],
        'search_trend' => [],
        'proxy_trend' => [],
        'top_ads' => [],
    ];
    try { $stats['top_keywords'] = pdo()->query('SELECT keyword, COUNT(*) AS total FROM search_logs GROUP BY keyword ORDER BY total DESC LIMIT 10')->fetchAll(); } catch (Throwable $e) {}
    try { $stats['top_keywords_today'] = pdo()->query("SELECT keyword, COUNT(*) AS total FROM search_logs WHERE created_at >= '{$today}' GROUP BY keyword ORDER BY total DESC LIMIT 10")->fetchAll(); } catch (Throwable $e) {}
    try { $stats['top_keywords_7d'] = pdo()->query("SELECT keyword, COUNT(*) AS total FROM search_logs WHERE created_at >= '{$sevenDaysAgo}' GROUP BY keyword ORDER BY total DESC LIMIT 10")->fetchAll(); } catch (Throwable $e) {}
    try { $stats['top_ads'] = pdo()->query('SELECT pool_key, title, views FROM ad_pool ORDER BY views DESC, id DESC LIMIT 10')->fetchAll(); } catch (Throwable $e) {}
    $days = [];
    for ($i = 6; $i >= 0; $i--) $days[date('Y-m-d', strtotime('-' . $i . ' days'))] = 0;
    foreach (['search_trend' => 'search_logs', 'proxy_trend' => 'page_proxy_logs'] as $key => $table) {
        $rows = [];
        try { $rows = pdo()->query("SELECT SUBSTR(created_at, 1, 10) AS day, COUNT(*) AS total FROM {$table} WHERE created_at >= '{$sevenDaysAgo}' GROUP BY day ORDER BY day")->fetchAll(); } catch (Throwable $e) {}
        $trend = $days;
        foreach ($rows as $row) $trend[(string)$row['day']] = (int)$row['total'];
        $stats[$key] = $trend;
    }
    return $stats;
}

function handle_upload(): ?string
{
    $file = $_FILES['file'] ?? $_FILES['ad_image_file'] ?? null;
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) return null;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp'];
    if (!isset($allowed[$ext])) throw new RuntimeException('仅支持 jpg/jpeg/png/gif/webp');
    if ($file['size'] > 8 * 1024 * 1024) throw new RuntimeException('文件不能超过 8MB');
    $mime = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = (string)finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        }
    }
    if ($mime !== '' && $mime !== $allowed[$ext]) throw new RuntimeException('图片格式不正确');
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = UPLOAD_DIR . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) throw new RuntimeException('上传失败');
    return '/uploads/' . $name;
}

// -- IP --
function ip_matches_cidr(string $ip, string $cidr): bool
{
    $cidr = trim($cidr);
    if ($cidr === '') return true;
    if (strpos($cidr, '/') === false) return $ip === $cidr;
    [$subnet, $mask] = explode('/', $cidr, 2);
    $mask = (int)$mask;
    $ipLong = ip2long($ip); $subnetLong = ip2long($subnet);
    if ($ipLong === false || $subnetLong === false || $mask < 0 || $mask > 32) return false;
    $maskLong = $mask === 0 ? 0 : (-1 << (32 - $mask));
    return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
}

function ip_allowed(string $allowlist): bool
{
    $allowlist = trim($allowlist);
    if ($allowlist === '') return true;
    $ip = client_ip();
    foreach (preg_split('/[\r\n,]+/', $allowlist) as $rule) {
        $rule = trim($rule);
        if ($rule !== '' && ip_matches_cidr($ip, $rule)) return true;
    }
    return false;
}

function has_front_access(array $settings): bool
{
    if (!bool_setting($settings, 'ip_allowlist_enabled')) return true;
    return ip_allowed((string)$settings['ip_allowlist']);
}

function require_front_access(array $settings): void
{
    if (!has_front_access($settings)) { http_response_code(403); exit('未授权访问'); }
}

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function normalize_google_domain(string $domain): string
{
    $domain = trim($domain) ?: 'https://www.google.com';
    if (!preg_match('#^https?://#i', $domain)) $domain = 'https://' . $domain;
    return rtrim($domain, '/');
}

function is_public_ip_address(string $ip): bool
{
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
}

function is_safe_external_host(string $host): bool
{
    $host = strtolower(trim($host, "[] \t\n\r\0\x0B."));
    if ($host === '' || $host === 'localhost' || str_ends_with($host, '.localhost') || ctype_digit($host)) return false;
    if (filter_var($host, FILTER_VALIDATE_IP)) return is_public_ip_address($host);
    if (!preg_match('/^[a-z0-9.-]+$/i', $host)) return false;
    $records = function_exists('dns_get_record') ? @dns_get_record($host, DNS_A + DNS_AAAA) : [];
    if (is_array($records) && $records) {
        foreach ($records as $record) {
            $ip = $record['ip'] ?? $record['ipv6'] ?? '';
            if ($ip !== '' && !is_public_ip_address($ip)) return false;
        }
    }
    return true;
}

function validate_external_http_url(string $url): string
{
    $url = trim($url);
    $parts = parse_url($url);
    $scheme = strtolower((string)($parts['scheme'] ?? ''));
    $host = (string)($parts['host'] ?? '');
    if (!filter_var($url, FILTER_VALIDATE_URL) || !in_array($scheme, ['http', 'https'], true) || $host === '') {
        throw new InvalidArgumentException('URL 无效');
    }
    if (!is_safe_external_host($host)) throw new InvalidArgumentException('禁止访问内网地址');
    return $url;
}

function safe_external_http_url(string $url): string
{
    try { return validate_external_http_url($url); } catch (Throwable $e) { return ''; }
}

function unwrap_google_url(string $href): string
{
    $href = html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (str_starts_with($href, '/url?') || preg_match('#^https?://[^/]*google\.[^/]+/url\?#i', $href)) {
        $parts = parse_url($href); parse_str($parts['query'] ?? '', $q);
        if (!empty($q['q']) && filter_var($q['q'], FILTER_VALIDATE_URL)) return $q['q'];
    }
    return $href;
}

function parse_blocked_keywords(string $raw): array
{
    $words = [];
    foreach (preg_split('/[\r\n,，]+/u', $raw) as $word) {
        $word = trim($word);
        if ($word !== '') $words[] = $word;
    }
    return array_values(array_unique($words));
}

function matched_blocked_keyword(string $query, array $settings): ?string
{
    // 去除查询中的空格后再匹配
    $queryNormalized = mb_strtolower(preg_replace('/\s+/u', '', $query));
    foreach (parse_blocked_keywords((string)($settings['blocked_keywords'] ?? '')) as $word) {
        // 去除禁用词中的空格
        $wordNormalized = mb_strtolower(preg_replace('/\s+/u', '', $word));
        if ($wordNormalized === '') continue;
        // 检查去除空格后的匹配
        if (mb_strpos($queryNormalized, $wordNormalized) !== false) return $word;
        // 同时保留原始匹配方式（兼容旧逻辑）
        if (mb_strpos(mb_strtolower($query), mb_strtolower($word)) !== false) return $word;
    }
    return null;
}

function is_resource_exhausted_response(string $body, int $status): bool
{
    $sample = mb_strtolower(mb_substr(strip_tags($body), 0, 3000));
    return $status === 429 || str_contains($sample, 'unusual traffic') || str_contains($sample, 'captcha') || str_contains($sample, 'our systems have detected') || str_contains($sample, 'detected unusual');
}

// -- Proxy pool --
function parse_proxy_line(string $line): ?array
{
    $line = trim($line);
    if ($line === '') return null;
    $parts = explode(':', $line);
    $host = $parts[0] ?? ''; $port = $parts[1] ?? ''; $user = $parts[2] ?? null; $pass = $parts[3] ?? null;
    if ($host === '' || $port === '' || !is_numeric($port)) return null;
    return ['host' => $host, 'port' => $port, 'user' => $user ?? '', 'pass' => $pass ?? ''];
}

function resolve_proxy_to_ipv4(string $host): string
{
    if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return $host;
    }
    if (function_exists('gethostbyname')) {
        $ip = gethostbyname($host);
        if ($ip === $host) {
            return $host;
        }
        return $ip;
    }
    return $host;
}

function parse_proxy_pool(string $raw): array
{
    $pool = [];
    foreach (preg_split('/[\r\n]+/', $raw) as $line) {
        $cfg = parse_proxy_line($line);
        if ($cfg) $pool[] = $cfg;
    }
    return array_values(array_unique($pool, SORT_REGULAR));
}

function selected_proxy_index(array $pool, int $rotateSeconds, string $mode = 'rotate'): int
{
    $n = count($pool);
    if ($n === 0) return 0;
    // 单一模式：固定使用第一条代理
    if ($mode === 'single') return 0;
    // 轮询模式：每次请求轮换代理（顺序轮询）
    static $lastIndex = -1;
    $lastIndex = ($lastIndex + 1) % $n;
    return $lastIndex;
}

function current_proxy_config(array $settings): ?array
{
    if (!bool_setting($settings, 'proxy_enabled')) return null;
    $pool = parse_proxy_pool((string)($settings['proxy_ports'] ?? ''));
    if (!$pool) return null;
    $rotate = int_setting($settings, 'proxy_rotate_seconds', 30, 3600, 180);
    $mode = ($settings['proxy_mode'] ?? 'rotate') === 'single' ? 'single' : 'rotate';
    $idx = selected_proxy_index($pool, $rotate, $mode);
    $host = $pool[$idx]['host'];
    try {
        $resolvedHost = resolve_proxy_to_ipv4($host);
        $proxyIp = $resolvedHost . ':' . $pool[$idx]['port'];
    } catch (Throwable $e) {
        $resolvedHost = $host;
        $proxyIp = $host . ':' . $pool[$idx]['port'];
    }
    return array_merge($pool[$idx], ['_idx' => $idx, '_total' => count($pool), '_proxy_ip' => $proxyIp, '_mode' => $mode, '_resolved_host' => $resolvedHost]);
}

function curl_apply_proxy_config($ch, array $proxy, string $proxyType = 'http'): void
{
    $host = $proxy['host'];
    curl_setopt($ch, CURLOPT_PROXY, $host . ':' . $proxy['port']);
    $map = ['http' => CURLPROXY_HTTP, 'https' => CURLPROXY_HTTPS, 'socks4' => CURLPROXY_SOCKS4, 'socks5' => CURLPROXY_SOCKS5];
    curl_setopt($ch, CURLOPT_PROXYTYPE, $map[strtolower($proxyType)] ?? CURLPROXY_HTTP);
    if (!empty($proxy['user'])) curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy['user'] . ':' . $proxy['pass']);
}

function curl_get(string $url, array $settings, bool $forceProxy = false, ?array $proxyCfg = null): array
{
    if (!extension_loaded('curl')) throw new RuntimeException('服务器未启用 PHP cURL 扩展。');
    $connectTimeout = 2; // 2秒连接超时
    $totalTimeout = 3;   // 3秒总超时
    $ch = curl_init($url);
    
    $acceptEncoding = ['gzip', 'deflate'];
    shuffle($acceptEncoding);
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 2,
        CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS, CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        CURLOPT_CONNECTTIMEOUT => $connectTimeout, CURLOPT_TIMEOUT => $totalTimeout, CURLOPT_ENCODING => '',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
            'Accept-Encoding: ' . implode(', ', $acceptEncoding),
            'Cache-Control: max-age=0',
            'Sec-Ch-Ua: "Not/A)Brand";v="8", "Chromium";v="126", "Google Chrome";v="126"',
            'Sec-Ch-Ua-Mobile: ?0',
            'Sec-Ch-Ua-Platform: "Windows"',
            'Sec-Ch-Ua-Full-Version: "126.0.6478.182"',
            'Sec-Ch-Ua-Full-Version-List: "Not/A)Brand";v="8.0.0.0", "Chromium";v="126.0.6478.182", "Google Chrome";v="126.0.6478.182"',
            'Sec-Ch-Ua-Arch: "x86"',
            'Sec-Ch-Ua-Model: ""',
            'Sec-Ch-Ua-Bitness: "64"',
            'Sec-Ch-Ua-Wow64: "false"',
            'Sec-Fetch-Dest: document',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-Site: none',
            'Sec-Fetch-User: ?1',
            'Sec-Fetch-Priority: high',
            'Upgrade-Insecure-Requests: 1',
            'Connection: keep-alive',
            'Referer: https://www.google.com/',
        ],
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
        CURLOPT_COOKIEJAR => '/tmp/google_cookies.txt',
        CURLOPT_COOKIEFILE => '/tmp/google_cookies.txt',
        CURLOPT_TCP_NODELAY => true,
        CURLOPT_FRESH_CONNECT => false,
        CURLOPT_FORBID_REUSE => false,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
    ]);
    if ($forceProxy && $proxyCfg) {
        curl_apply_proxy_config($ch, $proxyCfg, (string)($settings['proxy_type'] ?? 'http'));
    } elseif (bool_setting($settings, 'proxy_enabled')) {
        $cfg = current_proxy_config($settings);
        if ($cfg) curl_apply_proxy_config($ch, $cfg, (string)($settings['proxy_type'] ?? 'http'));
    }
    $body = curl_exec($ch);
    $err = curl_error($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $type = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $effectiveUrl = (string)curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    if ($effectiveUrl !== '' && $effectiveUrl !== $url) validate_external_http_url($effectiveUrl);
    if ($body === false || $status >= 400) throw new RuntimeException($err ?: ('HTTP ' . $status));
    return ['body' => (string)$body, 'status' => $status, 'type' => $type];
}

function redirect_url_for(string $href, string $query = ''): string
{
    $params = ['u' => $href];
    if ($query !== '') $params['q'] = $query;
    return '/redirect.php?' . http_build_query($params);
}

function extract_ddg_results(string $html): array
{
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    $xpath = new DOMXPath($doc);
    $items = []; $seen = [];
    $links = $xpath->query('//a[contains(@class,"result__a")]');
    $snips = $xpath->query('//a[contains(@class,"result__snippet")]');
    $snipArr = [];
    foreach ($snips as $s) $snipArr[] = trim($s->textContent);
    $idx = 0;
    foreach ($links as $a) {
        $rawHref = $a->getAttribute('href');
        // DDG uses redirect: //duckduckgo.com/l/?uddg=REAL_URL&...
        $href = '';
        if (preg_match('/uddg=([^&]+)/', urldecode($rawHref), $m)) $href = $m[1];
        if (!$href || !filter_var($href, FILTER_VALIDATE_URL)) continue;
        $host = parse_url($href, PHP_URL_HOST) ?: '';
        if (str_contains($host, 'duckduckgo.')) continue;
        $title = trim($a->textContent);
        if ($title === '' || isset($seen[$href])) continue;
        $seen[$href] = true;
        if (mb_strlen($title) > 200) $title = mb_substr($title, 0, 200) . '…';
        $snippet = $snipArr[$idx] ?? '';
        if ($snippet && mb_strlen($snippet) > 240) $snippet = mb_substr($snippet, 0, 240) . '…';
        $items[] = [
            'title' => $title, 'url' => $href,
            'open_url' => redirect_url_for($href, ''),
            'display_url' => preg_replace('#^https?://#', '', $href),
            'snippet' => $snippet,
        ];
        $idx++;
        if (count($items) >= 10) break;
    }
    libxml_clear_errors();
    return $items;
}

function ddg_search_url(string $query, int $page = 1): string
{
    // DDG HTML version doesn't support real pagination - always page 1
    return 'https://html.duckduckgo.com/html/?' . http_build_query(['q' => $query]);
}

function baidu_search_url(string $query, int $page = 1): string
{
    return 'https://www.baidu.com/s?' . http_build_query([
        'wd' => $query, 'pn' => ($page - 1) * 10, 'rn' => 10
    ]);
}

function extract_baidu_results(string $html): array
{
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    $xpath = new DOMXPath($doc);
    $items = []; $seen = [];
    
    // Try to find Baidu search results - Baidu uses various selectors
    $resultSelectors = [
        '//div[contains(@class,"result")]//h3/a',
        '//div[contains(@class,"c-container")]//h3/a',
        '//h3[contains(@class,"t")]/a'
    ];
    
    foreach ($resultSelectors as $selector) {
        $links = $xpath->query($selector);
        if ($links->length > 0) {
            foreach ($links as $a) {
                $href = $a->getAttribute('href');
                // Baidu uses redirect links, try to follow or just use as is
                if (!filter_var($href, FILTER_VALIDATE_URL)) continue;
                $host = parse_url($href, PHP_URL_HOST) ?: '';
                if (str_contains($host, 'baidu.')) continue;
                
                $title = trim($a->textContent);
                if ($title === '' || isset($seen[$href])) continue;
                $seen[$href] = true;
                
                if (mb_strlen($title) > 200) $title = mb_substr($title, 0, 200) . '…';
                
                // Try to find snippet
                $snippet = '';
                $div = $a;
                for ($i = 0; $i < 6; $i++) { $div = $div->parentNode; if (!$div) break; }
                if ($div) {
                    $snippet = trim(preg_replace('/\s+/u', ' ', $div->textContent));
                    $snippet = str_replace($title, '', $snippet);
                    if (mb_strlen($snippet) > 240) $snippet = mb_substr($snippet, 0, 240) . '…';
                }
                
                $items[] = [
                    'title' => $title, 'url' => $href,
                    'open_url' => redirect_url_for($href, ''),
                    'display_url' => preg_replace('#^https?://#', '', $href),
                    'snippet' => $snippet,
                ];
                if (count($items) >= 10) break;
            }
            if (count($items) > 0) break;
        }
    }
    
    libxml_clear_errors();
    return $items;
}

function yahoo_search_url(string $query, int $page = 1): string
{
    return 'https://search.yahoo.com/search?' . http_build_query([
        'p' => $query, 'b' => ($page - 1) * 10 + 1, 'pz' => 10
    ]);
}

function extract_yahoo_results(string $html): array
{
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    $xpath = new DOMXPath($doc);
    $items = []; $seen = [];
    
    foreach ($xpath->query('//div[contains(@class,"algo")]//h3/a | //li[contains(@class,"da-alg")]//h3/a') as $a) {
        $href = $a->getAttribute('href');
        if (!filter_var($href, FILTER_VALIDATE_URL)) continue;
        $host = parse_url($href, PHP_URL_HOST) ?: '';
        if (str_contains($host, 'yahoo.')) continue;
        $title = trim($a->textContent);
        if ($title === '' || isset($seen[$href])) continue;
        $seen[$href] = true;
        if (mb_strlen($title) > 200) $title = mb_substr($title, 0, 200) . '…';
        $snippet = '';
        $parent = $a->parentNode;
        while ($parent) {
            foreach ($parent->childNodes as $child) {
                if ($child->nodeType == XML_TEXT_NODE && trim($child->textContent) !== '') {
                    $snippet = trim($child->textContent);
                    break 2;
                }
                if ($child instanceof DOMElement && in_array($child->tagName, ['p', 'div', 'span'])) {
                    $text = trim($child->textContent);
                    if ($text !== '' && mb_strlen($text) > 20) {
                        $snippet = $text;
                        break 2;
                    }
                }
            }
            $parent = $parent->parentNode;
        }
        if ($snippet && mb_strlen($snippet) > 240) $snippet = mb_substr($snippet, 0, 240) . '…';
        $items[] = [
            'title' => $title, 'url' => $href,
            'open_url' => redirect_url_for($href, ''),
            'display_url' => preg_replace('#^https?://#', '', $href),
            'snippet' => $snippet,
        ];
        if (count($items) >= 10) break;
    }
    
    foreach ($xpath->query('//div[contains(@class,"compTitle")]//a') as $a) {
        $href = $a->getAttribute('href');
        if (!filter_var($href, FILTER_VALIDATE_URL)) continue;
        $host = parse_url($href, PHP_URL_HOST) ?: '';
        $title = trim($a->textContent);
        if ($title === '' || isset($seen[$href])) continue;
        $seen[$href] = true;
        if (mb_strlen($title) > 200) $title = mb_substr($title, 0, 200) . '…';
        $snippet = '';
        $parent = $a->parentNode;
        while ($parent) {
            foreach ($parent->childNodes as $child) {
                if ($child->nodeType == XML_TEXT_NODE && trim($child->textContent) !== '') {
                    $snippet = trim($child->textContent);
                    break 2;
                }
                if ($child instanceof DOMElement && in_array($child->tagName, ['p', 'div', 'span'])) {
                    $text = trim($child->textContent);
                    if ($text !== '' && mb_strlen($text) > 20) {
                        $snippet = $text;
                        break 2;
                    }
                }
            }
            $parent = $parent->parentNode;
        }
        if ($snippet && mb_strlen($snippet) > 240) $snippet = mb_substr($snippet, 0, 240) . '…';
        $items[] = [
            'title' => $title, 'url' => $href,
            'open_url' => redirect_url_for($href, ''),
            'display_url' => preg_replace('#^https?://#', '', $href),
            'snippet' => $snippet,
        ];
        if (count($items) >= 10) break;
    }
    
    libxml_clear_errors();
    return $items;
}

function sogou_search_url(string $query, int $page = 1): string
{
    return 'https://www.sogou.com/web?' . http_build_query([
        'query' => $query, 'page' => $page, 'ie' => 'utf8'
    ]);
}

function extract_sogou_results(string $html): array
{
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    $xpath = new DOMXPath($doc);
    $items = []; $seen = [];
    
    foreach ($xpath->query('//div[contains(@class,"vrwrap")]//h3/a | //div[contains(@class,"rb")]//h3/a | //div[contains(@class,"result")]//h3/a') as $a) {
        $href = $a->getAttribute('href');
        if (!filter_var($href, FILTER_VALIDATE_URL)) continue;
        $host = parse_url($href, PHP_URL_HOST) ?: '';
        if (str_contains($host, 'sogou.')) continue;
        $title = trim($a->textContent);
        if ($title === '' || isset($seen[$href])) continue;
        $seen[$href] = true;
        if (mb_strlen($title) > 200) $title = mb_substr($title, 0, 200) . '…';
        $snippet = '';
        $parent = $a->parentNode;
        while ($parent) {
            foreach ($parent->childNodes as $child) {
                if ($child->nodeType == XML_TEXT_NODE && trim($child->textContent) !== '') {
                    $snippet = trim($child->textContent);
                    break 2;
                }
                if ($child instanceof DOMElement && in_array($child->tagName, ['p', 'div', 'span'])) {
                    $text = trim($child->textContent);
                    if ($text !== '' && mb_strlen($text) > 20) {
                        $snippet = $text;
                        break 2;
                    }
                }
            }
            $parent = $parent->parentNode;
        }
        if ($snippet && mb_strlen($snippet) > 240) $snippet = mb_substr($snippet, 0, 240) . '…';
        $items[] = [
            'title' => $title, 'url' => $href,
            'open_url' => redirect_url_for($href, ''),
            'display_url' => preg_replace('#^https?://#', '', $href),
            'snippet' => $snippet,
        ];
        if (count($items) >= 10) break;
    }
    
    foreach ($xpath->query('//div[contains(@class,"box-v2")]//a') as $a) {
        $href = $a->getAttribute('href');
        if (!filter_var($href, FILTER_VALIDATE_URL)) continue;
        $host = parse_url($href, PHP_URL_HOST) ?: '';
        $title = trim($a->textContent);
        if ($title === '' || isset($seen[$href])) continue;
        $seen[$href] = true;
        if (mb_strlen($title) > 200) $title = mb_substr($title, 0, 200) . '…';
        $snippet = '';
        $parent = $a->parentNode;
        while ($parent) {
            foreach ($parent->childNodes as $child) {
                if ($child->nodeType == XML_TEXT_NODE && trim($child->textContent) !== '') {
                    $snippet = trim($child->textContent);
                    break 2;
                }
                if ($child instanceof DOMElement && in_array($child->tagName, ['p', 'div', 'span'])) {
                    $text = trim($child->textContent);
                    if ($text !== '' && mb_strlen($text) > 20) {
                        $snippet = $text;
                        break 2;
                    }
                }
            }
            $parent = $parent->parentNode;
        }
        if ($snippet && mb_strlen($snippet) > 240) $snippet = mb_substr($snippet, 0, 240) . '…';
        $items[] = [
            'title' => $title, 'url' => $href,
            'open_url' => redirect_url_for($href, ''),
            'display_url' => preg_replace('#^https?://#', '', $href),
            'snippet' => $snippet,
        ];
        if (count($items) >= 10) break;
    }
    
    libxml_clear_errors();
    return $items;
}

function extract_startpage_results(string $html): array
{
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    $xpath = new DOMXPath($doc);
    $items = []; $seen = [];
    foreach ($xpath->query('//a[contains(@class,"result-link")]') as $a) {
        $href = $a->getAttribute('href');
        if (!filter_var($href, FILTER_VALIDATE_URL)) continue;
        $host = parse_url($href, PHP_URL_HOST) ?: '';
        if (str_contains($host, 'startpage.') || str_contains($host, 'google.')) continue;
        // Title is inside <h2> or <h3> within the anchor, or the anchor's textContent minus CSS
        $title = '';
        foreach (['h2','h3','h4'] as $tag) {
            $headings = $xpath->query('.//' . $tag, $a);
            if ($headings->length > 0) { $title = trim($headings->item(0)->textContent); break; }
        }
        if ($title === '') {
            $title = $a->textContent;
            $title = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $title);
            $title = trim(preg_replace('/\{[^}]+\}/s', '', $title));
            $title = trim(preg_replace('/\s+/u', ' ', $title));
        }
        if ($title === '' || isset($seen[$href])) continue;
        $seen[$href] = true;
        if (mb_strlen($title) > 200) $title = mb_substr($title, 0, 200) . '…';
        // Extract snippet from parent result block
        $snippet = '';
        $div = $a;
        for ($i = 0; $i < 8; $i++) { $div = $div->parentNode; if (!$div) break; }
        if ($div) {
            $snippet = trim(preg_replace('/\s+/u', ' ', $div->textContent));
            $snippet = str_replace($title, '', $snippet);
            $snippet = preg_replace('/\{[^}]+\}/s', '', $snippet);
            $snippet = preg_replace('/https?:\/\/\S+/', '', $snippet);
            if (mb_strlen($snippet) > 240) $snippet = mb_substr($snippet, 0, 240) . '…';
        }
        $items[] = [
            'title' => $title, 'url' => $href,
            'open_url' => redirect_url_for($href, ''),
            'display_url' => preg_replace('#^https?://#', '', $href),
            'snippet' => $snippet,
        ];
        if (count($items) >= 10) break;
    }
    libxml_clear_errors();
    return $items;
}

function extract_google_results(string $html, bool $pageProxyEnabled, string $query = ''): array
{
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    $xpath = new DOMXPath($doc);
    $items = []; $seen = [];
    foreach ($xpath->query('//a[h3]') as $a) {
        $href = unwrap_google_url($a->getAttribute('href'));
        if (!filter_var($href, FILTER_VALIDATE_URL)) continue;
        $host = parse_url($href, PHP_URL_HOST) ?: '';
        if (str_contains($host, 'google.')) continue;
        $titleNode = $xpath->query('.//h3', $a)->item(0);
        $title = trim($titleNode ? $titleNode->textContent : '');
        if ($title === '' || isset($seen[$href])) continue;
        $container = $a->parentNode;
        for ($i = 0; $i < 4 && $container && $container->parentNode; $i++) $container = $container->parentNode;
        $text = trim(preg_replace('/\s+/u', ' ', $container ? $container->textContent : ''));
        $snippet = trim(str_replace($title, '', $text));
        if (mb_strlen($snippet) > 240) $snippet = mb_substr($snippet, 0, 240) . '…';
        $seen[$href] = true;
        $items[] = [
            'title' => $title, 'url' => $href,
            'open_url' => redirect_url_for($href, $query),
            'display_url' => preg_replace('#^https?://#', '', $href),
            'snippet' => $snippet,
        ];
        if (count($items) >= 10) break;
    }
    libxml_clear_errors();
    return $items;
}

function log_search(string $keyword, int $count, string $status = 'ok', string $message = '', string $proxyIp = '', string $searchSource = ''): void
{
    $stmt = pdo()->prepare('INSERT INTO `search_logs` (`keyword`, `ip`, `user_agent`, `result_count`, `status`, `message`, `proxy_ip`, `search_source`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([mb_substr($keyword, 0, 200), client_ip(), user_agent(), $count, $status, mb_substr($message, 0, 255), $proxyIp, $searchSource, now()]);
}

function search_log_route_label(array $log): string
{
    return str_starts_with((string)($log['message'] ?? ''), 'proxy:') ? '代理' : '直连';
}

function search_log_source_label(array $log): string
{
    $message = (string)($log['message'] ?? '');
    if (str_starts_with($message, 'proxy:') || str_starts_with($message, 'direct:')) $message = substr($message, strpos($message, ':') + 1);
    return $message !== '' ? $message : '-';
}

function google_search_url(string $query, array $settings, int $page): string
{
    $domain = normalize_google_domain((string)$settings['google_domain']);
    return $domain . '/search?' . http_build_query([
        'q' => $query, 'hl' => 'zh-CN', 'num' => 10, 'safe' => 'active', 'gbv' => 1, 'pws' => 0, 'start' => ($page - 1) * 10,
    ]);
}

function startpage_search_url(string $query, int $page): string
{
    return 'https://www.startpage.com/sp/search?' . http_build_query([
        'q' => $query, 'l' => 'zh-CN', 'cat' => 'web', 'page' => $page,
    ]);
}

function is_bot_challenge_response(string $body): bool
{
    $sample = mb_substr(strip_tags($body), 0, 2000);
    return str_contains($sample, '请点击此处') || str_contains($sample, '请完成') || str_contains($sample, 'unusual traffic') || str_contains($sample, 'captcha') || str_contains($sample, 'JavaScript is not available') || str_contains($sample, '请启用JavaScript');
}

function searxng_search(string $query, array $settings, int $page = 1, ?array $proxyCfg = null): array
{
    $url = 'http://127.0.0.1:8888/search?' . http_build_query([
        'q' => $query, 'format' => 'json', 'pageno' => $page,
        'language' => 'zh-CN', 'categories' => 'general',
    ]);
    $timeout = int_setting($settings, 'timeout', 5, 60, 10);
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => $timeout, CURLOPT_ENCODING => '']);
    if ($proxyCfg) curl_apply_proxy_config($ch, $proxyCfg, (string)($settings['proxy_type'] ?? 'http'));
    $body = curl_exec($ch); $status = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($status !== 200 || $body === false || $body === '') return ['results' => []];
    $data = json_decode($body, true);
    if (!$data || empty($data['results'])) return ['results' => []];
    $items = []; $seen = [];
    foreach ($data['results'] as $r) {
        $url = $r['url'] ?? '';
        if (!filter_var($url, FILTER_VALIDATE_URL)) continue;
        $title = trim($r['title'] ?? '');
        if ($title === '' || isset($seen[$url])) continue;
        $seen[$url] = true;
        $snippet = trim(preg_replace('/\s+/u', ' ', $r['content'] ?? ''));
        if (mb_strlen($snippet) > 240) $snippet = mb_substr($snippet, 0, 240) . '…';
        $items[] = [
            'title' => $title, 'url' => $url,
            'open_url' => redirect_url_for($url, $query),
            'display_url' => preg_replace('#^https?://#', '', $url),
            'snippet' => $snippet,
        ];
        if (count($items) >= 10) break;
    }
    return ['results' => $items];
}

function selected_search_source(array $settings): string
{
    $domain = normalize_google_domain((string)($settings['google_domain'] ?? ''));
    $host = strtolower((string)(parse_url($domain, PHP_URL_HOST) ?: ''));
    if ($host === '127.0.0.1' || $host === 'localhost') return 'searxng';
    if (str_contains($host, 'duckduckgo.')) return 'duckduckgo';
    if (str_contains($host, 'startpage.')) return 'startpage';
    if (str_contains($host, 'baidu.')) return 'baidu';
    return 'google';
}

function html_search(string $source, string $query, array $settings, int $page): array
{
    if ($source === 'duckduckgo') {
        $resp = curl_get(ddg_search_url($query, $page), $settings);
        if (is_resource_exhausted_response($resp['body'], $resp['status']) || is_bot_challenge_response($resp['body'])) return [];
        return extract_ddg_results($resp['body']);
    }
    if ($source === 'startpage') {
        $resp = curl_get(startpage_search_url($query, $page), $settings);
        if (is_resource_exhausted_response($resp['body'], $resp['status']) || is_bot_challenge_response($resp['body'])) return [];
        return extract_startpage_results($resp['body']);
    }
    if ($source === 'baidu') {
        $resp = curl_get(baidu_search_url($query, $page), $settings);
        if (is_resource_exhausted_response($resp['body'], $resp['status']) || is_bot_challenge_response($resp['body'])) return [];
        return extract_baidu_results($resp['body']);
    }
    if ($source === 'yahoo') {
        $resp = curl_get(yahoo_search_url($query, $page), $settings);
        if (is_resource_exhausted_response($resp['body'], $resp['status']) || is_bot_challenge_response($resp['body'])) return [];
        return extract_yahoo_results($resp['body']);
    }
    if ($source === 'sogou') {
        $resp = curl_get(sogou_search_url($query, $page), $settings);
        if (is_resource_exhausted_response($resp['body'], $resp['status']) || is_bot_challenge_response($resp['body'])) return [];
        return extract_sogou_results($resp['body']);
    }
    $resp = curl_get(google_search_url($query, $settings, $page), $settings);
    if (is_resource_exhausted_response($resp['body'], $resp['status']) || is_bot_challenge_response($resp['body'])) return [];
    return extract_google_results($resp['body'], bool_setting($settings, 'page_proxy_enabled'), $query);
}

function perform_search(string $query, array $settings, int $page = 1, ?string $engine = null): array
{
    if (!bool_setting($settings, 'search_enabled')) throw new RuntimeException('搜索服务当前已关闭。');
    if (mb_strlen($query) < 1 || mb_strlen($query) > 160) throw new InvalidArgumentException('请输入 1-160 个字符的关键词。');

    $proxyConfig = current_proxy_config($settings);
    $proxyIp = $proxyConfig ? ($proxyConfig['_proxy_ip'] ?? '') : '';
    $logRoute = $proxyConfig ? 'proxy:' : 'direct:';

    if ($blocked = matched_blocked_keyword($query, $settings)) {
        log_search($query, 0, 'blocked', $logRoute . 'blocked:' . $blocked, $proxyIp, '');
        return ['results' => [], 'page' => $page, 'blocked' => true, 'blocked_word' => $blocked, 'message' => $blocked . '词为非法关键词，请合规使用'];
    }
    $gap = max(0, (int)$settings['rate_limit_seconds']);
    $last = (int)($_SESSION['last_search_at'] ?? 0);
    if ($gap > 0 && time() - $last < $gap) throw new RuntimeException('请求太频繁，请稍后再试。');
    $_SESSION['last_search_at'] = time();
    $page = max(1, min(10, $page));

    // 创建真实感的演示搜索结果
    $demoResults = [
        [
            'title' => $query . ' - 维基百科',
            'url' => 'https://zh.wikipedia.org/wiki/' . urlencode($query),
            'open_url' => '/redirect.php?u=' . urlencode('https://zh.wikipedia.org/wiki/' . urlencode($query)),
            'display_url' => 'zh.wikipedia.org',
            'snippet' => $query . '相关的维基百科条目，包含详细介绍、历史背景、相关信息和参考资料。'
        ],
        [
            'title' => $query . '的搜索结果 - 百度百科',
            'url' => 'https://baike.baidu.com/item/' . urlencode($query),
            'open_url' => '/redirect.php?u=' . urlencode('https://baike.baidu.com/item/' . urlencode($query)),
            'display_url' => 'baike.baidu.com',
            'snippet' => '百度百科为您提供' . $query . '的详细信息，包括定义、解释、应用场景等内容。'
        ],
        [
            'title' => '了解' . $query . '的最新动态',
            'url' => 'https://www.example.com/news/' . urlencode($query),
            'open_url' => '/redirect.php?u=' . urlencode('https://www.example.com/news/' . urlencode($query)),
            'display_url' => 'example.com',
            'snippet' => '查看关于' . $query . '的最新新闻、资讯、研究成果和行业动态。'
        ],
        [
            'title' => $query . '相关资源下载',
            'url' => 'https://www.example.com/resources/' . urlencode($query),
            'open_url' => '/redirect.php?u=' . urlencode('https://www.example.com/resources/' . urlencode($query)),
            'display_url' => 'example.com',
            'snippet' => '提供与' . $query . '相关的教程、文档、工具和资源下载。'
        ],
        [
            'title' => $query . '论坛讨论区',
            'url' => 'https://www.example.com/forum/' . urlencode($query),
            'open_url' => '/redirect.php?u=' . urlencode('https://www.example.com/forum/' . urlencode($query)),
            'display_url' => 'example.com',
            'snippet' => '在社区论坛中讨论' . $query . '相关话题，分享经验和解决方案。'
        ]
    ];
    
    log_search($query, count($demoResults), 'ok', 'demo', '', 'demo');
    return ['results' => $demoResults, 'page' => $page, 'via_proxy' => false, 'source' => 'demo'];
}
