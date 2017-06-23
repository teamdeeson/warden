Warden
======

Warden is for busy people managing multiple websites.  It provides a central
dashboard for reviewing the status of every website, highlighting those
with immediate issues which need resolving.

Presently Warden monitors Drupal websites. Drupal websites need to install the
[Warden Drupal module][1] in order to connect to Warden.

On the roadmap is a pluggable system allowing Warden to be used flexibly
for any website which has a supporting connector module.

Server Configuration
--------------------

Warden is built using the Symfony2 web development framework.

Symfony2 uses [Composer][2] to manage its dependencies, if you don't have
Composer yet, download it following the instructions on http://getcomposer.org/
or just run the following command:

    curl -s http://getcomposer.org/installer | php

Warden also has a dependency on [Mongodb][3], so this will need to also be
installed and PHP configured to use it.

### Mongodb Driver

Warden uses Doctrine's MongoDB ODM bundle to interface with MongoDB. Under the 
bonnet Doctrine's MongoDB ODM depends upon the MongoDB PHP driver. 

There was an [issue][4] raised due to Warden using the legacy MongoDB PHP driver.
This has been fixed now so that the latest Mongodb driver can be used when setting
up the server to run Warden.

The following page on the MongoDB docs site explains more about the different types 
of driver, the user land PHP library and compatibility with different MongoDB server 
versions and PHP language versions.

https://docs.mongodb.com/ecosystem/drivers/php/

#### Using the legacy Mongodb driver

If you are using an older version of Mongodb which limits you to the legacy mongodb 
driver, Warden is shipped with a legacy composer file to help get started.

There is a file called `composer.json-legacy` which has the package details for
the using the legacy driver. 

Before installing the Warden server, rename the file composer.json-legacy to be 
composer.json. When installing Warden the legacy Mongodb will then be installed.

Installation
------------

Once the dependencies have been installed you will need to follow these steps
to get your application started:

  * Run `composer install` to install the Symfony application fully
  * Run `./scripts/clear-cache.sh [ENV]` to clear the cache and rebuild the assets 
  for specific environment

Once set up you can log in using the credentials that you entered during the 
installation process.

The basic installation parameters are:

  * locale            - the language code (e.g. en), currently only en is supported
  * secret            - a long random string used for security
  * protocol          - how warden should be accessed, either https (recommended) 
  or http (not secure)
  * public_key_file   - the location of where the Warden app will create the public key
  * private_key_file  - the location of where the Warden app will create the private key
  
Installation parameters when using Mongodb with authentication are:

  * db_host      - the mongodb host (defaults to localhost)
  * db_port      - the mongodb port (defaults to 27017)
  * db_name      - the mongodb database name (defaults to warden)
  * db_username  - the mongodb authentication username (defaults to null)
  * db_password  - the mongodb authentication password (defaults to null)
  
If you are not using Mongodb with authentication enabled, then you can leave the 
username and password settings as 'null', otherwise these should be the username
and password needed to be able to connect the Mongodb database.
  
Installation parameters when using Swiftmailer for sending emails are:

  * mailer_transport               - the transport method to use to deliver emails (defaults to smtp)
  * mailer_host                    - The host to connect to when using smtp as the transport (defaults to 127.0.0.1)
  * mailer_port                    - The port when using smtp as the transport (defaults to 25)
  * mailer_user                    - The username when using smtp as the transport (defaults to null)
  * mailer_password                - The password when using smtp as the transport (defaults to null)
  * email_sender_address           - The email address that any emails will be sent from (defaults to blank)
  * email_dashboard_alert_address  - The email address to send the dashboard alerts to (defaults to blank)
  
Further reading on mailer configuration can be found on the [Symfony documentation][5]

How it Works
------------

Once a site has been 'registered' via the [Warden Drupal module][1], the site
is in a 'pending' state before all the data for that site has been requested 
by the Warden server.

To update the sites that are registered against the Warden server with the latest 
information for the sites and from Drupal.org, you will need to config the cron 
script to process the sites and the latest data from Drupal.org.

Cron Scripts
------------

Warden is shipped with a set of bash scripts which can be used to update the site
and Drupal module information.

In order to keep the site and module information up to date, you will need to setup
a cron entry to run the script: 

    ./scripts/cron.sh [ENV] --new-only

Where:
  * [ENV]  - the environment to run cron on (e.g. @dev, @test or @prod)
  * --new-only - set this to only import newly added sites, those that that are 
  in a 'pending' state

It is recommended to run this with the 'new-only' flag as often as you can (ideally 
every 5 minutes), as this should be a relatively short process to run as it is 
only importing new sites.

It is also recommended to then run the full import (without the 'new-only' flag) 
at least once a day, but this could be as often as you require. 
This will update all the sites and request updates from Drupal.org so this can 
be a longer running process depending upon the number of sites that you have.

Security
--------

It is recommended that this application should be run under SSL to maintain
the security of the data and the system.  For that reason this application has
the security set to 'force' to run under SSL by default.

During installation you will need to set the protocol parameter to 'https'
for secure SSL or 'http' for insecure if your server does not support SSL.

You can change this setting in app/config/parameters.yml file after installation

> *Using this application without SSL will be at your own risk.*

General Help
------------

A couple of things for you to be aware of with this application:

  1. User credentials: If you need to regenerate the user credentials run:

      `php app/console deeson:warden:install --regenerate`

  2. There is a custom CSS file generated in the following directory:

      `src/Deeson/WardenBundle/Resources/public/css/warden-custom.css`

     If you want to override any of the styling of the application edit this
     file and then run:

      `./scripts/clear-cache.sh [ENV]`

Where `[ENV]` is the environment that you are running on - @dev/ @test/ @prod

[1]:  https://www.drupal.org/project/warden
[2]:  http://getcomposer.org/
[3]:  http://docs.mongodb.org/manual/
[4]:  https://github.com/teamdeeson/warden/issues/60
[5]:  https://symfony.com/doc/2.8/reference/configuration/swiftmailer.html
