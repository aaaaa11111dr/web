#!/usr/bin/env python3
import subprocess
import time
import re

# 清理旧进程
try:
    subprocess.run(['pkill', '-f', 'lt --port'], capture_output=True)
    subprocess.run(['pkill', '-f', 'ngrok'], capture_output=True)
except:
    pass

print("🚀 正在启动 LocalTunnel...")
print("=" * 60)

# 启动 lt
process = subprocess.Popen(
    ['lt', '--port', '8000'],
    stdout=subprocess.PIPE,
    stderr=subprocess.STDOUT,
    text=True
)

# 读取输出
url = None
start_time = time.time()
timeout = 15

print("等待连接...")

while time.time() - start_time < timeout:
    line = process.stdout.readline()
    if not line:
        break
    
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
    print("✅ 公网访问配置成功！")
    print("="*60)
    print(f"🌐 主站地址：{url}")
    print(f"⚙️  管理面板：{url}/goolehome.php")
    print("="*60)
    print("\n📝 登录信息：")
    print("   用户名：admin")
    print("   密码：Admin1234")
    print("="*60)
    print("\n⚠️  注意：")
    print("   1. 首次访问可能需要在浏览器中点击验证按钮")
    print("   2. 隧道正在后台运行")
    print("="*60)
    
    # 保存 URL 到文件
    with open('/workspace/public_url.txt', 'w') as f:
        f.write(url)
else:
    print("\n⚠️  让我们用更简单的方式 - 直接在后台运行并手动查看...")
    
    # 直接后台运行
    subprocess.Popen(['lt', '--port', '8000'], 
                    stdout=open('/workspace/lt.log', 'w'),
                    stderr=open('/workspace/lt_err.log', 'w'))
    
    print("\n请稍等 5 秒，然后运行以下命令查看地址：")
    print("  cat /workspace/lt.log")
    print("\n或者手动运行：lt --port 8000")
    
    # 备用方案：使用 Python 内置的简单隧道
    print("\n\n备用方案 - 使用 cloudflared (如果安装)...")
    try:
        subprocess.Popen(['npx', '--yes', 'cloudflared', 'tunnel', '--url', 'http://localhost:8000'],
                        stdout=open('/workspace/cf.log', 'w'),
                        stderr=open('/workspace/cf_err.log', 'w'))
        print("Cloudflared 也在启动中，请稍后查看 /workspace/cf.log")
    except:
        pass
