docker-compose build --no-cache

docker-compose up -d

ejecutar las migraciones 
docker exec orderservice php artisan migrate:fresh --force --seed


subir cambios Front
docker-compose up -d --build frontend
