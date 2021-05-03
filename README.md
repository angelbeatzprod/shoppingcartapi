# After launching the project run these commands

docker-compose exec app php artisan migrate

docker-compose exec app php artisan passport:install

# To generate documentation
docker-compose exec app php artisan l5-swagger:generate

# To access the documentation

http://127.0.0.1:8000/api/documentation
