## Installation
composer install

## Swagger Update
rm -r storage/api-docs
php artisan l5-swagger:generate

## Migrations & Seeds
php artisan migrate:fresh --seed

## Start Server
php artisan serve