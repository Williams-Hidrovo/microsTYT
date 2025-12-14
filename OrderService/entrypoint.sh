#!/bin/bash

echo "Esperando a que SQL Server esté listo..."
sleep 10

echo "Ejecutando migraciones..."
php artisan migrate --force

echo "Ejecutando seeders..."
php artisan db:seed --force

echo "Iniciando servidor Laravel..."
php artisan serve --host=0.0.0.0 --port=80
