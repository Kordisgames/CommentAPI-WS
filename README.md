## Installation
composer install

## Swagger Update
rm -r storage/api-docs
php artisan l5-swagger:generate

## Migrations & Seeds
php artisan migrate:fresh --seed

## Start Server
php artisan serve

## Test Cases
mysql > "CREATE DATABASE IF NOT EXISTS 7eminar_test;"
php artisan test tests/Feature/CommentTest.php

## Websockets
npm install
npm run build

php artisan websocket:serve
php artisan queue:work
php artisan queue:work --queue=websocket
php artisan queue:work --queue=notifications