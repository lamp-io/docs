# Wordpress

[wordpress demo video](https://www.youtube.com/watch?v=8j2Ev1hX4ys)

- create an app
- create a db (save the password somewhere)
- in your app click 'run command'
- paste the following in
```
mysql --user=root --host= --password= --execute "\
create database wordpress; \
create user 'wordpress'@'%' identified by 'secret'; \
grant all privileges on wordpress.* to 'wordpress'@'%';"
```
- edit in the values for `host` and `password` and click Run
- in your app click 'edit files'
- click the `public` directory to move into your DocumentRoot
- in the right hand panel paste the url for the latest wordpress zip into the "FETCH" box and click it
- click on the `latest.zip` file on the left, and then click on the `unzip` button presented on the right
- load https://app-xxxxx.lamp.app/wordpress/ in your browser
- fill in the fields
- click submit
- copy the generated text
- create a `wp-config.php` file
- paste
- go back to your wordpress tab and click "run the installation"
- fill in your information
- click the "Install WordPress" button
- click the "Log In" button
- login
- write a test post
- view it
