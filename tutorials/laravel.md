# Laravel

## Deploying a laravel app to lamp.io
- create an app
- create a database
- paste this into `public/db.php` in your app
```
<?php
$db_host = '';
$db_pass = '';
$db_name = 'mysql';
$db_user = 'root';
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
$pdo->exec("create database homestead;");
$pdo->exec("create user 'homestead'@'%' identified by 'secret';");
$pdo->exec("grant all privileges on homestead.* to 'homestead'@'%';");
?>
```
- edit the `db_host` and `db_pass` values to reflect your database
- create the `.env` file in your apps root directory with the correct `DB\_\*` fields
- in your development environment cd into your laravel applications directory
- `curl -O https://github.com/lamp-io/examples/lampio-laravel-deploy.sh`
- configure your api token in `~./config/lamp.io/token`
- run it like this `./lampio-laravel-deploy.sh app-xxxxx`

### zero downtime deploys
- create an app
- paste this into your apps `httpd.conf` section under Configuration
```
<VirtualHost *:80>
  ServerName app-xxxxx.lamp.app
  DocumentRoot /var/www/current/public
</VirtualHost>
- edit the `app-xxxxx` above to reflect your apps ID
``` 
- create a database
- in your development environment, cd into your laravel application's directory
- `cp .env .env.live`
- edit `.env.live`'s values to reflect your new database
- `composer require laravel/envoy`
- `curl -O https://github.com/lamp-io/examples/Envoy.blade.php`
- `envoy run deploy --app=app-xxxxx`
