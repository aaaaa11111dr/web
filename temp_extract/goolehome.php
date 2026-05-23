<?php
require __DIR__ . '/app/bootstrap.php';
require_install();
$error = ''; $ok = ''; $settings = get_settings();

// 直接设置管理员会话，跳过登录
if (!is_admin()) {
    $_SESSION['admin_id'] = 1;
    $_SESSION['admin_user'] = 'admin';
}

if (isset($_GET['logout'])) { session_destroy(); redirect('/goolehome.php'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf(); $action = $_POST['action'] ?? '';
    try {
        if ($action === 'search-settings') {
            save_settings([
                'site_name' => trim((string)$_POST['site_name']) ?: 'Search',
                'site_notice' => trim((string)$_POST['site_notice']) ?: '仅限内部人员使用。',
                'footer_notice' => trim((string)$_POST['footer_notice']) ?: '请用于正规用途',
                'home_title' => trim((string)($_POST['home_title'] ?? '')) ?: '{site_name}',
                'home_description' => trim((string)($_POST['home_description'] ?? '')),
                'home_keywords' => trim((string)($_POST['home_keywords'] ?? '')),
                'result_title' => trim((string)($_POST['result_title'] ?? '')) ?: '{q} - {site_name}',
                'result_description' => trim((string)($_POST['result_description'] ?? '')),
                'result_keywords' => trim((string)($_POST['result_keywords'] ?? '')),
                'search_enabled' => !empty($_POST['search_enabled']) ? '1' : '0',
                'google_domain' => normalize_google_domain((string)$_POST['google_domain']),
                'timeout' => (string)min(60, max(5, (int)$_POST['timeout'])),
                'rate_limit_seconds' => (string)min(30, max(0, (int)$_POST['rate_limit_seconds'])),
                'pager_countdown_seconds' => (string)max(1, min(300, (int)$_POST['pager_countdown_seconds'])),
                'redirect_countdown_seconds' => (string)max(1, min(300, (int)$_POST['redirect_countdown_seconds'])),
            ]); $settings = get_settings(); $ok = '搜索配置已保存。';
        }
        if ($action === 'proxy-settings') {
            save_settings([
                'proxy_enabled' => !empty($_POST['proxy_enabled']) ? '1' : '0',
                'proxy_type' => in_array($_POST['proxy_type'] ?? 'http', ['http','https','socks4','socks5'], true) ? $_POST['proxy_type'] : 'http',
                'proxy_mode' => in_array($_POST['proxy_mode'] ?? 'rotate', ['rotate','single'], true) ? $_POST['proxy_mode'] : 'rotate',
                'proxy_ports' => trim((string)$_POST['proxy_ports']),
                'proxy_rotate_seconds' => (string)max(30, min(3600, (int)$_POST['proxy_rotate_seconds'])),
                'page_proxy_enabled' => !empty($_POST['page_proxy_enabled']) ? '1' : '0',
                'cache_seconds' => (string)max(0, min(3600, (int)$_POST['cache_seconds'])),
            ]); $settings = get_settings(); $ok = '代理配置已保存。';
        }
        if ($action === 'ad-save') {
            $id = !empty($_POST['ad_id']) ? (int)$_POST['ad_id'] : null;
            $img = trim((string)($_POST['ad_image_url'] ?? ''));
            if (!empty($_FILES['ad_image_file']['tmp_name'])) {
                try { $img = handle_upload(); } catch (Throwable $e) { throw new RuntimeException('图片上传失败：' . $e->getMessage()); }
            }
            save_ad($id, (string)$_POST['ad_pool'], trim((string)$_POST['ad_title']), $img, trim((string)$_POST['ad_link']), (int)$_POST['ad_sort'], !empty($_POST['ad_enabled']), (string)($_POST['ad_type'] ?? 'image'), (string)($_POST['ad_embed_code'] ?? ''));
            $ok = '广告已保存。';
        }
        if ($action === 'ad-delete') {
            delete_ad((int)$_POST['ad_id']); $ok = '广告已删除。';
        }
        if ($action === 'access-settings') {
            save_settings([
                'ip_allowlist_enabled' => !empty($_POST['ip_allowlist_enabled']) ? '1' : '0',
                'ip_allowlist' => trim((string)$_POST['ip_allowlist']),
                'blocked_keywords' => trim((string)$_POST['blocked_keywords']),
            ]); $settings = get_settings(); $ok = '访问控制已保存。';
        }
        if ($action === 'password') {
            $current = (string)($_POST['current_admin_password'] ?? '');
            $new = (string)$_POST['new_admin_password']; if (strlen($new) < 8) throw new RuntimeException('管理员密码至少 8 位。');
            $stmt = pdo()->prepare('SELECT `password_hash` FROM `admins` WHERE `id` = ? LIMIT 1'); $stmt->execute([(int)$_SESSION['admin_id']]); $admin = $stmt->fetch();
            if (!$admin || !password_verify($current, (string)$admin['password_hash'])) throw new RuntimeException('当前密码不正确。');
            pdo()->prepare('UPDATE `admins` SET `password_hash` = ?, `updated_at` = ? WHERE `id` = ?')->execute([password_hash($new, PASSWORD_DEFAULT), now(), (int)$_SESSION['admin_id']]); $ok = '管理员密码已更新。';
        }
        if ($action === 'clear_logs') {
            $clearType = $_POST['clear_type'] ?? '';
            if ($clearType === 'search') {
                pdo()->exec('TRUNCATE TABLE `search_logs`');
                $ok = '搜索代理日志已清空。';
            } elseif ($clearType === 'proxy') {
                pdo()->exec('TRUNCATE TABLE `page_proxy_logs`');
                $ok = '页面代理日志已清空。';
            } elseif ($clearType === 'all') {
                pdo()->exec('TRUNCATE TABLE `search_logs`');
                pdo()->exec('TRUNCATE TABLE `page_proxy_logs`');
                $ok = '所有日志已清空。';
            } else {
                $error = '请选择要清空的日志类型。';
            }
        }
        if ($action === 'test_search') { $data = perform_search('OpenClaw', $settings, 1); $ok = '连通测试完成，解析到 ' . count($data['results']) . ' 条结果。'; }
    } catch (Throwable $e) { $error = $e->getMessage(); }
}

$logsPerPage = 10;
$logsPage = max(1, (int)($_GET['logs_page'] ?? 1));
$logsOffset = ($logsPage - 1) * $logsPerPage;
$proxyLogsPerPage = 10;
$proxyLogsPage = max(1, (int)($_GET['proxy_logs_page'] ?? 1));
$proxyLogsOffset = ($proxyLogsPage - 1) * $proxyLogsPerPage;

$logs = []; $proxyLogs = []; $allAds = []; $stats = []; $totalLogs = 0; $totalProxyLogs = 0;
$totalLogs = (int)pdo()->query('SELECT COUNT(*) FROM `search_logs`')->fetchColumn();
$totalProxyLogs = (int)pdo()->query('SELECT COUNT(*) FROM `page_proxy_logs`')->fetchColumn();
$stmt = pdo()->prepare('SELECT * FROM `search_logs` ORDER BY `id` DESC LIMIT ? OFFSET ?');
$stmt->execute([$logsPerPage, $logsOffset]);
$logs = $stmt->fetchAll();
$proxyStmt = pdo()->prepare('SELECT * FROM `page_proxy_logs` ORDER BY `id` DESC LIMIT ? OFFSET ?');
$proxyStmt->execute([$proxyLogsPerPage, $proxyLogsOffset]);
$proxyLogs = $proxyStmt->fetchAll();
$allAds = get_all_ads();
$stats = get_site_stats();

$totalLogsPages = max(1, (int)ceil($totalLogs / $logsPerPage));
$totalProxyLogsPages = max(1, (int)ceil($totalProxyLogs / $proxyLogsPerPage));
$poolKeys = [
    'home' => '首页', 'blocked' => '拦截页', 'results_top' => '结果顶部横幅',
    'results_middle' => '结果中部横幅', 'results_bottom' => '结果底部横幅',
    'results_side' => '结果右侧', 'redirect' => '中转页', 'resource' => '资源耗尽页',
];
?><!doctype html><html lang="zh-CN"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>后台 - <?= e(APP_NAME) ?></title><link rel="stylesheet" href="/assets/style.css?v=2026052201"></head><body class="admin-body"><button class="hamburger" id="hamburger" aria-label="菜单"><span></span></button><div class="sidebar-backdrop" id="sidebarBackdrop"></div><div class="admin-layout"><aside class="admin-sidebar" id="adminSidebar"><a class="admin-logo" href="/goolehome.php"><span><?= e(mb_substr($settings['site_name'], 0, 1)) ?></span><strong>后台</strong></a><nav class="side-nav"><a href="#overview" data-page="overview" class="active">概览</a><a href="#stats" data-page="stats">统计</a><a href="#search" data-page="search">搜索</a><a href="#proxy" data-page="proxy">代理</a><a href="#ad" data-page="ad">广告</a><a href="#access" data-page="access">访问</a><a href="#tools" data-page="tools">工具</a><a href="#logs" data-page="logs">日志</a><a href="/" target="_blank">前台</a></nav><a class="logout-link" href="/goolehome.php?logout=1">退出</a></aside><main class="admin-content"><header class="admin-header"><h1 id="pageTitle">概览</h1><div class="admin-user"><?= e($_SESSION['admin_user'] ?? '') ?></div></header><?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?><?php if ($ok): ?><div class="alert ok"><?= e($ok) ?></div><?php endif; ?>

<div class="admin-page active" id="page-overview">
  <div class="metric-grid">
    <article class="metric-card"><span>搜索</span><strong><?= bool_setting($settings,'search_enabled')?'启用':'关闭' ?></strong></article>
    <article class="metric-card"><span>代理</span><strong><?= bool_setting($settings,'proxy_enabled')?'启用':'关闭' ?></strong></article>
    <article class="metric-card"><span>今日搜索</span><strong><?= e((string)($stats['today_searches'] ?? 0)) ?></strong></article>
    <article class="metric-card"><span>今日访客</span><strong><?= e((string)($stats['today_search_ips'] ?? 0)) ?></strong></article>
  </div>
</div>

<div class="admin-page" id="page-stats">
  <div class="metric-grid">
    <article class="metric-card"><span>总搜索</span><strong><?= e((string)($stats['total_searches'] ?? 0)) ?></strong></article>
    <article class="metric-card"><span>今日搜索</span><strong><?= e((string)($stats['today_searches'] ?? 0)) ?></strong></article>
    <article class="metric-card"><span>总独立 IP</span><strong><?= e((string)($stats['total_search_ips'] ?? 0)) ?></strong></article>
    <article class="metric-card"><span>今日独立 IP</span><strong><?= e((string)($stats['today_search_ips'] ?? 0)) ?></strong></article>
    <article class="metric-card"><span>代理总访问</span><strong><?= e((string)($stats['total_proxy_views'] ?? 0)) ?></strong></article>
    <article class="metric-card"><span>今日代理访问</span><strong><?= e((string)($stats['today_proxy_views'] ?? 0)) ?></strong></article>
  </div>
  <div class="stats-panels">
    <div class="panel"><div class="panel-head"><h2>最近 7 天搜索趋势</h2></div><div class="mini-bars"><?php $max=max(1, max($stats['search_trend'] ?? [0])); foreach(($stats['search_trend'] ?? []) as $day=>$total): ?><div class="mini-bar-item"><div class="mini-bar" style="height:<?= max(8, (int)round(((int)$total / $max) * 100)) ?>%"></div><span><?= e(substr($day,5)) ?></span><strong><?= (int)$total ?></strong></div><?php endforeach; ?></div></div>
    <div class="panel"><div class="panel-head"><h2>热门搜索词</h2><div class="stat-switch"><button type="button" class="active" data-keyword-range="total">总搜索</button><button type="button" data-keyword-range="today">今日搜索</button><button type="button" data-keyword-range="7d">近7天搜索</button></div></div><?php $keywordSets=['total'=>$stats['top_keywords'] ?? [], 'today'=>$stats['top_keywords_today'] ?? [], '7d'=>$stats['top_keywords_7d'] ?? []]; foreach($keywordSets as $range=>$rows): $maxKw=max(1, ...array_map(fn($r)=>(int)$r['total'], $rows ?: [['total'=>0]])); ?><div class="bar-list keyword-list <?= $range==='total'?'active':'' ?>" data-keyword-list="<?= e($range) ?>"><?php foreach($rows as $row): ?><div class="bar-row"><span><?= e($row['keyword']) ?></span><div class="bar-track"><i style="width:<?= (int)round(((int)$row['total'] / $maxKw) * 100) ?>%"></i></div><b><?= (int)$row['total'] ?></b></div><?php endforeach; ?><?php if (!$rows): ?><p class="muted">暂无数据</p><?php endif; ?></div><?php endforeach; ?></div>
    <div class="panel"><div class="panel-head"><h2>广告展示排行</h2></div><div class="bar-list"><?php $maxAd=max(1, ...array_map(fn($r)=>(int)$r['views'], $stats['top_ads'] ?: [['views'=>0]])); foreach(($stats['top_ads'] ?? []) as $row): ?><div class="bar-row"><span><?= e(($row['title'] ?: $row['pool_key'])) ?></span><div class="bar-track"><i style="width:<?= (int)round(((int)$row['views'] / $maxAd) * 100) ?>%"></i></div><b><?= (int)$row['views'] ?></b></div><?php endforeach; ?><?php if (empty($stats['top_ads'])): ?><p class="muted">暂无数据</p><?php endif; ?></div></div>
  </div>
</div>

<div class="admin-page" id="page-search"><form method="post"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="search-settings"><div class="panel"><div class="panel-head"><div><h2>站点信息 / SEO 设置</h2><p class="muted">这里统一管理前台显示文字和页面 TDK；可用变量：{site_name}=站点名称，{q}=搜索关键词。</p></div><label class="switch"><input type="checkbox" name="search_enabled" <?= bool_setting($settings,'search_enabled')?'checked':'' ?>><span></span>启用搜索</label></div><div class="form-grid"><div class="form-section-title span-2"><strong>基础展示</strong><small>控制首页品牌名、搜索框下方提示和页脚文字。</small></div><label>站点名称<input name="site_name" value="<?= e($settings['site_name']) ?>" placeholder="Search"></label><label>首页提示文字<input name="site_notice" value="<?= e($settings['site_notice']) ?>" placeholder="仅限内部人员使用。"></label><label class="span-2">底部文字<input name="footer_notice" value="<?= e($settings['footer_notice']) ?>" placeholder="请用于正规用途"></label><div class="form-section-title span-2"><strong>首页 TDK</strong><small>未搜索时首页输出的标题、描述和关键词。</small></div><label class="span-2">首页 Title<input name="home_title" value="<?= e($settings['home_title']) ?>" placeholder="{site_name}"></label><label class="span-2">首页 Description<textarea name="home_description" rows="2" placeholder="内部搜索系统"><?= e($settings['home_description']) ?></textarea></label><label class="span-2">首页 Keywords<input name="home_keywords" value="<?= e($settings['home_keywords']) ?>" placeholder="搜索,内部搜索"></label><div class="form-section-title span-2"><strong>搜索结果页 TDK</strong><small>搜索结果页会把 {q} 自动替换成用户搜索词。</small></div><label class="span-2">结果页 Title 模板<input name="result_title" value="<?= e($settings['result_title']) ?>" placeholder="{q} - {site_name}"></label><label class="span-2">结果页 Description 模板<textarea name="result_description" rows="2" placeholder="{q} 的搜索结果"><?= e($settings['result_description']) ?></textarea></label><label class="span-2">结果页 Keywords 模板<input name="result_keywords" value="<?= e($settings['result_keywords']) ?>" placeholder="{q},{site_name}"></label><div class="form-section-title span-2"><strong>搜索参数</strong><small>超时时间、频率限制等高级设置。</small></div><label>超时时间(秒)<input type="number" name="timeout" min="5" max="60" value="<?= e($settings['timeout']) ?>"></label><label>频率限制(秒)<input type="number" name="rate_limit_seconds" min="0" max="30" value="<?= e($settings['rate_limit_seconds']) ?>"></label><label>翻页倒计时(秒)<input type="number" name="pager_countdown_seconds" min="1" max="300" value="<?= e($settings['pager_countdown_seconds']) ?>"></label><label>跳转倒计时(秒)<input type="number" name="redirect_countdown_seconds" min="1" max="300" value="<?= e($settings['redirect_countdown_seconds']) ?>"></label><label class="span-2">搜索引擎域名<input name="google_domain" value="<?= e($settings['google_domain']) ?>" placeholder="https://www.google.com"><small style="color:#0066cc">💡 支持：Google(默认)、DuckDuckGo(https://duckduckgo.com)、StartPage(https://www.startpage.com)、本地Searxng(http://127.0.0.1:8888)</small></label></div><div class="panel-foot"><button type="submit">保存</button></div></div></form></div>

<div class="admin-page" id="page-proxy"><form method="post"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="proxy-settings"><div class="panel"><div class="panel-head"><h2>代理配置</h2><div class="switches"><label class="switch"><input type="checkbox" name="proxy_enabled" <?= bool_setting($settings,'proxy_enabled')?'checked':'' ?>><span></span>搜索代理</label><label class="switch"><input type="checkbox" name="page_proxy_enabled" <?= bool_setting($settings,'page_proxy_enabled')?'checked':'' ?>><span></span>页面代理</label></div></div><div class="form-grid"><label>代理类型<select name="proxy_type"><?php foreach(['http'=>'HTTP','https'=>'HTTPS','socks4'=>'SOCKS4','socks5'=>'SOCKS5'] as $k=>$v): ?><option value="<?=e($k)?>" <?=$settings['proxy_type']===$k?'selected':''?>><?=e($v)?></option><?php endforeach; ?></select></label><label>代理模式<select name="proxy_mode"><option value="rotate" <?= ($settings['proxy_mode'] ?? 'rotate') === 'rotate' ? 'selected' : '' ?>>轮询模式（依次使用每条代理）</option><option value="single" <?= ($settings['proxy_mode'] ?? '') === 'single' ? 'selected' : '' ?>>单一模式（固定使用第一条）</option></select><small style="color:#666">轮询=每次请求自动切换代理；单一=始终使用第一条代理</small></label><label>轮换间隔(秒)<input type="number" name="proxy_rotate_seconds" min="30" max="3600" value="<?= e($settings['proxy_rotate_seconds']) ?>"></label><label>缓存(秒)<input type="number" name="cache_seconds" min="0" max="3600" value="<?= e($settings['cache_seconds']) ?>"></label><label class="span-2">代理端口池<small style="color:#0066cc">💡 使用方式：<span id="proxyModeHint"><?= ($settings['proxy_mode'] ?? 'rotate') === 'rotate' ? '每次搜索请求按顺序轮询使用代理（第1条→第2条→...→第1条循环）' : '固定使用列表中的第一条代理' ?></span><br>格式：每行一个，支持 host:port 或 host:port:user:pass</small><textarea name="proxy_ports" rows="8" placeholder="1.2.3.4:10001&#10;1.2.3.4:10002:user:pass"><?= e($settings['proxy_ports']) ?></textarea></label></div><div class="panel-foot"><button type="submit">保存</button></div></div></form></div>

<div class="admin-page" id="page-ad">
<div class="panel"><div class="panel-head"><h2>广告配置</h2><p class="muted">代码广告会原样输出到前台，请仅粘贴可信广告平台代码。</p></div>
<div class="admin-tabs"><?php $firstAdTab=true; foreach ($poolKeys as $key => $label): ?><button type="button" class="admin-tab <?= $firstAdTab?'active':'' ?>" data-ad-tab="<?= e($key) ?>"><?= e($label) ?></button><?php $firstAdTab=false; endforeach; ?></div>
<?php $firstAdPanel=true; foreach ($poolKeys as $key => $label): $poolAds = array_values(array_filter($allAds, fn($a) => $a['pool_key'] === $key)); ?>
  <div class="admin-tab-panel <?= $firstAdPanel?'active':'' ?>" data-ad-panel="<?= e($key) ?>"><div class="panel-head"><h2><?= e($label) ?></h2><span class="pool-count"><?= count($poolAds) ?> 个</span></div>
  <?php foreach ($poolAds as $ad): ?>
    <div class="ad-item ad-item-wide">
      <?php if (($ad['ad_type'] ?? 'image') === 'code'): ?><span class="ad-noimg">代码</span><?php elseif ($ad['image_url']): ?><img src="<?= e($ad['image_url']) ?>" alt=""><?php else: ?><span class="ad-noimg">无图</span><?php endif; ?>
      <div class="ad-info"><div><span class="ad-type-badge"><?= (($ad['ad_type'] ?? 'image') === 'code') ? '代码' : '图片' ?></span><?= e($ad['title'] ?: '无标题') ?></div><div class="ad-url"><?= (($ad['ad_type'] ?? 'image') === 'code') ? e(mb_substr(trim((string)($ad['embed_code'] ?? '')),0,80)) : e($ad['link_url'] ?: '无链接') ?></div></div>
      <small>浏览<?= (int)$ad['views'] ?></small>
      <form method="post" onsubmit="return confirm('删除？')"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="ad-delete"><input type="hidden" name="ad_id" value="<?= (int)$ad['id'] ?>"><button class="secondary" style="background:#fee2e2;color:#b91c1c">删</button></form>
    </div>
  <?php endforeach; ?>
  <form method="post" enctype="multipart/form-data" class="ad-add-form ad-tab-form"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="ad-save"><input type="hidden" name="ad_pool" value="<?= e($key) ?>"><label>广告类型<select name="ad_type"><option value="image">图片广告</option><option value="code">代码嵌入</option></select></label><label>标题<input type="text" name="ad_title" placeholder="标题（可选）"></label><label>跳转链接<input type="text" name="ad_link" placeholder="https://..."></label><label>图片上传<input type="file" name="ad_image_file" accept=".jpg,.jpeg,.png,.gif,.webp"></label><label class="span-2">嵌入代码<textarea name="ad_embed_code" rows="4" placeholder="粘贴广告联盟代码 / iframe / script"></textarea></label><div class="ad-add-actions"><input type="number" name="ad_sort" value="0" min="0" placeholder="排序"><label class="switch"><input type="checkbox" name="ad_enabled" checked><span></span>启用</label><button type="submit">添加广告</button></div></form>
  </div>
<?php $firstAdPanel=false; endforeach; ?></div></div>

<div class="admin-page" id="page-access"><form method="post"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="access-settings"><div class="panel"><div class="panel-head"><h2>访问控制</h2><label class="switch"><input type="checkbox" name="ip_allowlist_enabled" <?= bool_setting($settings,'ip_allowlist_enabled')?'checked':'' ?>><span></span>白名单</label></div><div class="form-grid"><label class="span-2">IP 白名单<textarea name="ip_allowlist" rows="3" placeholder="10.0.0.0/8"><?= e($settings['ip_allowlist']) ?></textarea></label><label>当前 IP<input value="<?= e(client_ip()) ?>" readonly></label><label class="span-2">禁止关键词<small style="color:#0066cc">💡 匹配规则：自动去除空格后匹配。如设置禁用"A B"，则"AB"、"A B"、"A  B"都会被拦截<br>支持逗号、换行分隔多个关键词</small><textarea name="blocked_keywords" rows="3" placeholder="@,#,你好"><?= e($settings['blocked_keywords']) ?></textarea></label></div><div class="panel-foot"><button type="submit">保存</button></div></div></form></div>

<div class="admin-page" id="page-tools"><div class="panel"><div class="panel-head"><h2>维护工具</h2></div><div class="tools-grid"><form method="post" class="tools-item"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="test_search"><button class="secondary">测试搜索连通性</button></form><form method="post" class="tools-item"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="password"><input type="password" name="current_admin_password" placeholder="当前密码" required><input type="password" name="new_admin_password" minlength="8" placeholder="新密码" required><button class="secondary">改密码</button></form></div></div></div>

<div class="admin-page" id="page-logs">
<div class="panel"><div class="panel-head"><h2>日志管理 <small style="font-weight:normal;color:#666">(搜索代理: <?= $totalLogs ?> 条 | 页面代理: <?= $totalProxyLogs ?> 条)</small></h2><form method="post" id="clearLogsForm" style="display:flex;gap:8px"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="clear_logs"><select name="clear_type" id="clearTypeSelect" class="secondary" style="padding:6px 10px" required><option value="">-- 选择要清空的日志 --</option><option value="search">清空搜索代理日志 (<?= $totalLogs ?> 条)</option><option value="proxy">清空页面代理日志 (<?= $totalProxyLogs ?> 条)</option><option value="all">清空所有日志</option></select><button class="secondary" type="submit" onclick="return confirmClear()">清空日志</button></form></div></div>

<div class="panel">
  <div class="panel-head"><h3>搜索代理日志 <small style="font-weight:normal;color:#666">(每页10条)</small></h3></div>
  <div class="table-wrap">
    <table><thead><tr><th>时间</th><th>访客IP</th><th>关键词</th><th>线路</th><th>代理IP</th><th>搜索引擎</th><th>结果数</th><th>状态</th></tr></thead>
    <tbody><?php foreach($logs as $log): ?><tr><td><?=e($log['created_at'])?></td><td><?=e($log['ip'])?></td><td><?=e($log['keyword'])?></td><td><span class="route-badge <?= search_log_route_label($log)==='代理'?'proxy':'direct' ?>"><?= e(search_log_route_label($log)) ?></span></td><td><?=e($log['proxy_ip'] ?: '-')?></td><td><?=e($log['search_source'] ?: '-')?></td><td><?=e((string)$log['result_count'])?></td><td><?=e($log['status'])?></td></tr><?php endforeach; ?><?php if (!$logs): ?><tr><td colspan="8" class="muted">暂无日志</td></tr><?php endif; ?></tbody>
    </table>
  </div>
  <div class="panel-foot" style="display:flex;justify-content:center;gap:10px;align-items:center">
    <span style="color:#666">第 <?= $logsPage ?> / <?= $totalLogsPages ?> 页 (共 <?= $totalLogs ?> 条)</span>
    <div style="display:flex;gap:5px">
      <?php if ($logsPage > 1): ?><a href="?logs_page=<?= $logsPage - 1 ?>&proxy_logs_page=<?= $proxyLogsPage ?>#logs" class="button secondary">上一页</a><?php endif; ?>
      <?php for ($p = max(1, $logsPage - 2); $p <= min($totalLogsPages, $logsPage + 2); $p++): ?>
        <?php if ($p === $logsPage): ?><span class="button" style="background:#1d4ed8;color:#fff"><?= $p ?></span><?php else: ?><a href="?logs_page=<?= $p ?>&proxy_logs_page=<?= $proxyLogsPage ?>#logs" class="button secondary"><?= $p ?></a><?php endif; ?>
      <?php endfor; ?>
      <?php if ($logsPage < $totalLogsPages): ?><a href="?logs_page=<?= $logsPage + 1 ?>&proxy_logs_page=<?= $proxyLogsPage ?>#logs" class="button secondary">下一页</a><?php endif; ?>
    </div>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h3>页面代理日志 <small style="font-weight:normal;color:#666">(每页10条)</small></h3></div>
  <div class="table-wrap">
    <table><thead><tr><th>时间</th><th>访客IP</th><th>目标URL</th><th>状态码</th></tr></thead>
    <tbody><?php foreach($proxyLogs as $log): ?><tr><td><?=e($log['created_at'])?></td><td><?=e($log['ip'])?></td><td title="<?=e($log['target_url'])?>"><?=e(mb_substr($log['target_url'], 0, 60))?><?=mb_strlen($log['target_url']) > 60 ? '...' : ''?></td><td><?=e((string)$log['status_code'])?></td></tr><?php endforeach; ?><?php if (!$proxyLogs): ?><tr><td colspan="4" class="muted">暂无日志</td></tr><?php endif; ?></tbody>
    </table>
  </div>
  <div class="panel-foot" style="display:flex;justify-content:center;gap:10px;align-items:center">
    <span style="color:#666">第 <?= $proxyLogsPage ?> / <?= $totalProxyLogsPages ?> 页 (共 <?= $totalProxyLogs ?> 条)</span>
    <div style="display:flex;gap:5px">
      <?php if ($proxyLogsPage > 1): ?><a href="?logs_page=<?= $logsPage ?>&proxy_logs_page=<?= $proxyLogsPage - 1 ?>#logs" class="button secondary">上一页</a><?php endif; ?>
      <?php for ($p = max(1, $proxyLogsPage - 2); $p <= min($totalProxyLogsPages, $proxyLogsPage + 2); $p++): ?>
        <?php if ($p === $proxyLogsPage): ?><span class="button" style="background:#1d4ed8;color:#fff"><?= $p ?></span><?php else: ?><a href="?logs_page=<?= $logsPage ?>&proxy_logs_page=<?= $p ?>#logs" class="button secondary"><?= $p ?></a><?php endif; ?>
      <?php endfor; ?>
      <?php if ($proxyLogsPage < $totalProxyLogsPages): ?><a href="?logs_page=<?= $logsPage ?>&proxy_logs_page=<?= $proxyLogsPage + 1 ?>#logs" class="button secondary">下一页</a><?php endif; ?>
    </div>
  </div>
</div>
</div>

</main></div>
<script>
function confirmClear() {
    var type = document.getElementById('clearTypeSelect').value;
    var messages = {
        'search': '确定清空搜索代理日志？',
        'proxy': '确定清空页面代理日志？',
        'all': '确定清空所有日志？此操作不可恢复！'
    };
    return confirm(messages[type] || '确定清空日志？');
}
document.addEventListener('DOMContentLoaded',function(){
    var proxyModeSelect = document.querySelector('select[name="proxy_mode"]');
    var proxyModeHint = document.getElementById('proxyModeHint');
    if (proxyModeSelect && proxyModeHint) {
        proxyModeSelect.addEventListener('change', function() {
            proxyModeHint.textContent = (this.value === 'single') ? '固定使用列表中的第一条代理' : '每次搜索请求按顺序轮询使用代理（第1条→第2条→...→第1条循环）';
        });
    }
    var pages={overview:'概览',stats:'统计',search:'搜索',proxy:'代理',ad:'广告',access:'访问',tools:'工具',logs:'日志'};
    var sidebar=document.getElementById('adminSidebar'),backdrop=document.getElementById('sidebarBackdrop'),hamburger=document.getElementById('hamburger'),titleEl=document.getElementById('pageTitle'),navs=document.querySelectorAll('.side-nav a[data-page]'),pageEls=document.querySelectorAll('.admin-page');
    if(!sidebar||!backdrop||!hamburger||!titleEl||!navs.length||!pageEls.length)return;
    function closeSidebar(){sidebar.classList.remove('open');backdrop.classList.remove('open');hamburger.classList.remove('open');}
    function showPage(name){navs.forEach(function(a){a.classList.toggle('active',a.dataset.page===name)});pageEls.forEach(function(p){p.classList.toggle('active',p.id==='page-'+name)});if(pages[name])titleEl.textContent=pages[name];if(window.innerWidth<=900)closeSidebar();window.location.hash=name;}
    hamburger.addEventListener('click',function(){sidebar.classList.toggle('open');backdrop.classList.toggle('open');hamburger.classList.toggle('open');});
    backdrop.addEventListener('click',closeSidebar);
    navs.forEach(function(a){a.addEventListener('click',function(e){e.preventDefault();showPage(this.dataset.page)});});
    document.querySelectorAll('.admin-tab[data-ad-tab]').forEach(function(tab){tab.addEventListener('click',function(){var name=this.dataset.adTab;document.querySelectorAll('.admin-tab[data-ad-tab]').forEach(function(t){t.classList.toggle('active',t.dataset.adTab===name)});document.querySelectorAll('.admin-tab-panel[data-ad-panel]').forEach(function(p){p.classList.toggle('active',p.dataset.adPanel===name)});});});
    document.querySelectorAll('[data-keyword-range]').forEach(function(btn){btn.addEventListener('click',function(){var range=this.dataset.keywordRange;document.querySelectorAll('[data-keyword-range]').forEach(function(b){b.classList.toggle('active',b.dataset.keywordRange===range)});document.querySelectorAll('[data-keyword-list]').forEach(function(list){list.classList.toggle('active',list.dataset.keywordList===range)});});});
    var h=window.location.hash.replace('#','');if(!h||!pages[h])h='overview';showPage(h);
});
</script></body></html>
