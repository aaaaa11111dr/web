#!/bin/bash

echo "========================================"
echo "启动隧道..."
echo "========================================"

# 清理旧进程
pkill -f "lt --port" 2>/dev/null
pkill -f cloudflared 2>/dev/null
sleep 1

# 启动 LocalTunnel 并直接显示
echo "正在运行 LocalTunnel..."
echo "地址将显示在下方，请稍等..."
echo "----------------------------------------"

# 直接运行并捕获输出
cd /workspace
lt --port 8000 > lt.log 2>&1 &
LT_PID=$!
echo "进程ID: $LT_PID"

# 等待一下
sleep 8

echo ""
echo "----------------------------------------"
echo "日志内容："
echo "----------------------------------------"
cat lt.log
echo "----------------------------------------"
echo ""

# 尝试提取地址
URL=$(grep -o "https://.*loca.lt" lt.log)
if [ -n "$URL" ]; then
    echo ""
    echo "✅✅✅ 找到地址！"
    echo "========================================"
    echo "主站：$URL"
    echo "管理：$URL/goolehome.php"
    echo "========================================"
    echo "登录：admin / Admin1234"
    echo "========================================"
    echo ""
    echo "$URL" > /workspace/real_url.txt
else
    echo ""
    echo "尝试 Cloudflare..."
    npx --yes cloudflared tunnel --url http://localhost:8000 > cf.log 2>&1 &
    CF_PID=$!
    sleep 12
    
    echo ""
    echo "CF 日志："
    echo "----------------------------------------"
    cat cf.log
    echo "----------------------------------------"
    
    CF_URL=$(grep -o "https://.*trycloudflare.com" cf.log)
    if [ -n "$CF_URL" ]; then
        echo ""
        echo "✅ CF 地址：$CF_URL"
        echo "管理：$CF_URL/goolehome.php"
        echo "$CF_URL" > /workspace/real_url.txt
    fi
fi
