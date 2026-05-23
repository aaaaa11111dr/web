#!/usr/bin/env python3
import subprocess
import time
import re
import select

print("="*65)
print("🚀 启动公网访问隧道")
print("="*65)

# 清理旧进程
subprocess.run(['pkill', '-f', 'lt --port'], capture_output=True)
subprocess.run(['pkill', '-f', 'cloudflared'], capture_output=True)
time.sleep(1)

# 尝试 LocalTunnel
print("\n[1] 启动 LocalTunnel...")
lt = subprocess.Popen(
    ['lt', '--port', '8000'],
    stdout=subprocess.PIPE,
    stderr=subprocess.STDOUT,
    text=True
)

found = False
timeout = 15
start = time.time()

print("等待地址...\n")

while time.time() - start < timeout and not found:
    # 使用 select 检查是否有数据可读
    ready, _, _ = select.select([lt.stdout], [], [], 0.5)
    if ready:
        line = lt.stdout.readline()
        if not line:
            break
        line = line.strip()
        if line:
            print(f"  {line}")
        
        # 检查 URL
        match = re.search(r'https?://[^\s]+\.loca\.lt', line)
        if match:
            url = match.group()
            found = True
            print("\n" + "="*65)
            print("✅ 公网访问地址获取成功！")
            print("="*65)
            print(f"🌐 主站：{url}")
            print(f"⚙️  管理：{url}/goolehome.php")
            print("="*65)
            print("\n📝 登录：admin / Admin1234")
            print("="*65)
            
            with open('/workspace/the_url.txt', 'w') as f:
                f.write(url)
            break

if not found:
    print("\n[2] LocalTunnel 未获取到，尝试 Cloudflare...")
    lt.terminate()
    time.sleep(1)
    
    cf = subprocess.Popen(
        ['npx', '--yes', 'cloudflared', 'tunnel', '--url', 'http://localhost:8000'],
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
        text=True
    )
    
    cf_start = time.time()
    while time.time() - cf_start < 25:
        ready, _, _ = select.select([cf.stdout], [], [], 0.5)
        if ready:
            line = cf.stdout.readline()
            if not line:
                break
            line = line.strip()
            if line:
                print(f"  {line}")
            
            match = re.search(r'https?://[^\s]+\.trycloudflare\.com', line)
            if match:
                url = match.group()
                print("\n" + "="*65)
                print(f"✅ CF 地址：{url}")
                print(f"管理：{url}/goolehome.php")
                print("登录：admin / Admin1234")
                print("="*65)
                with open('/workspace/the_url.txt', 'w') as f:
                    f.write(url)
                break
