# Install Project
-----------------------

Follow commands for run project:

1. `composer install`

2. Update database credentials (DB_DATABASE, DB_USERNAME, DB_PASSWORD) in `.env` file.

3. `php artisan migrate`

4. `php artisan db:seed`: This seeder create Fake Products.

5. `php artisan optimize:clear`

6. `php artisan serve`

# APIs documentation

- Visit `/api/documentation` route for accessing documentation