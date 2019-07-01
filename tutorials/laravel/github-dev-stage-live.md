# Intro
In this guide we'll show you how to setup a "dev -> stage -> live" workflow for a laravel application using github and lamp.io

## Dev
First off we'll create a new Laravel application from scratch.  You'll need to already have [Composer](https://getcomposer.org) installed.

### create a new laravel app locally
In a terminal run the following commands:
```
$ laravel new demo
$ cd demo
$ php artisan serve
```
It should give you a link to [http://127.0.0.1:8000](http://127.0.0.1:8000), view that in your browser.  You should see the default Laravel welcome page.

### make a small change
Now lets make a small edit just to prove we can change things and see them working locally.  

Open the `resources/views/welcome.blade.php` file in your editor and change the "Laravel" string in the middle of the page (not the title) to "Laravel Demo".

Save the file and refresh your browser tab on [http://127.0.0.1:8000](http://127.0.0.1:8000).  You should see the updated text in the middle of the screen.

Now you have a working Laravel app in your dev environment and have made your first change.

### setup a git repo
Back in your terminal type `Ctrl-c` to stop the development webserver and get your command prompt back. Then run the following commands:
```
$ git init
$ git add .
$ git commit -m 'initial commit'
```
In your browser visit [github.com/new](https://github.com/new) to create a new github repo for your application. Copy the example commands from the second "push an existing" section and run them in your terminal.

```
$ git remote add origin git@github.com:{your_user}/demo.git
$ git push -u origin master
```

## Live
Now lets get that app live.  We'll come back and add staging inbetween later.

### create your apps live environment
In your browser go to [lamp.io](https://www.lamp.io/) and login
- click Apps in the top navigation
- click the Create App button 
- click Configure
- add the word "live" as a description
- click the Save Changes button
- navigate back to your app by clicking on its name in the top left corner
- click the Copy button on the right side at the end of the Ssh public key row

### add your deploy key to the github repo
- navigate to the github repo you created earlier
- click the Settings tab
- click the Deploy Keys section
- click the Add Deploy Key button
- paste your buffer into the form field
- click Add Key

### do the initial setup of your app
At the bottom of our apps page on lamp.io in the App runs section paste the following in the command box:
```
rm -rf * \
&& ssh-keyscan -t rsa github.com >> /etc/ssh/ssh_known_hosts \
&& git clone git@github.com:{your_user}/demo.git . \
&& curl -sO https://getcomposer.org/composer.phar \
&& php composer.phar install \
&& cp .env.example .env \
&& php artisan key:generate \
&& chown -R www-data:www-data bootstrap/cache \
&& chown -R www-data:www-data storage
```
Edit the `{your_user}` part so the url matches the repo we just created, then click the Run button.
Give it a few minutes for composer to finish.

Once its complete click the View link in the top left to see your app live.

Now that the intial setup is looking good lets configure a webhook to automatically deploy updates.

### setup webhook deploys
- In your browser tab open to your lamp.io app click the `api` link in the top right corner.
- scroll down to the `Apps` section and click the `PATCH /apps/{app_id}` row
- click the `Try it out` button
- paste the following in over/replacing the contents of the Body field

```
{
  "data": {
    "attributes": {
      "webhook_run_command": "if [ \"$(echo $WEBHOOK_GITHUB_PAYLOAD | jq '.ref' -r)\" == \"refs/heads/master\" ]; then ssh-keyscan -t rsa github.com >> /etc/ssh/ssh_known_hosts && git pull && php composer.phar install; fi"
    },
    "id": "{app_id}",
    "type": "apps"
  }
}
```

- edit the `{app_id}` part to the actual app ID we created earlier.
- also add that same app ID to the second form field
- click execute
- scroll down a bit to see the response
- copy the `github_webhook_secret` field in the response to your clipboard
- in your browser navigate to your github repo
- click the Settings tab in the upper right
- click Webhooks
- click Add Webhook
- type in `https://api.lamp.io/webhooks/github/app-XXXXX` using your app ID
- chose the application/json content type from the pulldown
- paste in the secret from from your clipbaord
- click Add webhook
- verify its green

Now you have your webhook configured to automatically deploy your master branch on any push.
Lets test that by making another trivial change.  
- Open the `resources/views/welcome.blade.php` in your editor again
- change "Laravel Demo" to "Laravel Demo Deploy"
- save your change and run the following commands in your terminal:

```
$ git commit resources/views/welcome.blade.php -m 'github webhook deploy'
$ git push
```
Refresh your browser tab displaying your live app. You may need to count to 5 before you hit refresh.  You should see your updated text, "Laravel Demo Deploy".

Now we've got fully automated deploys to live working, but what if we want to test something or show it to someone before putting it live?
Lets setup a stage environment for that.

## Stage
### create a stage branch
Run the following command in your terminal:
```
$ git checkout -b stage
```
Open `resources/views/welcome.blade.php` in your editor again.  This time make it say "Laravel Demo Stage".  Save the file and run the following commands:

```
$ git commit resources/views/welcome.blade.php -m 'stage'
$ git push origin stage
```

Now view your live app on lamp.io again.  Notice it did not deploy the change, because it was not to the `master` branch.  Next we'll give it somewhere to go.

### create a stage app
In your browser, back on lamp.io, create a second app by navigating to the Apps section and clicking the Create App button again.
- click Configure
- add the word "stage" as a description
- click the Save Changes button
- navigate back to your app by clicking on its name in the top left corner
- at the bottom of the page, in the `App runs` section paste the following in the `command` box:

```
rm -rf * \
&& ssh-keyscan -t rsa github.com >> /etc/ssh/ssh_known_hosts \
&& git clone -b stage git@github.com:{your_user}/demo.git . \
&& curl -sO https://getcomposer.org/composer.phar \
&& php composer.phar install \
&& cp .env.example .env \
&& php artisan key:generate \
&& chown -R www-data:www-data bootstrap/cache \
&& chown -R www-data:www-data storage
```
- edit the `{your_user}` part and click `Run`
- once the run is complete, scroll back near the top and click the `View: app-XXXXX.lamp.app` link

You should see "Laravel Demo Stage" proving that we just deployed the stage branch of your github repository.

Now that we have a stage app setup lets configure automatic deploys for it like we did for master->live.

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
