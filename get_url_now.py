#!/usr/bin/env python3
import subprocess
import time
import re
import sys

print("="*60)
print("🚀 正在获取公网访问地址...")
print("="*60)

# 启动 LocalTunnel
process = subprocess.Popen(
    ['lt', '--port', '8000'],
    stdout=subprocess.PIPE,
    stderr=subprocess.STDOUT,
    text=True,
    bufsize=1
)

url = None
start_time = time.time()
timeout = 20

print("等待连接建立...\n")

# 实时读取输出
while time.time() - start_time < timeout:
    # 使用非阻塞方式读取
    line = process.stdout.readline()
    if not line:
        time.sleep(0.5)
        continue
    
    line = line.strip()
    if line:
        print(f"[LT] {line}")
    
    # 查找 URL
    match = re.search(r'https://[^\s]+\.loca\.lt', line)
    if match:
        url = match.group()
        break

if url:
    print("\n" + "="*60)
    print("✅ 公网访问地址获取成功！")
    print("="*60)
    print(f"🌐 主站地址：{url}")
    print(f"⚙️  管理面板：{url}/goolehome.php")
    print("="*60)
    print("\n📝 登录信息：")
    print("   用户名：admin")
    print("   密码：Admin1234")
    print("="*60)
    print("\n💡 请复制上面的地址在浏览器中打开！")
    print("="*60)
    
    # 保存到文件
    with open('/workspace/working_url.txt', 'w') as f:
        f.write(url)
else:
    print("\n⚠️  尝试 Cloudflare Tunnel...")
    
    # 终止 LT
    process.terminate()
    time.sleep(2)
    
    # 启动 CF
    cf_process = subprocess.Popen(
        ['npx', '--yes', 'cloudflared', 'tunnel', '--url', 'http://localhost:8000'],
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
        text=True,
        bufsize=1
    )
    
    cf_start = time.time()
    while time.time() - cf_start < 25:
        line = cf_process.stdout.readline()
        if not line:
            time.sleep(0.5)
            continue
        
        line = line.strip()
        if line:
            print(f"[CF] {line}")
        
        match = re.search(r'https://[^\s]+\.trycloudflare\.com', line)
        if match:
            url = match.group()
            print("\n" + "="*60)
            print(f"✅ CF 地址：{url}")
            print(f"管理：{url}/goolehome.php")
            print("登录：admin / Admin1234")
            print("="*60)
            with open('/workspace/working_url.txt', 'w') as f:
                f.write(url)
            break
