#!/bin/bash

# 清理所有旧的隧道进程
pkill -f cloudflared 2>/dev/null
pkill -f "lt --port" 2>/dev/null
pkill -f ngrok 2>/dev/null

echo "========================================"
echo "🚀 公网访问设置工具"
echo "========================================"
echo ""

echo "选项 1: 使用 Cloudflare Tunnel (推荐)"
echo "选项 2: 使用 LocalTunnel"
echo "选项 3: 使用 Serveo (SSH)"
echo ""

# 先尝试 Cloudflare Tunnel，这是最稳定的
echo "🔄 正在启动 Cloudflare Tunnel..."
npx --yes cloudflared tunnel --url http://localhost:8000 > /workspace/tunnel_output.log 2>&1 &
TUNNEL_PID=$!
echo "   已启动 (PID: $TUNNEL_PID)"
echo ""
echo "⏳ 等待 10 秒让连接建立..."
sleep 10

# 查看日志获取地址
echo ""
echo "📋 连接日志："
echo "----------------------------------------"
cat /workspace/tunnel_output.log
echo "----------------------------------------"
echo ""

# 尝试查找地址
URL=$(grep -o "https://.*trycloudflare.com" /workspace/tunnel_output.log | head -1)

if [ -n "$URL" ]; then
    echo ""
    echo "✅✅✅ 成功获取公网地址！"
    echo "========================================"
    echo "🌐 主站访问：$URL"
    echo "⚙️  管理后台：$URL/goolehome.php"
    echo "========================================"
    echo ""
    echo "📝 管理员登录信息："
    echo "   用户名：admin"
    echo "   密码：Admin1234"
    echo "========================================"
    echo ""
    echo "💡 使用说明："
    echo "   - 复制上面的链接在浏览器中打开"
    echo "   - 隧道正在后台运行"
    echo "   - 日志保存在：/workspace/tunnel_output.log"
    echo "========================================"
    
    # 保存地址
    echo "$URL" > /workspace/public_address.txt
else
    echo ""
    echo "⚠️  Cloudflare 未获取到地址，尝试 LocalTunnel..."
    echo ""
    
    # 启动 LocalTunnel
    lt --port 8000 > /workspace/lt_output.log 2>&1 &
    LT_PID=$!
    echo "LocalTunnel 已启动 (PID: $LT_PID)"
    sleep 8
    
    echo ""
    echo "📋 LocalTunnel 日志："
    echo "----------------------------------------"
    cat /workspace/lt_output.log
    echo "----------------------------------------"
    
    LT_URL=$(grep -o "https://.*loca.lt" /workspace/lt_output.log | head -1)
    if [ -n "$LT_URL" ]; then
        echo ""
        echo "✅ LocalTunnel 地址：$LT_URL"
        echo "管理后台：$LT_URL/goolehome.php"
        echo "登录：admin / Admin1234"
        echo "$LT_URL" > /workspace/public_address.txt
    else
        echo ""
        echo "❌ 两种方法都未获取到地址"
        echo ""
        echo "💡 请尝试手动运行以下任一命令："
        echo "   npx --yes cloudflared tunnel --url http://localhost:8000"
        echo "   或者"
        echo "   lt --port 8000"
        echo ""
        echo "然后复制显示的 https 地址"
    fi
fi
