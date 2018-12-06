# Overview

[lamp.io](https://www.lamp.io) is a [LAMP stack](https://en.wikipedia.org/wiki/LAMP_(software_bundle)) focused Platform as a Service
([PaaS](https://azure.microsoft.com/en-us/overview/what-is-paas/)) web application hosting provider. 
That means we provide a platform for you to run your [PHP](http://www.php.net/) code on, without you having to manage any servers.
For more about us check out our [About page](https://www.lamp.io/about).

Here you'll find documentation to help you with everything from getting started to scaling and advanced configuration.

# Getting Started

To get started with lamp.io you sign by going to https://www.lamp.io/ and clicking the "Try It" button.  
Once you've created your account and logged in you can click around and go from there. 
We've tried to make it as streamlined and clear as possible. 
If you want a little more guidance try watching our [demo video](https://www.youtube.com/watch?v=DY-oH7gQ2gQ) or keep reading below and
we'll walk you through it.

# Concepts

## LAMP App
The core product unit in lamp.io is the "app". 
We take your PHP code, and serve it up at https://random-name.lamp.app. 
Under the hood we do that in the form of [Docker containers](https://www.docker.com/resources/what-container) running in our 
[Kubernetes](https://kubernetes.io/) cluster on the [Google Cloud Platform](https://cloud.google.com/).

## Users and Organizations
When you sign up as a lamp.io user the first time you're automatically put in your own default Organization.
Resources and the billing for them are associated with Organizations, not users.
This way you can invite other users into into your Organization to work on your apps and other resources.
Similarly you can be invited into other Organizations where you can create and manage apps for them that they pay for.

## Traffic flow
your-app.lamp.app points to a [Google Cloud Load Balancer](https://cloud.google.com/load-balancing/) that sends traffic to our
Kubernetes ([GKE](https://cloud.google.com/kubernetes-engine/)) cluster. 
From there the [Nginx Ingress Controller](https://github.com/kubernetes/ingress-nginx) picks it up and decrypts it using 
[Lets Encrypt](https://letsencrypt.org/) TLS Certificates. 
Then the request goes to your container(s) running the [Apache HTTPD](https://httpd.apache.org/) server with 
[php](https://hub.docker.com/_/php/).
Those containers load your code from a [Google Cloud Filestore](https://cloud.google.com/filestore/) NFS mount.

# How-to
## create an app
- login to the web interface at [lamp.io](https://www.lamp.io/) or if already logged in click Try It
- click the 'Create App' button

## deploy code
- login to the web interface at [lamp.io](https://www.lamp.io/) or if already logged in click Try It
- select the app you want to deploy code to
- click 'edit files'
- click the 'public' directory
- from here you can create, edit and/or upload files, we've given an example `index.php` to start
- if you upload a `.zip` or `.tar.gz` file you can expand it by selecting it and clicking 'extract'
- you can also have us fetch a file from the internet for you by providing the url and clicking 'fetch'

## configure a custom domain
lamp.io does not yet directly support custom domains, but you can front us with with a CDN that does such as:
- [Fastly](https://www.fastly.com/)
- [CloudFlare](https://www.cloudflare.com/)
- [Fly.io](https://www.fly.io/)

# Tutorials
- [Wordpress](https://github.com/lamp-io/docs/blob/master/tutorials/wordpress.md)
- Laravel
- Using the API

# API Reference
Our API reference is produced as a [swagger file](https://api.lamp.io/static/swagger.json). 
You can interact with the API directly from your browser via our [Swagger-UI](https://www.lamp.io/api) setup.
