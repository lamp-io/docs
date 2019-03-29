# Wordpress

[video](https://www.youtube.com/watch?v=l1fAsGW3Wmw)

## creating lamp.io resources
- login to lamp.io
- create an app
- create a db (save the password and hostname somewhere)

## setting up the database
- in your app click "App Runs"
- paste in the following:
```
mysql --user=root --host= --password= --execute "\
create database wordpress; \
create user 'wordpress'@'%' identified by 'secret'; \
grant all privileges on wordpress.* to 'wordpress'@'%';"
```
- edit in the values for `host` and `password` and click Run

## getting wordpress
- in your app click 'edit files'
- click the `public` directory to move into your DocumentRoot
- in the right hand panel paste the url for the latest wordpress zip into the "FETCH" box and click it
- click on the `latest.zip` file on the left, and then click on the "UNZIP" button presented on the right

## configuring wordpress
- click the external link to load your app in a browser tab
- edit `/wordpress/` onto the end of the url and hit enter
- click "Lets Go"
- fill in the fields, and click "Submit"
- copy the generated text to your clipboard
- navigate to your app's wordpress directory
- create a new file named `wp-config.php`
- paste your config

## installing wordpress
- go back to your wordpress tab and click "Run the installation"
- fill in your information
- click the "Install WordPress" button

## using wordpress
- click the "Log In" button
- login
- write a test post
- view it
- view the updated homepage
