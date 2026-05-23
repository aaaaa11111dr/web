<?php
require __DIR__ . '/app/bootstrap.php';

$pdo = pdo();
$hash = password_hash('rootroot', PASSWORD_DEFAULT);
$stmt = $pdo->prepare('UPDATE `admins` SET `password_hash` = ?, `updated_at` = ? WHERE `username` = ?');
$stmt->execute([$hash, now(), 'admin']);

echo "管理员密码已更新为: rootroot\n";
echo "请访问: /goolehome.php\n";
echo "账号: admin\n";
echo "密码: rootroot\n";
