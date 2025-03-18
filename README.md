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


# How to Dockerize: Laravel + Mysql + Apache

> Folder Structure

```sh
- project-root/
  - Project Files
- Dockerfile
- start.sh
- apache2.conf
```

Build Image: `docker build -t image-name .`

where `-t` is tag name and `.` is `Dockerfile` location

# How to use Dockerize image

`docker run -p 8000:80 image-name`

If container is already running then use `docker exec -it container-id bash`
where `-it` is intreactive mode which mean can be move to container terminal

