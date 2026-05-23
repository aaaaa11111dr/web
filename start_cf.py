#!/usr/bin/env python3
import subprocess
import time
import sys
import re

print("="*60)
print("🚀 启动 Cloudflare Tunnel (最简单稳定版)")
print("="*60)

# 清理旧进程
try:
    subprocess.run(['pkill', '-f', 'cloudflared'], capture_output=True)
    subprocess.run(['pkill', '-f', 'lt --port'], capture_output=True)
except:
    pass

print("正在启动中，请稍候...")

# 使用 npx 直接运行
process = subprocess.Popen(
    ['npx', '--yes', 'cloudflared', 'tunnel', '--url', 'http://localhost:8000'],
    stdout=subprocess.PIPE,
    stderr=subprocess.STDOUT,
    text=True
)

print("已启动，正在获取地址...\n")

found = False
start_time = time.time()

for line in process.stdout:
    line = line.strip()
    if line:
        print(f"[CF] {line}")
    
    # 查找 URL
    if '.trycloudflare.com' in line:
        match = re.search(r'https://[^\s]+\.trycloudflare\.com', line)
        if match and not found:
            url = match.group()
            found = True
            print("\n" + "="*60)
            print("✅ 公网访问地址已获取！")
            print("="*60)
            print(f"🌐 主站：{url}")
            print(f"⚙️  管理：{url}/goolehome.php")
            print("="*60)
            print("\n📝 登录：admin / Admin1234")
            print("="*60)
            print("\n💡 提示：直接复制上面的链接在浏览器中打开")
            print("   隧道将持续在后台运行")
            print("="*60)
            
            # 保存到文件
            with open('/workspace/url.txt', 'w') as f:
                f.write(url)
            
            break
    
    # 超时
    if time.time() - start_time > 30 and not found:
        print("\n⚠️  30秒未获取到，尝试备用方案...")
        process.terminate()
        break

if not found:
    print("\n🔄 尝试备用方案: 使用 LocalTunnel")
    process2 = subprocess.Popen(
        ['lt', '--port', '8000'],
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
        text=True
    )
    
    for line in process2.stdout:
        line = line.strip()
        if line:
            print(f"[LT] {line}")
        
        if '.loca.lt' in line:
            match = re.search(r'https://[^\s]+\.loca\.lt', line)
            if match:
                url = match.group()
                print("\n" + "="*60)
                print(f"✅ LT地址：{url}")
                print(f"管理：{url}/goolehome.php")
                print("="*60)
                print("登录：admin / Admin1234")
                print("="*60)
                break
