# Overview

[lamp.io](https://www.lamp.io) is a [LAMP stack](https://en.wikipedia.org/wiki/LAMP_(software_bundle)) focused Platform as a Service
([PaaS](https://azure.microsoft.com/en-us/overview/what-is-paas/)) web application hosting provider. 

That means we provide a platform for you to run your [PHP](http://www.php.net/) code on, without you having to manage any servers.

For more about us check out our [About page](https://www.lamp.io/about).

# Concepts

## LAMP App
The core product unit in lamp.io is the "app". 
We take your PHP code and serve it up at `https://app-<id>.lamp.app`.

## Users and Organizations
When you sign up as a lamp.io user the first time your default Organization is created automatically.
Resources and billing are associated with Organizations, not users.
This way you can invite other users into into your Organization to work on your apps.
Similarly you can be invited into other Organizations where you can create and manage apps for them that they pay for.

## Traffic flow
your-app.lamp.app points to a [Google Cloud Load Balancer](https://cloud.google.com/load-balancing/) that sends traffic to our
Kubernetes ([GKE](https://cloud.google.com/kubernetes-engine/)) cluster. 
From there the [Nginx Ingress Controller](https://github.com/kubernetes/ingress-nginx) picks it up and decrypts it using 
[Lets Encrypt](https://letsencrypt.org/) TLS Certificates. 
Then the request goes to your container(s) running the [Apache HTTPD](https://httpd.apache.org/) server with 
[php](https://hub.docker.com/_/php/).
Those containers load your code from [Google Cloud Filestore](https://cloud.google.com/filestore/).

# How-to
## create an app
- login to the web interface at [lamp.io](https://www.lamp.io/)
- click the "Apps" link in the admin toolbar
- click the "Create App" button

## deploy code
- select the app you want to deploy code to
- click 'edit files'
- click the 'public' directory
- from here you can create, edit and/or upload files, we've given an example `index.php` to start
- if you upload a `.zip` or `.tar.gz` file you can expand it by selecting it and clicking 'extract'
- you can also have us fetch a file from the internet for you by providing the url and clicking 'fetch'

## configure a custom domain
lamp.io does not yet directly support custom domains, but you can front us with with a CDN that does such as:
- [Fastly](/tutorials/fastly/)
- [CloudFlare](https://www.cloudflare.com/)
- [Fly.io](https://www.fly.io/)

# [Tutorials](/tutorials/)
- [Wordpress](/tutorials/wordpress/)
- [Laravel](/tutorials/laravel/)
- [API](/tutorials/api/)

# API Reference
Our API reference is produced as a [swagger file](https://api.lamp.io/static/swagger.json). 
You can interact with the API directly from your browser via our [Swagger-UI](https://www.lamp.io/api) setup.
