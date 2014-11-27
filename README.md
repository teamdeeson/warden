Warden
======

Warden is for busy people managing multiple websites.  It provides a central
dashboard for reviewing the status of every website, highlighting those
with immediate issues which need resolving.

Presently Warden monitors Drupal websites. Drupal websites need to install the
[Warden Drupal module][1] in order to connect to Warden.

On the roadmap is a pluggable system allowing Warden to be used flexibly
for any website which has a supporting connector module.

Installation
------------

Warden is built using the Symfony2 web development framework.

Symfony2 uses [Composer][2] to manage its dependencies, if you don't have
Composer yet, download it following the instructions on http://getcomposer.org/
or just run the following command:

    curl -s http://getcomposer.org/installer | php

Warden also has a dependency on [Mongodb][3], so this will need to also be
installed and PHP configured to use it.

Once these dependencies have been installed you will need to follow these steps
to get your application started:

  * Run ./scripts/install.sh to install the Symfony application fully
  * Run ./scripts/config.sh to configure the user account for accessing the dashboard
  * Run ./scripts/clear-cache.sh to clear the cache and rebuild the assets

Once set up you can log in using the credentials that you entered when running
the config.sh command.

The installation parameters are:

* locale   - the language code (e.g. en), currently only en is supported
* secret   - a long random string used for security
* protocol - how warden should be accessed, either https (recommended) or http (not secure)

Running a Development Webserver
-------------------------------

Symfony has a built in webserver that you can use when working on a development
environment. To start this webserver run:

    ./scripts/run-webserver.sh

You can optionally pass in the environment that you would like to run (if no
parameter is passed in then it default to dev).

    ./scripts/run-webserver.sh [ENV]

Where [ENV] is the environment that you are running on - @dev/ @test/ @prod

Security
--------

It is recommended that this application should be run under SSL to maintain
the security of the data and the system.  For that reason this application has
the security set to 'force' to run under SSL by default.

During installation you will need to set the protocol parameter to https
for secure SSL or http for insecure if your server does not support SSL.

You can change this setting in app/config/parameters.yml file after installation

*Using this application without SSL will be at your own risk.*

General Help
------------

A couple of things for you to be aware of with this application:

  1. User credentials: If you need to regenerate the user credentials run:

         php app/console deeson:warden:install --regenerate

  2. There is a custom CSS file generated in the following directory:

        src/Deeson/WardenBundle/Resources/public/css/warden-custom.css

     If you want to override any of the styling of the application edit this
     file and then run:

        ./script/clear-cache.sh [ENV]

Where [ENV] is the environment that you are running on - @dev/ @test/ @prod

[1]:  https://www.drupal.org/project/warden
[2]:  http://getcomposer.org/
[3]:  http://docs.mongodb.org/manual/
