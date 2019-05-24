# How to deploy a Laravel app using SQLite

## create and configure an app

- login to lamp.io
- click "Create App"
- click "Run Command"
- paste in the following command and click "RUN"
```
rm -rf * \
&& curl -so /tmp/composer.phar https://getcomposer.org/composer.phar \
&& php /tmp/composer.phar create-project laravel/laravel . \
&& sed -i '/^DB_/d' .env \
&& echo 'DB_CONNECTION=sqlite' >> .env \
&& echo 'DB_DATABASE=/var/www/storage/database.sqlite' >> .env \
&& touch storage/database.sqlite \
&& chown -R www-data:www-data storage \
&& chown -R www-data:www-data bootstrap/cache \
&& php artisan make:auth \
&& php artisan migrate
```
- give it a few minutes, you'll see output and a spinner as it makes progress
- navigate back to your app and click the "VIEW" link to see the results
