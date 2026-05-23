#!/bin/bash

echo "========================================"
echo "🚀 正在启动公网访问隧道..."
echo "========================================"

# 使用 cloudflared 最简单的方式
echo "使用 Cloudflare Tunnel (最快最稳定)..."

# 直接用 npx 运行
npx --yes cloudflared tunnel --url http://localhost:8000 > /workspace/cf_tunnel.log 2>&1 &
CF_PID=$!

echo "Cloudflared 已启动 (PID: $CF_PID)"
echo "正在获取公网地址，请稍候..."
sleep 8

# 尝试获取 URL
URL=$(grep -o "https://.*\.trycloudflare\.com" /workspace/cf_tunnel.log | head -1)

if [ -n "$URL" ]; then
    echo ""
    echo "========================================"
    echo "✅ 公网访问配置成功！"
    echo "========================================"
    echo "🌐 主站地址：$URL"
    echo "⚙️  管理面板：$URL/goolehome.php"
    echo "========================================"
    echo ""
    echo "📝 登录信息："
    echo "   用户名：admin"
    echo "   密码：Admin1234"
    echo "========================================"
    echo ""
    echo "📂 日志文件：/workspace/cf_tunnel.log"
    echo "🔄 隧道正在后台运行中"
    echo "========================================"
    
    # 保存 URL
    echo "$URL" > /workspace/public_url.txt
else
    echo ""
    echo "稍等，正在尝试另一种方式查看日志..."
    echo "日志内容："
    echo "----------------------------------------"
    cat /workspace/cf_tunnel.log
    echo "----------------------------------------"
    echo ""
    echo "手动查找 trycloudflare.com 开头的 URL"
fi
