# How to deploy a laravel app to lamp.io from a private GitHub repo

## pre-requisites
you will need a GitHub Personal Access Token with `repo` permissions to repo you are deploying

[Creating a personal access token for the command line](https://help.github.com/en/articles/creating-a-personal-access-token-for-the-command-line)

## create and configure an app

- login to lamp.io
- click "Create App"
- click "App Runs"
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

## initial deploy
- back in your app click "App Runs"
- paste in the following (don't click RUn yet)

```
rm -rf * \
&& git init \
&& git pull https:// <token>  @github.com/ <github org> / <repo name>.git \
&& chown -R www-data:www-data bootstrap/cache \
&& chown -R www-data:www-data storage \
&& curl -sO https://getcomposer.org/composer.phar \
&& php composer.phar install \
&& sed -i 's/127.0.0.1/db-XXXXX/' .env \
&& php artisan migrate
```
- edit in the value of your Github token where it says "<token>" 
- edit in the location of the Github repo you want to deploy where it says "<github org>" and "<repo name>"
- edit in the value of your lamp.io database where it says "db-XXXXX"
- click Run, takes 2 - 3 minutes

## subsequent deploys
- again in the "App Runs" section paste the following:
```
git pull https:// <token> @github.com/ <github org> / <repo name>.git \
&& php composer.phar install \
&& php artisan migrate
```
- edit in your Github values and click Run
