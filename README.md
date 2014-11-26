Warden Installation
===================

This is a Symfony app to show an overview of all your Drupal sites.
 
The dashboard will pull the latest core and module data from your sites via the 
[Warden Drupal module][1].


Installation
------------

As Symfony uses [Composer][2] to manage its dependencies, if you don't have 
Composer yet, download it following the instructions on http://getcomposer.org/ 
or just run the following command:

    curl -s http://getcomposer.org/installer | php

Warden also has a dependency on [Mongodb][3], so this will need to also be 
installed and PHP configured to use it.

Once these dependencies have been installed you will need to follow these steps 
to get your application started: 

  * Run ./script/install.sh to install the Symfony application fully (when 
  prompted for data credentials just press enter for all of them as they are not 
  needed)
  * Run ./script/config.sh to configure the user account for accessing the dashboard
  * Run ./scripts/clear-cache.sh to clear the cache and rebuild the assets
  
Once set up you can log in using the credentials that you entered when running 
the config.sh command.

Running a Development Webserver
-------------------------------

Symfony has a build in webserver that you can use when working on a development
environment.  To start this webserver run:

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

Should you wish to run this application without SSL, you can make a slight change 
to the security.yml file (app/config).

At the end of the file there are two line under 'access_control'. Comment out 
the first line under this heading and uncomment the second line to remove the 
running of the application under SSL. 

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

[1]:  https://www.drupal.org/projects/warden
[2]:  http://getcomposer.org/
[3]:  http://docs.mongodb.org/manual/