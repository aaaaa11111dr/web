# 内部搜索系统 - 云端部署指南

本指南将帮助您在云端环境中部署和运行内部搜索系统。

## 📋 先决条件

- 一个云端服务器（DigitalOcean、AWS EC2、Google Cloud、Azure 等）
- 服务器上已安装 Docker 和 Docker Compose
- 基本的命令行知识

## 🚀 快速开始

### 方法 1: 使用 Docker Compose（推荐）

1. **将项目文件上传到您的云端服务器**

2. **进入项目目录**
   ```bash
   cd /path/to/project
   ```

3. **启动服务**
   ```bash
   docker-compose up -d --build
   ```

4. **查看日志，确保系统正常启动**
   ```bash
   docker-compose logs -f
   ```

5. **访问系统**
   - 主站: `http://your-server-ip:8000`
   - 管理面板: `http://your-server-ip:8000/goolehome.php`
   - 默认用户名: `admin`
   - 默认密码: `Admin1234`

### 方法 2: 使用 Docker 命令

```bash
# 创建网络
docker network create search-network

# 启动 MySQL 数据库
docker run -d \
  --name search-db \
  --network search-network \
  -e MYSQL_ROOT_PASSWORD=rootpassword \
  -e MYSQL_DATABASE=wow_search \
  -e MYSQL_USER=search_user \
  -e MYSQL_PASSWORD=search_pass123 \
  -v mysql_data:/var/lib/mysql \
  mysql:8.0

# 构建并启动 Web 应用
docker build -t search-system .
docker run -d \
  --name search-web \
  --network search-network \
  -p 8000:80 \
  -e DB_HOST=search-db \
  -e DB_NAME=wow_search \
  -e DB_USER=search_user \
  -e DB_PASSWORD=search_pass123 \
  -v $(pwd):/var/www/html \
  search-system
```

## ☁️ 主流云平台部署指南

### DigitalOcean
1. 创建一个新的 Droplet（推荐 2GB 内存以上）
2. 选择 "Docker" 应用镜像
3. 按照上述快速开始指南操作

### AWS EC2
1. 启动一个 EC2 实例（推荐 t2.medium 或更大）
2. 使用 Amazon Linux 2 或 Ubuntu 并安装 Docker
3. 配置安全组，开放 8000 端口
4. 按照上述快速开始指南操作

### Google Cloud Platform (GCP)
1. 创建一个 Compute Engine 实例
2. 安装 Docker
3. 配置防火墙规则，开放 8000 端口
4. 按照上述快速开始指南操作

### Microsoft Azure
1. 创建一个 Azure 虚拟机
2. 安装 Docker
3. 配置网络安全组，开放 8000 端口
4. 按照上述快速开始指南操作

## 🌐 设置公网访问

### 选项 1: 使用 Cloudflare Tunnel（推荐）

1. **安装 Cloudflared**
   ```bash
   # Linux (Debian/Ubuntu)
   wget https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb
   sudo dpkg -i cloudflared-linux-amd64.deb
   ```

2. **启动隧道**
   ```bash
   cloudflared tunnel --url http://localhost:8000
   ```

3. **复制显示的 HTTPS 地址并访问**

### 选项 2: 使用 Nginx 反向代理 + SSL

1. **安装 Nginx**
   ```bash
   sudo apt update
   sudo apt install nginx
   ```

2. **配置 Nginx 反向代理**
   创建 `/etc/nginx/sites-available/search-system`:
   ```nginx
   server {
       listen 80;
       server_name your-domain.com;

       location / {
           proxy_pass http://localhost:8000;
           proxy_set_header Host $host;
           proxy_set_header X-Real-IP $remote_addr;
       }
   }
   ```

3. **启用配置**
   ```bash
   sudo ln -s /etc/nginx/sites-available/search-system /etc/nginx/sites-enabled/
   sudo nginx -t
   sudo systemctl reload nginx
   ```

4. **使用 Let's Encrypt 获取 SSL 证书**
   ```bash
   sudo apt install certbot python3-certbot-nginx
   sudo certbot --nginx -d your-domain.com
   ```

## 🔧 常用管理命令

```bash
# 查看服务状态
docker-compose ps

# 查看日志
docker-compose logs -f

# 停止服务
docker-compose down

# 停止服务并删除数据卷（⚠️ 会删除所有数据）
docker-compose down -v

# 更新并重新构建
docker-compose up -d --build

# 进入 Web 容器
docker-compose exec web bash
```

## 📊 默认配置

- **Web 端口**: 8000
- **数据库**: MySQL 8.0
- **数据库名**: wow_search
- **数据库用户**: search_user
- **数据库密码**: search_pass123
- **管理员用户名**: admin
- **管理员密码**: Admin1234

## ⚠️ 安全建议

1. **修改默认密码** - 首次登录后立即修改管理员密码
2. **设置防火墙** - 只允许必要的端口访问
3. **使用 HTTPS** - 始终使用 SSL/TLS 加密连接
4. **定期备份** - 定期备份数据库数据
5. **限制 IP 访问** - 在管理面板中启用 IP 白名单

## 🐛 故障排除

### 问题: 无法连接到数据库
- 确保 MySQL 容器正在运行: `docker-compose ps`
- 查看日志: `docker-compose logs db`

### 问题: 权限错误
- 确保 storage 目录有正确的权限: `chmod -R 777 storage`

### 问题: 页面显示 500 错误
- 查看 Apache 日志: `docker-compose logs web`
- 检查 PHP 错误: 在 php.ini 中启用错误显示
