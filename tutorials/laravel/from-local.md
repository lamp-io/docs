# How to deploy a Laravel app to lamp.io from your local development environment

<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/qsLBDoMCYDs" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

## create and configure an app

- login to lamp.io
- click "Create App"
- under Configuration, paste the following into the `httpd.conf` section
  (edit `app-xxxxx` to reflect your app's ID)

```
<VirtualHost *:80>
  ServerName app-xxxxx.lamp.app
  DocumentRoot /var/www/current/public
</VirtualHost>
```

- click back to your app and click "App Runs"
- paste in the following but don't click "Run" yet

```
mysql --user=root --host= --password= --execute "\
create database homestead; \
create user 'homestead'@'%' identified by 'secret'; \
grant all privileges on homestead.* to 'homestead'@'%';"
```

## setup the database

- create a db
- back in your "App Runs" tab edit in the values for `host` and `password` and click Run

## configure your local app for deployment

- `git clone git@github.com:laravel/quickstart-basic.git`
- `cd quickstart-basic`
- `cp .env .env.live`
- edit `.env.live`'s `DB_HOST` value to reflect your new database
- `composer install`
- `composer global require laravel/envoy`
- `curl -O https://raw.githubusercontent.com/lamp-io/docs/master/tutorials/laravel/Envoy.blade.php`

## deploy

- `envoy run deploy --app=app-xxxxx`
