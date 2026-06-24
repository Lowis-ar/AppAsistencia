#!/bin/bash
pm2 stop asistencia_api || true
pm2 delete asistencia_api || true
rm -rf /home/ubuntu/laravel_api
mkdir -p /home/ubuntu/laravel_api
unzip -q -o web.zip -d /home/ubuntu/laravel_api
cd /home/ubuntu/laravel_api/web
cp .env.example .env
sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=pgsql/' .env
sed -i 's/# DB_HOST=127.0.0.1/DB_HOST=172.17.0.1/' .env
sed -i 's/# DB_PORT=5432/DB_PORT=5433/' .env
sed -i 's/# DB_DATABASE=laravel/DB_DATABASE=asistencia_db/' .env
sed -i 's/# DB_USERNAME=root/DB_USERNAME=asistencia_user/' .env
sed -i 's/# DB_PASSWORD=/DB_PASSWORD=asistencia_pass/' .env
docker stop laravel_api || true
docker rm laravel_api || true
docker build -t laravel_api_image .
docker run -d --name laravel_api -p 4000:80 laravel_api_image
sleep 5
docker exec laravel_api php artisan key:generate
docker exec laravel_api php artisan migrate:fresh --force
docker exec laravel_api php artisan tinker --execute="User::create(['name'=>'Admin', 'email'=>'admin@asistencia.com', 'password'=>Hash::make('admin123'), 'role'=>'admin']);"
