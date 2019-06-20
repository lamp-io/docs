# Drupal

- login to lamp.io
- click the Create App button
- click the Run Command option on the left
- paste in the following
```
rm -rf * \
&& curl -so /tmp/composer.phar https://getcomposer.org/composer.phar \
&& php /tmp/composer.phar create-project drupal-composer/drupal-project:8.x-dev . --no-interaction \
&& ln -s web public
```
- wait for the spinner to finish and be replaced with "complete", this will take about 8 minutes
- click your apps name in the navigation bar
- click the "Edit Files" option
- on the right under "new directory" type `config` and click Create
- under "directory settings" click the Toggle button to make it writable by the webserver
- load the site in a new browser tab by clicking the [/^] icon
- follow the drupal installation 
