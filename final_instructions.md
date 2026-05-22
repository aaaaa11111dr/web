
# 🎉 内部搜索系统 - 公网访问配置

## ✅ 系统已部署并运行

### 📍 本地访问地址（已生效）
- **主站**：http://localhost:8000
- **管理面板**：http://localhost:8000/goolehome.php

### 🔐 登录信息
- **用户名**：`admin`
- **密码**：`Admin1234`

---

## 🌐 公网访问配置方法

### 方法 1：最简单 - LocalTunnel（推荐）
在终端中运行：
```bash
cd /workspace && lt --port 8000
```
然后复制显示的 `https://xxx.loca.lt` 地址

### 方法 2：最稳定 - Cloudflare Tunnel
```bash
npx --yes cloudflared tunnel --url http://localhost:8000
```
然后复制显示的 `https://xxx.trycloudflare.com` 地址

### 方法 3：已安装的脚本
```bash
cd /workspace && ./run_direct.sh
```

---

## 📝 数据库信息（如需要）
- **数据库名**：`wow_search`
- **用户名**：`search_user`
- **密码**：`search_pass123`

---

## 💡 使用提示
1. 复制公网地址在浏览器打开
2. 首次访问可能需要简单的验证（点击按钮）
3. 保持隧道进程运行才能持续访问
4. 建议在后台使用 `screen` 或 `tmux` 保持隧道运行

---

## 📂 项目文件
- **主目录**：/workspace
- **数据库配置**：/workspace/storage/db.php

