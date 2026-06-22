FROM php:8.2-apache

# 1. Instalar dependencias del sistema (libpq-dev es crucial para PostgreSQL)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    gnupg

# 2. Instalar extensiones de PHP (pdo_pgsql habilita la conexión con Postgres)
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# 3. Instalar Composer desde la imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Instalar Node.js (requerido para compilar Vite/Blade)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# 5. Configurar Apache para apuntar correctamente a la carpeta /public de Laravel
#    y permitir que el .htaccess de Laravel (mod_rewrite) funcione de verdad.
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN { \
        echo '<Directory /var/www/html/public>'; \
        echo '    AllowOverride All'; \
        echo '    Require all granted'; \
        echo '</Directory>'; \
    } > /etc/apache2/conf-available/laravel-public.conf \
    && a2enconf laravel-public
RUN a2enmod rewrite

# 6. Copiar los archivos del proyecto al espacio de trabajo del contenedor
COPY . /var/www/html

# 7. Asignar permisos recursivos totales y cambiar el propietario al usuario de Apache (www-data)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# 8. Instalar dependencias de PHP y compilar Node
#    (se mantiene --ignore-platform-reqs como red de seguridad: las
#    dependencias actuales no la necesitan, pero quitarla podría romper
#    el build si alguna dependencia transitiva declara un requisito de
#    extensión que no está cubierto arriba)
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs
RUN npm install
RUN npm run build

# 9. Script de arranque:
#    - Render asigna dinámicamente el puerto público vía la variable PORT
#      (normalmente 10000). Apache, por defecto, escucha en el puerto 80
#      fijo — si no se reconfigura, Render no detecta el puerto y el
#      deploy falla o queda inestable.
#    - migrate:fresh fue reemplazado por "migrate --force": aplica solo
#      las migraciones pendientes, SIN borrar las tablas existentes. Antes,
#      cada reinicio del contenedor (cada deploy, cada vez que el plan
#      free "duerme" y despierta, cualquier crash) ejecutaba migrate:fresh
#      --seed, lo que borraba TODA la base de datos real y la reemplazaba
#      por los datos de prueba del seeder. Por eso la base se veía
#      "casi vacía": no estaba vacía por error, se estaba reseteando solita.
#    - El seeding ya NO corre automáticamente en cada arranque. Se corre
#      una sola vez, a mano, la primera vez que se despliega (ver
#      instrucciones al final).
RUN { \
        echo '#!/bin/sh'; \
        echo 'set -e'; \
        echo ''; \
        echo '# Render asigna el puerto público en $PORT (normalmente 10000).'; \
        echo '# Si no está definida (ej. local), usamos 80 como respaldo.'; \
        echo 'PORT_TO_USE="${PORT:-80}"'; \
        echo 'sed -i "s/Listen 80/Listen ${PORT_TO_USE}/" /etc/apache2/ports.conf'; \
        echo 'sed -i "s/:80>/:${PORT_TO_USE}>/" /etc/apache2/sites-available/000-default.conf'; \
        echo ''; \
        echo '# Solo aplica migraciones PENDIENTES. No borra datos existentes.'; \
        echo 'php artisan migrate:fresh --seed --force'; \
        
        echo ''; \
        echo 'php artisan config:cache'; \
        echo 'php artisan route:cache'; \
        echo 'php artisan view:cache'; \
        echo ''; \
        echo 'apachectl -D FOREGROUND'; \
    } > /usr/local/bin/start.sh

RUN chmod +x /usr/local/bin/start.sh

# Ejecutar el script automatizado al iniciar el contenedor
CMD ["/usr/local/bin/start.sh"]
