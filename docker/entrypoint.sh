#!/bin/bash
########################################################
# Aegis Filter – Docker Entrypoint
# Ejecuta preparación de Laravel antes de iniciar Apache
########################################################

set -e

echo "==================================================="
echo "  Aegis Filter – Iniciando contenedor..."
echo "==================================================="

# Esperar que MySQL esté disponible
echo ">> Esperando conexión a base de datos..."
until php artisan db:show &>/dev/null; do
  echo "   Base de datos no disponible, reintentando en 3s..."
  sleep 3
done

echo ">> Asegurando estructura de storage..."
mkdir -p /var/www/html/storage/app/public \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/testing \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs

echo ">> Ejecutando migraciones..."
php artisan migrate --force

echo ">> Limpiando cache de configuración..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo ">> Generando enlace simbólico de storage..."
php artisan storage:link || true

echo ">> Permisos de storage..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "==================================================="
echo "  Aegis Filter – Listo. Iniciando Apache..."
echo "==================================================="

exec "$@"
