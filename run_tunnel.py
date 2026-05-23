#!/usr/bin/env python3
import subprocess
import time
import threading
import sys

# 停止之前的隧道进程
try:
    subprocess.run(['pkill', '-f', 'lt --port'], capture_output=True)
    subprocess.run(['pkill', '-f', 'ngrok'], capture_output=True)
except:
    pass

print("🚀 正在启动内网穿透服务...")
print("=" * 60)

# 使用 localtunnel
try:
    process = subprocess.Popen(
        ['lt', '--port', '8000'],
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
        text=True,
        bufsize=1
    )
    
    url_found = False
    timeout = 20
    start_time = time.time()
    
    def read_output():
        nonlocal url_found
        for line in process.stdout:
            line = line.strip()
            if line:
                print(f"[服务] {line}")
            if 'https://' in line and '.loca.lt' in line:
                import re
                url_match = re.search(r'https://[^\s]+loca\.lt', line)
                if url_match and not url_found:
                    url = url_match.group()
                    url_found = True
                    print("\n" + "="*60)
                    print("✅ 公网访问地址已获取！")
                    print("="*60)
                    print(f"🔗 主站地址：{url}")
                    print(f"⚙️  管理面板：{url}/goolehome.php")
                    print("="*60)
                    print("\n📝 登录信息：")
                    print("   用户名：admin")
                    print("   密码：Admin1234")
                    print("="*60)
                    print("\n⚠️  注意：首次访问可能需要在浏览器中验证")
                    print("   隧道正在后台运行中...")
                    print("="*60)
    
    # 启动输出读取线程
    output_thread = threading.Thread(target=read_output, daemon=True)
    output_thread.start()
    
    # 等待一段时间
    time.sleep(timeout)
    
    if not url_found:
        print("\n⚠️  未能自动获取地址，请检查日志或手动运行：lt --port 8000")
        
except Exception as e:
    print(f"❌ 错误：{e}")
    print("\n尝试使用另一种方法...")
    
    # 尝试使用 serveo 作为备用方案
    print("\n🚀 尝试使用 Serveo.net...")
    try:
        ssh_process = subprocess.Popen(
            ['ssh', '-o', 'StrictHostKeyChecking=no', '-R', '80:localhost:8000', 'serveo.net'],
            stdout=subprocess.PIPE,
            stderr=subprocess.STDOUT,
            text=True
        )
        
        def read_ssh():
            for line in ssh_process.stdout:
                line = line.strip()
                if line:
                    print(f"[Serveo] {line}")
        
        ssh_thread = threading.Thread(target=read_ssh, daemon=True)
        ssh_thread.start()
        time.sleep(10)
    except Exception as e2:
        print(f"❌ Serveo 也失败：{e2}")
