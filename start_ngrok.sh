#!/bin/bash

# 启动 ngrok 隧道
ngrok http 8000 --log=stdout > /workspace/ngrok.log 2>&1 &
NGROK_PID=$!

# 等待 ngrok 启动
sleep 5

# 获取公网 URL
echo "正在获取公网地址..."
for i in {1..10}; do
    URL=$(curl -s http://127.0.0.1:4040/api/tunnels | python3 -c "import sys, json; data=json.load(sys.stdin); print(data['tunnels'][0]['public_url']) if data.get('tunnels') else exit(1)" 2>/dev/null)
    if [ -n "$URL" ]; then
        echo "========================================"
        echo "✅ 公网访问地址："
        echo "$URL"
        echo "========================================"
        echo "管理面板：$URL/goolehome.php"
        echo "========================================"
        echo "ngrok PID: $NGROK_PID"
        echo "日志文件: /workspace/ngrok.log"
        exit 0
    fi
    sleep 2
done

echo "❌ 无法获取 ngrok 地址，请检查日志："
cat /workspace/ngrok.log
