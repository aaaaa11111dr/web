#!/bin/bash

# 等待MySQL启动
sleep 5

# 创建数据库和用户
mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS wow_search CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'search_user'@'localhost' IDENTIFIED BY 'search_pass123';
GRANT ALL PRIVILEGES ON wow_search.* TO 'search_user'@'localhost';
FLUSH PRIVILEGES;
EOF

echo "数据库和用户创建成功！"
