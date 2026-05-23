<?php
require __DIR__ . '/app/bootstrap.php';

// 删除旧的数据库文件
$sqliteFile = APP_ROOT . '/storage/database.sqlite';
if (file_exists($sqliteFile)) {
    unlink($sqliteFile);
}

try {
    // 初始化数据库连接
    $pdo = pdo();

    // 创建管理员表
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(64) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL
    )");

    // 创建设置表
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        name VARCHAR(80) PRIMARY KEY,
        value TEXT,
        updated_at DATETIME NOT NULL
    )");

    // 创建搜索日志表
    $pdo->exec("CREATE TABLE IF NOT EXISTS search_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        keyword VARCHAR(200) NOT NULL,
        ip VARCHAR(64) NOT NULL,
        user_agent VARCHAR(255),
        result_count INTEGER NOT NULL DEFAULT 0,
        status VARCHAR(24) NOT NULL DEFAULT 'ok',
        message VARCHAR(255),
        proxy_ip VARCHAR(255),
        search_source VARCHAR(50),
        created_at DATETIME NOT NULL
    )");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_search_logs_created_at ON search_logs(created_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_search_logs_ip ON search_logs(ip)");

    // 创建页面代理日志表
    $pdo->exec("CREATE TABLE IF NOT EXISTS page_proxy_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        target_url VARCHAR(800) NOT NULL,
        ip VARCHAR(64) NOT NULL,
        status_code INTEGER NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL
    )");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_page_proxy_logs_created_at ON page_proxy_logs(created_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_page_proxy_logs_ip ON page_proxy_logs(ip)");

    // 创建广告池表
    $pdo->exec("CREATE TABLE IF NOT EXISTS ad_pool (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        pool_key VARCHAR(40) NOT NULL,
        title VARCHAR(120),
        image_url VARCHAR(500) NOT NULL DEFAULT '',
        link_url VARCHAR(500) NOT NULL DEFAULT '',
        ad_type VARCHAR(20) NOT NULL DEFAULT 'image',
        embed_code TEXT,
        sort_order INTEGER NOT NULL DEFAULT 0,
        enabled INTEGER NOT NULL DEFAULT 1,
        views INTEGER NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL,
        updated_at DATETIME
    )");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_ad_pool_pool_key ON ad_pool(pool_key)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_ad_pool_enabled ON ad_pool(enabled)");

    // 创建默认管理员账号（用户名：admin，密码：rootroot）
    $adminUser = 'admin';
    $adminPass = 'rootroot';
    $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash, created_at, updated_at) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $adminUser,
        password_hash($adminPass, PASSWORD_DEFAULT),
        now(),
        now()
    ]);

    // 保存默认设置
    $settings = default_settings();
    $settings['site_name'] = '内部搜索系统';
    foreach ($settings as $name => $value) {
        $stmt = $pdo->prepare("INSERT INTO settings (name, value, updated_at) VALUES (?, ?, ?)");
        $stmt->execute([$name, $value, now()]);
    }

    // 写入安装标记文件
    $dbConfig = [
        'type' => 'sqlite',
        'path' => $sqliteFile,
        'installed_at' => date('c')
    ];
    file_put_contents(DB_FILE, "<?php\nreturn " . var_export($dbConfig, true) . ";\n");

    echo "✅ 系统安装成功！\n";
    echo "管理员账号: admin\n";
    echo "管理员密码: rootroot\n";
    echo "数据库: SQLite ({$sqliteFile})\n";
    echo "\n";
    echo "现在可以访问 http://localhost:8000 来使用系统\n";
    echo "管理面板: http://localhost:8000/goolehome.php\n";

} catch (Throwable $e) {
    echo "❌ 安装失败: " . $e->getMessage() . "\n";
    if (file_exists($sqliteFile)) {
        unlink($sqliteFile);
    }
    if (file_exists(DB_FILE)) {
        unlink(DB_FILE);
    }
    exit(1);
}
