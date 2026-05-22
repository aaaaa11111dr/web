<?php
require __DIR__ . '/app/bootstrap.php';
$error = '';
if (installed()) redirect('/goolehome.php');
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
        $adminId = (int)$pdo->lastInsertId();
        $settings = default_settings();
        $settings['site_name'] = trim((string)($_POST['site_name'] ?? 'Search')) ?: 'Search';
        save_settings($settings);
        $_SESSION['admin_id'] = $adminId;
        $_SESSION['admin_user'] = $adminUser;
        redirect('/goolehome.php');
    } catch (Throwable $e) {
        if (is_file(DB_FILE)) @unlink(DB_FILE);
        $error = $e->getMessage();
    }
}
?>
<!doctype html><html lang="zh-CN"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>安装 - <?= e(APP_NAME) ?></title><link rel="stylesheet" href="/assets/style.css?v=2026051401"></head><body><div class="shell admin-shell"><main class="center-card wide"><h1>安装内部搜索系统</h1><p>当前版本只支持 MySQL。安装器会导入数据表并创建管理员账号；如需限制访问，可安装后在后台开启 IP 白名单。</p><?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?><form method="post" class="settings-form"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><div class="form-grid"><label>数据库主机<input name="db_host" value="127.0.0.1" required></label><label>端口<input type="number" name="db_port" value="3306" required></label><label>数据库名<input name="db_name" placeholder="wow_search" required></label><label>数据库账号<input name="db_user" required></label><label>数据库密码<input type="password" name="db_pass"></label><label>站点名称<input name="site_name" value="Search" required></label><label>管理员账号<input name="admin_user" value="admin" required></label><label>管理员密码<input type="password" name="admin_pass" minlength="8" required></label></div><div class="actions"><button type="submit">开始安装</button></div></form></main></div></body></html>
