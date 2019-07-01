# Intro
In this guide we'll show you how to setup a github based "dev -> stage -> live" workflow for a laravel application with lamp.io

## Dev
First off we'll create a new Laravel application from scratch.  You'll need to already have composer available (https://getcomposer.org/)

### create a laravel app named "demo" locally
```
$ laravel new demo
$ cd demo
$ php artisan serve
```
view the result in your browser at http://127.0.0.1:8000
`Ctrl-c` to return to the command prompt

### make a small change to the homepage
```
$ vi resources/views/welcome.blade.php
```
edit "Laravel" to "Laravel Demo"
```
$ php artisan serve
```
http://127.0.0.1:8000
`Ctrl-c`

Now you have a working Laravel app in your dev environment and have made your first change.
Now lets commit that change to a github repository.

### setup a github repo for your app
```
$ git commit -m 'initial commit'
```
create the new repo at https://github.com/new
```
$ git remote add origin git@github.com:jbartus/demo.git
$ git push -u origin master
```

## Live
Now lets get that change live.  We'll come back and add staging inbetween later.

### create your apps live environment
login to lamp.io
create an app
configure it with the description "live"
copy the ssh pub key
github repo -> settings -> deploye keys -> add deploy key -> paste -> add key
app_run: 
```
rm -rf * \
&& ssh-keyscan -t rsa github.com >> /etc/ssh/ssh_known_hosts \
&& git clone git@github.com:jbartus/demo.git . \
&& curl -sO https://getcomposer.org/composer.phar \
&& php composer.phar install \
&& cp .env.example .env \
&& php artisan key:generate \
&& chown -R www-data:www-data bootstrap/cache \
&& chown -R www-data:www-data storage
```
view the app

Now you've done your initial setup and deploy of your live app.
Next lets setup webhooks to automatically trigger future deploys.

### setup webhook deploys
navigate to https://www.lamp.io/api
under apps click PATCH /apps/{app_id}
click Try it out
edit the body down to 
```
{
  "data": {
    "attributes": {
      "webhook_run_command": "if [ \"$(echo $WEBHOOK_GITHUB_PAYLOAD | jq '.ref' -r)\" == \"refs/heads/master\" ]; then ssh-keyscan -t rsa github.com >> /etc/ssh/ssh_known_hosts && git pull && php composer.phar install; fi"
    },
    "id": "app-lajgu",
    "type": "apps"
  }
}
```
also add the app name at the bottom and click execute
copy the github_webhook_secret field in the response
back in your github repo go to settings -> webhooks -> add webhook 
type in https://api.lamp.io/webhooks/github/app-lajgu
chose the application/json content type
paste in the secret from earlier
click add webhook
validate 200

Now you have your webhook configured to automatically deploy your master branch on any push.
Lets test that by making another trivial change.

```
$ vi resources/views/welcome.blade.php
```
edit "Laravel Demo" to "Laravel Demo Deploy"
```
$ git commit resources/views/welcome.blade.php -m 'github webhook deploy'
$ git push
```
view the app

You may need to count to 5 before you hit refresh.
Now we've got fully automated deploys to live working, but what if we want to test something or show it to somene before putting it live?
Lets setup a stage environment for that.

## Stage
### create a stage branch
```
$ git checkout -b stage
$ vi resources/views/welcome.blade.php
```
edit "Laravel Demo Deploy" to "Laravel Demo Stage"
```
$ git commit resources/views/welcome.blade.php -m 'stage'
$ git push origin stage
```
view the app (notice it did not deploy)

### create a stage app
back on lamp.io create a second app
configure it with the name "stage"
app_run
```
rm -rf * \
&& ssh-keyscan -t rsa github.com >> /etc/ssh/ssh_known_hosts \
&& git clone -b stage git@github.com:jbartus/demo.git . \
&& curl -sO https://getcomposer.org/composer.phar \
&& php composer.phar install \
&& cp .env.example .env \
&& php artisan key:generate \
&& chown -R www-data:www-data bootstrap/cache \
&& chown -R www-data:www-data storage
```
view it

OK now that we have a stage app setup lets configure automatic deploys for it like we did for master->live.\

### setup webhook deploys for stage
navigate to https://www.lamp.io/api
under apps click PATCH /apps/{app_id}
click Try it out
paste this as the body
```
{
  "data": {
    "attributes": {
      "webhook_run_command": "if [ \"$(echo $WEBHOOK_GITHUB_PAYLOAD | jq '.ref' -r)\" == \"refs/heads/stage\" ]; then ssh-keyscan -t rsa github.com >> /etc/ssh/ssh_known_hosts && git pull && php composer.phar install; fi"
    },
    "id": "<your_stage_app_id>",
    "type": "apps"
  }
}
```
edit your app id into the body and add it to the form
click execute
copy the github_webhook_secret field in the response
back in your github repo go to settings -> webhooks -> add webhook (again, a second one)
type in https://api.lamp.io/webhooks/github/app-XXXXX
chose the application/json content type
paste in the secret from earlier
click add webhook
validate it (200)

Again now we have webhook based automatic deploys.  Lets test.

### test stage deploy
```
$ vi resources/views/welcome.blade.php
```
edit "Laravel Demo" to "Laravel Demo Stage Deploy"
```
$ git commit resources/views/welcome.blade.php -m 'github webhook stage deploy'
$ git push origin stage
```
view the app on stage (notice change)
view the app on live (notice no change)

Ok thats it, you now have the parts for a dev->stage->live workflow of a laravel app using github and lamp.io

# First Feature example workflow
## dev
```
$ vi resources/views/welcome.blade.php
```
edit "Laravel Demo" to "Laravel Feature 1"
```
php artisan serve
```
view it
`Ctrl-c`

## stage
make sure you're on the stage branch
```
$ git branch
$ git commit resources/views/welcome.blade.php -m 'feature 1'
$ git push origin stage
```
view the stage app

## live
in github nav to the stage branch
create a PR
merge it
view the live app
