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
