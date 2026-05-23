<?php
require __DIR__ . '/app/bootstrap.php';

// 如果没有安装，直接显示安装界面
if (!installed()) {
    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_csrf();
        try {
            $db = [
                'host' => trim((string)($_POST['db_host'] ?? '127.0.0.1')),
                'port' => (int)($_POST['db_port'] ?? 3306),
                'database' => trim((string)($_POST['db_name'] ?? '')),
                'username' => trim((string)($_POST['db_user'] ?? '')),
                'password' => (string)($_POST['db_pass'] ?? ''),
            ];
            $adminUser = trim((string)($_POST['admin_user'] ?? 'admin')) ?: 'admin';
            $adminPass = (string)($_POST['admin_pass'] ?? '');
            if ($db['database'] === '' || $db['username'] === '') throw new RuntimeException('请填写数据库名和数据库账号。');
            if (strlen($adminPass) < 8) throw new RuntimeException('管理员密码至少 8 位。');

            $dsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $db['host'], $db['port']);
            $tmp = new PDO($dsn, $db['username'], $db['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $tmp->exec('CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '``', $db['database']) . '` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            write_db_config($db);
            $pdo = pdo();
            run_install_sql($pdo);
            $stmt = $pdo->prepare('INSERT INTO `admins` (`username`, `password_hash`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?)');
            $stmt->execute([$adminUser, password_hash($adminPass, PASSWORD_DEFAULT), now(), now()]);
            $_SESSION['admin_id'] = (int)$pdo->lastInsertId();
            $_SESSION['admin_user'] = $adminUser;
            redirect('/');
        } catch (Throwable $e) {
            if (is_file(DB_FILE)) @unlink(DB_FILE);
            $error = $e->getMessage();
        }
    }
    ?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>安装 - <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="/assets/style.css?v=2026051401">
    <style>
        .install-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .install-title {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .install-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .form-group label {
            font-weight: bold;
            color: #555;
            font-size: 14px;
        }
        .form-group input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        .submit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert.error {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        .info-text {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <h1 class="install-title">🔧 安装内部搜索系统</h1>
        <p class="info-text">当前版本只支持 MySQL。安装器会导入数据表并创建管理员账号。</p>
        
        <?php if ($error): ?>
        <div class="alert error"><?= e($error) ?></div>
        <?php endif; ?>
        
        <form method="post" class="install-form">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            
            <div class="form-group">
                <label>数据库主机</label>
                <input type="text" name="db_host" value="127.0.0.1" required>
            </div>
            
            <div class="form-group">
                <label>端口</label>
                <input type="number" name="db_port" value="3306" required>
            </div>
            
            <div class="form-group">
                <label>数据库名</label>
                <input type="text" name="db_name" placeholder="search_system" required>
            </div>
            
            <div class="form-group">
                <label>数据库账号</label>
                <input type="text" name="db_user" value="root" required>
            </div>
            
            <div class="form-group">
                <label>数据库密码</label>
                <input type="password" name="db_pass" placeholder="留空即可">
            </div>
            
            <div class="form-group">
                <label>站点名称</label>
                <input type="text" name="site_name" value="内部搜索系统" required>
            </div>
            
            <div class="form-group">
                <label>管理员账号</label>
                <input type="text" name="admin_user" value="admin" required>
            </div>
            
            <div class="form-group">
                <label>管理员密码（至少8位）</label>
                <input type="password" name="admin_pass" minlength="8" placeholder="12345678" required>
            </div>
            
            <button type="submit" class="submit-btn">🚀 开始安装</button>
        </form>
    </div>
</body>
</html>
    <?php
    exit;
}

require_install();
$settings = get_settings();
$hasAccess = has_front_access($settings);
$q = trim((string)($_GET['q'] ?? ''));
$page = max(1, min(10, (int)($_GET['page'] ?? 1)));
$data = null; $error = '';
if ($q !== '' && $hasAccess && bool_setting($settings, 'search_enabled')) {
    try { $data = perform_search($q, $settings, $page); } catch (Throwable $e) { $error = $e->getMessage(); }
}
$isResult = ($q !== '');
$pageTitle = render_tdk_template((string)($settings[$isResult ? 'result_title' : 'home_title'] ?? ''), $settings, $q) ?: ($isResult ? $q . ' - ' . $settings['site_name'] : $settings['site_name']);
$pageDescription = render_tdk_template((string)($settings[$isResult ? 'result_description' : 'home_description'] ?? ''), $settings, $q);
$pageKeywords = render_tdk_template((string)($settings[$isResult ? 'result_keywords' : 'home_keywords'] ?? ''), $settings, $q);
?>
<!doctype html><html lang="zh-CN"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= e($pageTitle) ?></title><?php if ($pageDescription !== ''): ?><meta name="description" content="<?= e($pageDescription) ?>"><?php endif; ?><?php if ($pageKeywords !== ''): ?><meta name="keywords" content="<?= e($pageKeywords) ?>"><?php endif; ?><link rel="stylesheet" href="/assets/style.css?v=2026051401"></head><body class="<?= $isResult ? 'result-body' : 'home-body' ?>"><div class="shell search-shell">
<?php if ($isResult): ?>
<header class="result-topbar">
  <a class="result-logo" href="/"><?= brand_text($settings['site_name']) ?></a>
  <form class="result-search-box" method="get" action="/"><input name="q" type="search" value="<?= e($q) ?>" maxlength="160" autofocus><button type="submit" aria-label="搜索"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg></button></form>
  <nav class="result-nav"></nav>
</header>
<main class="result-layout"><section class="result-main"><?php if ($error): ?><div class="state error"><?= e($error) ?></div><?php endif; ?>
<?php if ($data): ?>
  <?php if (!empty($data['blocked'])): ?>
    <div class="blocked-page"><div class="blocked-icon">⛔</div><h2>搜索被拦截</h2><p class="blocked-msg"><?= e($data['blocked_word']) ?>词为非法关键词，请合规使用</p></div>
  <?php elseif (!empty($data['resource_exhausted'])): ?>
    <div class="blocked-page"><div class="blocked-icon">🕐</div><h2>资源繁忙</h2><p class="blocked-msg"><?= e($data['message'] ?? '因资源耗尽，请稍等再访问') ?></p></div>
  <?php else: ?>
    <p class="result-count">约 <?= count($data['results']) ?> 条结果</p>
    <div class="results"><?php foreach ($data['results'] as $i => $r): ?>
      <article class="result-card"><a class="result-title" href="<?= e($r['open_url']) ?>" target="_blank" rel="noopener noreferrer"><?= e($r['title']) ?></a><div class="result-url"><?= e(parse_url((string)$r['url'], PHP_URL_HOST) ?: $r['display_url']) ?></div><p><?= e($r['snippet']) ?></p></article>
    <?php endforeach; ?></div>
    <nav class="pager" id="pager" data-countdown="<?= $cd = int_setting($settings, 'pager_countdown_seconds', 0, 300, 20) ?>"><?php
    $maxPage = 10;
    $perPage = 8;
    $start = max(1, min($data['page'] - (int)($perPage / 2), $maxPage - $perPage + 1));
    $end = min($maxPage, $start + $perPage - 1);
    if ($data['page'] > 1): ?><a class="button secondary pager-link" href="/?q=<?= urlencode($q) ?>&page=<?= $data['page'] - 1 ?>">‹</a><?php endif;
    for ($p = $start; $p <= $end; $p++):
      if ($p === $data['page']): ?><span class="page-current"><?= $p ?></span><?php else: ?><a class="button secondary pager-link" href="/?q=<?= urlencode($q) ?>&page=<?= $p ?>"><?= $p ?></a><?php endif;
    endfor;
    if ($data['page'] < $maxPage): ?><a class="button secondary pager-link" href="/?q=<?= urlencode($q) ?>&page=<?= $data['page'] + 1 ?>">›</a><?php endif; ?></nav>
    <?php if ($cd > 0): ?><p class="pager-cd-bar" id="pagerCdBar"><strong id="pagerCdNum"><?= $cd ?></strong> 秒后可以翻页</p><?php endif; ?>
  <?php endif; ?>
<?php elseif ($q !== ''): ?><p class="state muted">没有解析到结果，可能需要调整代理或稍后再试。</p><?php endif; ?>
</section></main>
<?php else: ?>
<header class="topbar"><div class="brand"><?= brand_text($settings['site_name']) ?></div><nav></nav></header>
<?php if (!$hasAccess): ?><main class="center-card auth-card"><div class="mark"><?= brand_text($settings['site_name']) ?></div><h1>访问受限</h1><p>当前 IP 不在白名单内，请联系管理员处理。</p></main>
<?php else: ?><main class="search-main"><section class="hero"><div class="wordmark"><?= brand_text($settings['site_name']) ?></div><p><?= e($settings['site_notice']) ?></p><form class="search-box" method="get" action="/"><input name="q" type="search" placeholder="输入关键词搜索" maxlength="160" autofocus><button type="submit" aria-label="搜索"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg></button></form></section></main><footer class="site-footer"><?= e($settings['footer_notice']) ?></footer><?php endif; ?>
<?php endif; ?>
</div><script>
(function(){
  var pager = document.getElementById('pager');
  if(!pager) return;
  var cd = parseInt(pager.dataset.countdown) || 0;
  if(cd <= 0) return;
  var links = pager.querySelectorAll('.pager-link');
  var cdNum = document.getElementById('pagerCdNum');
  var cdBar = document.getElementById('pagerCdBar');
  links.forEach(function(a){ a.classList.add('disabled'); });
  pager.addEventListener('click', function(e){
    if(e.target.classList.contains('disabled') || e.target.closest('.disabled')){
      e.preventDefault(); e.stopPropagation();
    }
  }, true);
  var t = setInterval(function(){
    cd--;
    if(cdNum) cdNum.textContent = cd;
    if(cd <= 0){
      clearInterval(t);
      if(cdBar) cdBar.innerHTML = '可点击翻页';
      links.forEach(function(a){ a.classList.remove('disabled'); });
    }
  },1000);
})();
</script></body></html>
