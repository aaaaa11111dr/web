# 使用官方 PHP 镜像
FROM php:8.2-apache

# 安装必要的 PHP 扩展和工具
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# 启用 Apache mod_rewrite
RUN a2enmod rewrite

# 设置工作目录
WORKDIR /var/www/html

# 复制项目文件
COPY . /var/www/html

# 设置权限
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/storage \
    && chmod +x /var/www/html/docker-install.php

# 创建启动脚本
RUN echo '#!/bin/bash' > /usr/local/bin/docker-entrypoint.sh \
    && echo 'set -e' >> /usr/local/bin/docker-entrypoint.sh \
    && echo 'echo "等待数据库启动..."' >> /usr/local/bin/docker-entrypoint.sh \
    && echo 'sleep 10' >> /usr/local/bin/docker-entrypoint.sh \
    && echo 'echo "运行安装脚本..."' >> /usr/local/bin/docker-entrypoint.sh \
    && echo 'php /var/www/html/docker-install.php || true' >> /usr/local/bin/docker-entrypoint.sh \
    && echo 'echo "启动 Apache..."' >> /usr/local/bin/docker-entrypoint.sh \
    && echo 'exec apache2-foreground' >> /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# 配置 Apache
COPY .htaccess /var/www/html/.htaccess

# 暴露端口 80
EXPOSE 80

# 使用我们的入口点
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
