# Laravel

## drop in deploy

### creating lamp.io resources
- login to lamp.io
- create an app
- create a db (save the password and hostname somewhere)

### setting up the database
- in your app click "App Runs"
- paste in the following:
```
mysql --user=root --host= --password= --execute "\
create database homestead; \
create user 'homestead'@'%' identified by 'secret'; \
grant all privileges on homestead.* to 'homestead'@'%';"
```
- edit in the values for `host` and `password` and click Run
- create the `.env` file in your apps root directory with the correct `DB\_\*` fields

### running the deploy script
- in your development environment cd into your laravel applications directory
- `curl -O https://github.com/lamp-io/examples/lampio-laravel-deploy.sh`
- configure your api token in `~./config/lamp.io/token`
- run it like this `./lampio-laravel-deploy.sh app-xxxxx`

### zero downtime deploys
- follow the instructions above, only
- in your App, under Configuration, paste the following into the `httpd.conf` section 
(edit `app-xxxxx` to reflect your app's ID)
```
<VirtualHost *:80>
  ServerName app-xxxxx.lamp.app
  DocumentRoot /var/www/current/public
</VirtualHost>
``` 
- `cp .env .env.live`
- edit `.env.live`'s values to reflect your new database
- `composer require laravel/envoy`
- `curl -O https://github.com/lamp-io/examples/Envoy.blade.php`
- `envoy run deploy --app=app-xxxxx`
