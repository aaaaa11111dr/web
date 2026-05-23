<?php
require __DIR__ . '/../app/bootstrap.php';
require_install();
$settings = get_settings();
require_front_access($settings);
$q = trim((string)($_GET['q'] ?? ''));
$page = max(1, min(10, (int)($_GET['page'] ?? 1)));
try {
    $data = perform_search($q, $settings, $page);
    json_response([
        'ok' => true,
        'query' => $q,
        'page' => $page,
        'results' => $data['results'],
        'blocked' => !empty($data['blocked']),
        'blocked_word' => $data['blocked_word'] ?? '',
        'resource_exhausted' => !empty($data['resource_exhausted']),
        'message' => $data['message'] ?? '',
        'via_proxy' => !empty($data['via_proxy']),
        'proxy_slot' => $data['proxy_slot'] ?? '',
        'ads' => [
            'blocked' => render_ad_pool('blocked', '拦截页广告'),
            'resource' => render_ad_pool('resource', '资源耗尽页广告'),
            'top' => render_ad_pool('results_top', '结果顶部广告'),
            'middle' => render_ad_pool('results_middle', '结果中部广告'),
            'bottom' => render_ad_pool('results_bottom', '结果底部广告'),
        ],
        'pager_countdown' => int_setting($settings, 'pager_countdown_seconds', 0, 300, 20),
    ]);
} catch (Throwable $ex) {
    json_response(['ok' => false, 'message' => $ex->getMessage()], 400);
}
