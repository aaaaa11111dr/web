<?php
require __DIR__ . '/app/bootstrap.php';
require_install();
$settings = get_settings();
if (!has_front_access($settings)) { http_response_code(403); exit('未授权访问'); }
if (!bool_setting($settings, 'page_proxy_enabled')) { http_response_code(403); exit('页面代理未启用'); }
try { $u = validate_external_http_url((string)($_GET['u'] ?? '')); }
catch (Throwable $e) { http_response_code(400); exit($e->getMessage()); }
try {
    // 获取代理配置并解析为IPv4
    $proxyConfig = current_proxy_config($settings);
    $proxyIp = '';
    if ($proxyConfig && !empty($proxyConfig['host'])) {
        // 使用resolve_proxy_to_ipv4函数将域名解析为IPv4
        $resolvedHost = resolve_proxy_to_ipv4($proxyConfig['host']);
        $proxyIp = $resolvedHost . ':' . $proxyConfig['port'];
    }
    $resp = curl_get($u, $settings);
    // 记录日志，包含proxy_ip（已解析为IPv4）
    $stmt = pdo()->prepare('INSERT INTO `page_proxy_logs` (`target_url`, `ip`, `status_code`, `proxy_ip`, `created_at`) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([mb_substr($u, 0, 800), client_ip(), $resp['status'], $proxyIp, now()]);
    header('Content-Type: ' . ($resp['type'] ?: 'text/html; charset=utf-8'));
    echo $resp['body'];
} catch (Throwable $e) { http_response_code(502); echo '代理请求失败：' . e($e->getMessage()); }
