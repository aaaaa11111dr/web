<?php
require __DIR__ . '/app/bootstrap.php';

// 删除旧的配置文件
if (file_exists(DB_FILE)) {
    unlink(DB_FILE);
}

// 数据库配置
$db = [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'wow_search',
    'username' => 'search_user',
    'password' => 'search_pass123',
];

$adminUser = 'admin';
$adminPass = 'Admin1234';
$siteName = '内部搜索系统';

try {
    // 连接并创建数据库
    $dsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $db['host'], $db['port']);
    $pdo = new PDO($dsn, $db['username'], $db['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '``', $db['database']) . '` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    // 写入配置
    write_db_config($db);
    $pdo = pdo();

    // 安装数据库表
    run_install_sql($pdo);

    // 创建管理员
    $stmt = $pdo->prepare('INSERT INTO `admins` (`username`, `password_hash`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?)');
    $stmt->execute([$adminUser, password_hash($adminPass, PASSWORD_DEFAULT), now(), now()]);
    $adminId = (int)$pdo->lastInsertId();

    // 保存默认设置
    $settings = default_settings();
    $settings['site_name'] = $siteName;
    save_settings($settings);

    echo "系统安装成功！\n\n";
    echo "管理员账号: $adminUser\n";
    echo "管理员密码: $adminPass\n";
    echo "数据库名: " . $db['database'] . "\n";
    echo "数据库用户: " . $db['username'] . "\n";
    echo "数据库密码: " . $db['password'] . "\n";

} catch (Throwable $e) {
    echo "错误: " . $e->getMessage() . "\n";
    if (file_exists(DB_FILE)) {
        unlink(DB_FILE);
    }
    exit(1);
}
