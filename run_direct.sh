#!/bin/bash

echo "========================================"
echo "🚀 简易公网访问启动器"
echo "========================================"
echo ""

# 确保 PHP 服务在运行
echo "检查 PHP 服务器..."
if ! pgrep -f "php -S" > /dev/null; then
    echo "启动 PHP 服务器..."
    cd /workspace && php -S 0.0.0.0:8000 > /dev/null 2>&1 &
    echo "PHP 服务器已启动"
fi
echo ""

echo "请选择一个方式获取公网访问："
echo "1. Cloudflare Tunnel (推荐)"
echo "2. LocalTunnel"
echo ""

# 直接运行 LocalTunnel，它最简单
echo "✅ 正在启动 LocalTunnel..."
echo ""
echo "⏳ 请稍等，地址将在下方显示..."
echo "----------------------------------------"

# 直接在前台运行，让用户看到
cd /workspace
lt --port 8000
