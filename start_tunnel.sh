#!/bin/bash

# 启动 localtunnel
lt --port 8000 > /workspace/tunnel.log 2>&1 &
TUNNEL_PID=$!

# 等待启动
sleep 5

# 获取 URL
echo "正在获取公网地址..."
URL=$(grep -o "https://.*\.loca\.lt" /workspace/tunnel.log | head -1)

if [ -n "$URL" ]; then
    echo "========================================"
    echo "✅ 公网访问地址："
    echo "$URL"
    echo "========================================"
    echo "管理面板：$URL/goolehome.php"
    echo "========================================"
    echo "Tunnel PID: $TUNNEL_PID"
    echo "日志文件: /workspace/tunnel.log"
    echo ""
    echo "注意：首次访问可能需要在浏览器中点击按钮验证"
else
    echo "❌ 无法获取地址，尝试另一种方式..."
    # 尝试使用 python 的方式
    cat > /workspace/get_url.py << 'EOF'
import time
import subprocess
import re

# 启动 localtunnel
process = subprocess.Popen(['lt', '--port', '8000'], 
                          stdout=subprocess.PIPE, 
                          stderr=subprocess.STDOUT,
                          text=True)

# 等待并读取输出
time.sleep(3)
for line in process.stdout:
    print(line, end='')
    match = re.search(r'https?://[^\s]+loca\.lt', line)
    if match:
        print(f"\n{'='*50}")
        print(f"✅ 公网访问地址：{match.group()}")
        print(f"管理面板：{match.group()}/goolehome.php")
        print(f"{'='*50}")
        break
EOF
    
    python3 /workspace/get_url.py &
    PY_PID=$!
    sleep 8
    kill $PY_PID 2>/dev/null
fi
