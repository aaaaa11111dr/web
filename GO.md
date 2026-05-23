
# 🎉 内部搜索系统 - 公网访问指南

## ✅ 系统已成功部署！

### 📍 本地访问地址
- **主站**：http://localhost:8000
- **管理面板**：http://localhost:8000/goolehome.php

### 🔐 登录信息
- **用户名**：`admin`
- **密码**：`Admin1234`

---

## 🌐 获取公网访问地址（三种方法）

### 方法 1：最简单 - 运行下面的命令（推荐）
直接在终端中运行：
```bash
cd /workspace && lt --port 8000
```
然后复制显示的 `https://xxx.loca.lt` 地址即可！

---

### 方法 2：最稳定 - Cloudflare Tunnel
```bash
npx --yes cloudflared tunnel --url http://localhost:8000
```
复制显示的 `https://xxx.trycloudflare.com` 地址

---

### 方法 3：Serveo.net（无需安装）
```bash
ssh -R 80:localhost:8000 serveo.net
```

---

## 💡 使用步骤

1. **选择上面任一命令在终端运行**
2. **等待几秒钟，会显示一个 HTTPS 地址**
3. **复制该地址，在浏览器中打开**
4. **使用上面的登录信息登录**

---

## 📝 注意事项

- 保持隧道命令运行才能持续访问
- 首次访问可能需要简单的验证（通常点击按钮即可）
- 建议使用 screen 或 tmux 保持隧道在后台运行

---

## 🗃️ 数据库信息（如需）
- 数据库名：`wow_search`
- 用户名：`search_user`
- 密码：`search_pass123`

---

祝你使用愉快！ 🚀
