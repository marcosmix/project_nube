#!/bin/bash

set -e

APP_DIR="/var/www/html/crm_erp_nube"
BRANCH="main"

echo "Entrando al proyecto..."
cd $APP_DIR

echo "Activando modo mantenimiento..."
php artisan down || true

echo "Guardando commit actual..."
git rev-parse HEAD > .last_deploy_commit || true

echo "Actualizando código..."
git fetch origin $BRANCH
git reset --hard origin/$BRANCH

echo "Instalando dependencias PHP..."
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

if [ -f package.json ]; then
    echo "Instalando dependencias Node..."
    if [ -f package-lock.json ]; then
        npm ci
    else
        npm install
    fi

    echo "Compilando assets..."
    npm run build
fi

#echo "Ejecutando migraciones..."
#php artisan migrate --force --isolated

echo "Limpiando caché..."
php artisan optimize:clear

echo "Cacheando configuración..."
php artisan config:cache

echo "Cacheando vistas..."
php artisan view:cache

echo "Cacheando rutas..."
php artisan route:cache || true

echo "Reiniciando colas si existen..."
php artisan queue:restart || true

echo "Corrigiendo permisos..."
sudo chown -R admin:www-data storage bootstrap/cache public/build

sudo find storage bootstrap/cache public/build -type d -exec chmod 775 {} \;
sudo find storage bootstrap/cache public/build -type f -exec chmod 664 {} \;

php artisan storage:link || true

echo "Levantando aplicación..."
php artisan up

echo "Deploy terminado correctamente."
