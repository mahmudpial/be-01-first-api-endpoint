FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    postgresql-dev \
    nginx \
    supervisor \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql zip gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files first for better layer caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --optimize-autoloader

# Copy the rest of the application
COPY . .

RUN composer dump-autoload --optimize

# Laravel needs writable storage/cache directories
RUN chmod -R 775 storage bootstrap/cache

# Create nginx config
RUN mkdir -p /etc/nginx/conf.d && \
    cat > /etc/nginx/conf.d/default.conf <<'EOF'
server {
    listen 10000;
    server_name _;
    root /app/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

# Supervisor config for running both nginx and php-fpm
RUN mkdir -p /etc/supervisor/conf.d && \
    cat > /etc/supervisor/conf.d/supervisord.conf <<'EOF'
[supervisord]
nodaemon=true
logfile=/dev/null
pidfile=/var/run/supervisord.pid

[program:php-fpm]
command=/usr/local/sbin/php-fpm -F
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:nginx]
command=/usr/sbin/nginx -g "daemon off;"
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
EOF

EXPOSE 10000

# Install supervisor
RUN apk add --no-cache supervisor

CMD sh -c 'php artisan config:cache && php artisan route:cache && php artisan migrate --force && supervisord'
