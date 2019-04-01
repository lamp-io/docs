# Laravel

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
## setup the database
- create a db
- back in your app click "App Runs"
- paste in the following:
```
mysql --user=root --host= --password= --execute "\
create database homestead; \
create user 'homestead'@'%' identified by 'secret'; \
grant all privileges on homestead.* to 'homestead'@'%';"
```
- edit in the values for `host` and `password` and click Run

## configure your local app for deployment
- `git clone git@github.com:laravel/quickstart-basic.git`
- `cp .env .env.live`
- edit `.env.live`'s `DB_HOST` value to reflect your new database
- `composer install`
- `composer require laravel/envoy`
- `curl -O https://raw.githubusercontent.com/lamp-io/docs/master/tutorials/Envoy.blade.php`

## deploy
- `envoy run deploy --app=app-xxxxx`
