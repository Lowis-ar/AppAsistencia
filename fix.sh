#!/bin/bash
cd /home/ubuntu/laravel_api/web
DB_IP=$(docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' asistencia_db)
echo "Found DB IP: $DB_IP"

cat .env.example | grep -v "^DB_" > .env
echo "DB_CONNECTION=pgsql" >> .env
echo "DB_HOST=$DB_IP" >> .env
echo "DB_PORT=5432" >> .env
echo "DB_DATABASE=asistencia_db" >> .env
echo "DB_USERNAME=asistencia_user" >> .env
echo "DB_PASSWORD=asistencia_pass" >> .env

docker cp .env laravel_api:/var/www/html/.env
docker exec -u root laravel_api chown www-data:www-data /var/www/html/.env
docker exec laravel_api php artisan migrate:fresh --force
docker exec laravel_api php artisan tinker --execute="User::create(['name'=>'Admin', 'email'=>'admin@asistencia.com', 'password'=>Hash::make('admin123'), 'role'=>'admin']);"
