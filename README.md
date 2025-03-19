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

# How to use dockerize image

NOTE:: I already created docker image for this github repository. [click here](https://hub.docker.com/r/manishtechniz/laravel-cache-apis-bytequest)

Lets Example:

1. `docker pull manishtechniz/laravel-cache-apis-bytequest`

2. `docker run -p <expose port>:80 manishtechniz/laravel-cache-apis-bytequest`

NOTE:: Suggest exposing port: `8000`, because you can run APIs from `/api/documentation` route without using `postman` and others.

When you run this command terminal will not be released because of working Apache in the foreground. If you want to interact with the running container then:

A. Open a new terminal and run commands

B. `docker container ls`: get running containers

C. `docker exec -it <cotainer-id> bash`


# Other useful commands

1. `docker start <container-id>`: Start container
2. `docker stop <container-id>` : Stop container
3. `docker container ls -a` or `docker ps -a`: List stopped containers
4. `docker system prune <container-id>`: Delete stopped container for releasing space.

Pro tip: `docker system prune $(docker ps -q)`: Delete all stopped containers.
