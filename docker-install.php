<?php
/**
 * Docker 自动安装脚本
 * 在 Docker 容器启动时自动配置数据库
 */

require __DIR__ . '/app/bootstrap.php';

// 从环境变量获取数据库配置
$dbConfig = [
    'host' => getenv('DB_HOST') ?: 'db',
    'port' => (int)(getenv('DB_PORT') ?: 3306),
    'database' => getenv('DB_NAME') ?: 'wow_search',
    'username' => getenv('DB_USER') ?: 'search_user',
    'password' => getenv('DB_PASSWORD') ?: 'search_pass123',
    'installed_at' => date('c'),
];

// 写入数据库配置文件
write_db_config($dbConfig);

echo "数据库配置已写入\n";

// 等待 MySQL 启动
echo "等待 MySQL 数据库启动...\n";
$maxAttempts = 30;
$attempt = 0;
$pdo = null;

while ($attempt < $maxAttempts) {
    try {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $dbConfig['host'],
            $dbConfig['port'],
            $dbConfig['database']
        );
        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        echo "成功连接到数据库！\n";
        break;
    } catch (PDOException $e) {
        $attempt++;
        echo "连接尝试 {$attempt}/{$maxAttempts} 失败: " . $e->getMessage() . "\n";
        sleep(2);
    }
}

if (!$pdo) {
    die("无法连接到数据库，放弃安装\n");
}

// 检查是否已安装
try {
    // 尝试查询 admins 表
    $stmt = $pdo->query("SELECT COUNT(*) FROM admins");
    $count = $stmt->fetchColumn();
    if ($count > 0) {
        echo "系统已经安装，管理员数: {$count}\n";
        exit(0);
    }
} catch (PDOException $e) {
    // 表不存在，需要安装
    echo "开始安装系统...\n";
}

// 运行安装 SQL
try {
    run_install_sql($pdo);
    echo "数据库表创建成功\n";
} catch (Throwable $e) {
    die("安装失败: " . $e->getMessage() . "\n");
}

// 创建管理员账户
$adminUser = 'admin';
$adminPass = 'rootroot';

try {
    $stmt = $pdo->prepare('INSERT INTO `admins` (`username`, `password_hash`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?)');
    $stmt->execute([$adminUser, password_hash($adminPass, PASSWORD_DEFAULT), now(), now()]);
    echo "管理员账户创建成功！\n";
    echo "用户名: {$adminUser}\n";
    echo "密码: {$adminPass}\n";
} catch (PDOException $e) {
    die("创建管理员账户失败: " . $e->getMessage() . "\n");
}

// 保存默认设置
try {
    $settings = default_settings();
    save_settings($settings);
    echo "默认设置保存成功\n";
} catch (Throwable $e) {
    die("保存默认设置失败: " . $e->getMessage() . "\n");
}

echo "\n✅ 系统安装完成！\n";
echo "现在可以访问 http://localhost:8000 来使用系统\n";
echo "管理面板: http://localhost:8000/goolehome.php\n";
