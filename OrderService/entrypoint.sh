#!/bin/bash
set -e

echo "Esperando a que SQL Server y la base de datos estén listos..."

# Función para verificar la conexión a la base de datos
check_db() {
    php -r "
    try {
        \$pdo = new PDO(
            'sqlsrv:server=' . getenv('DB_HOST') . ',' . getenv('DB_PORT') . ';Database=' . getenv('DB_DATABASE') . ';TrustServerCertificate=yes',
            getenv('DB_USERNAME'),
            getenv('DB_PASSWORD'),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        exit(0);
    } catch (Exception \$e) {
        exit(1);
    }
    " 2>/dev/null
}

# Intentar conectar a la base de datos con retry logic
MAX_RETRIES=30
RETRY_COUNT=0

while [ $RETRY_COUNT -lt $MAX_RETRIES ]; do
    echo "Intento $((RETRY_COUNT + 1))/$MAX_RETRIES: Verificando conexión a la base de datos..."
    
    if check_db; then
        echo "✓ Conexión a la base de datos establecida exitosamente"
        break
    else
        RETRY_COUNT=$((RETRY_COUNT + 1))
        if [ $RETRY_COUNT -eq $MAX_RETRIES ]; then
            echo "✗ Error: No se pudo conectar a la base de datos después de $MAX_RETRIES intentos"
            exit 1
        fi
        echo "Esperando 3 segundos antes del siguiente intento..."
        sleep 3
    fi
done

echo "Ejecutando migraciones..."
php artisan migrate --force || {
    echo "✗ Error al ejecutar migraciones"
    exit 1
}
echo "✓ Migraciones ejecutadas correctamente"

echo "Ejecutando seeders..."
php artisan db:seed --force || {
    echo "⚠ Advertencia: Error al ejecutar seeders, continuando..."
}
echo "✓ Seeders ejecutados"

echo "Iniciando servidor Laravel..."
php artisan serve --host=0.0.0.0 --port=80
