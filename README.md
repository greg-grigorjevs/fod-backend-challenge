## How to test it on your system
First install the dependecies
```php
composer install
```
Then run the migrations
```php
php artisan migrate
```
Start the server
```
php artisan serve
```
Then run the tests to see that everything is working
```
php artisan test
```

Or use the postman collection in 
```
./storage/fod-api.postman_collection.json
```
To test the API using PostMan
