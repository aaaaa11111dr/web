#!/usr/bin/env python3
import subprocess
import time
import re
import sys

print("="*65)
print("🚀 使用 Serveo.net 获取公网地址")
print("="*65)
print()

# 清理旧进程
try:
    subprocess.run(['pkill', '-f', 'ssh.*serveo'], capture_output=True)
    subprocess.run(['pkill', '-f', 'lt --port'], capture_output=True)
    subprocess.run(['pkill', '-f', 'cloudflared'], capture_output=True)
except:
    pass

print("正在启动 Serveo.net SSH 隧道...")
print("等待连接...\n")

# 启动 SSH 隧道
cmd = ['ssh', '-o', 'StrictHostKeyChecking=no', '-R', '80:localhost:8000', 'serveo.net']
process = subprocess.Popen(
    cmd,
    stdout=subprocess.PIPE,
    stderr=subprocess.STDOUT,
    text=True,
    bufsize=1
)

url_found = False
timeout = 25
start_time = time.time()

while time.time() - start_time < timeout and not url_found:
    line = process.stdout.readline()
    if not line:
        time.sleep(0.5)
        continue
    
    line = line.strip()
    if line:
        print(f"[Serveo] {line}")
    
    # 查找 URL
    match = re.search(r'https?://[^\s]+\.serveo\.net', line)
    if match:
        url = match.group()
        url_found = True
        print()
        print("="*65)
        print("✅ 公网访问地址获取成功！")
        print("="*65)
        print(f"🌐 主站：{url}")
        print(f"⚙️  管理：{url}/goolehome.php")
        print("="*65)
        print()
        print("📝 登录信息：")
        print("   用户名：admin")
        print("   密码：Admin1234")
        print("="*65)
        print()
        print("💡 请复制上面的地址在浏览器中打开！")
        print("   隧道正在运行中...")
        print("="*65)
        
        with open('/workspace/final_url.txt', 'w') as f:
            f.write(url)
        break

if not url_found:
    print()
    print("⚠️  Serveo 超时，尝试最后一招 - 让我们手动运行一个简单的...")
    process.terminate()
    
    print()
    print("="*65)
    print("💡 请您手动运行以下任一命令：")
    print("="*65)
    print()
    print("1. LocalTunnel（推荐）：")
    print("   lt --port 8000")
    print()
    print("2. Cloudflare Tunnel：")
    print("   npx --yes cloudflared tunnel --url http://localhost:8000")
    print()
    print("3. Serveo.net：")
    print("   ssh -R 80:localhost:8000 serveo.net")
    print()
    print("然后复制显示的 HTTPS 地址即可！")
    print()
    print("="*65)
    print("📝 系统信息：")
    print("   本地：http://localhost:8000")
    print("   登录：admin / Admin1234")
    print("="*65)
