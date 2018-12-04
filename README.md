# Overview

lamp.io is a [LAMP stack](https://en.wikipedia.org/wiki/LAMP_(software_bundle)) focused Platform as a Service 
([PaaS](https://azure.microsoft.com/en-us/overview/what-is-paas/)) application hosting provider. 
That means we provide a platform for you to run your [PHP](http://www.php.net/) code on, without you having to manage any servers.
For more about us check out our [About page](https://www.lamp.io/about).

Here you'll find our documentation to help you with everything from getting started to scaling and advanced configuration.

# Getting Started

To get started with lamp.io you sign by going to https://www.lamp.io/ and clicking the "Try It" button.  
Once you've created your account and logged in you can click around and go from there, we've tried to make it as streamlined and clear
as possible. 
If you want a little more guidance try watching our [demo video](https://www.youtube.com/watch?v=DY-oH7gQ2gQ) or keep reading below and
we'll walk you through it.

# Concepts

## LAMP App
The central product unit in lamp.io is the "app". We take your PHP code, and serve it up at https://<random-name>.lamp.app. 
Under the hood we do that in the form of [Docker](https://www.docker.com/resources/what-container) containers running on our 
[Kubernetes](https://kubernetes.io/) cluster on the [Google Cloud Platform](https://cloud.google.com/).

## Traffic flow
your-app.lamp.app points to a [Google Cloud Load Balancer](https://cloud.google.com/load-balancing/) that sends traffic to our
Kubernetes ([GKE](https://cloud.google.com/kubernetes-engine/)) cluster. 
From there the [Nginx Ingress Controller](https://github.com/kubernetes/ingress-nginx) picks it up and decrypts it using our free 
[Lets Encrypt](https://letsencrypt.org/) TLS Certificates. Then the request goes to your container(s) running
[Apache HTTPD](https://httpd.apache.org/) server with [php](https://hub.docker.com/_/php/). 
Those containers pickup your code off a [Google Cloud Filestore](https://cloud.google.com/filestore/) NFS mount.

# How-to

# Tutorials
## Wordpress
[wordpress demo video](https://www.youtube.com/watch?v=8j2Ev1hX4ys)

## Laravel

## Using the API

# API Reference
Our API reference is produced as a [swagger file](https://api.lamp.io/static/swagger.json) and you can interact with it directly from
your browser [here](https://www.lamp.io/api)
