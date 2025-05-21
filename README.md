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
