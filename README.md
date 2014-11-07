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

  * Run ./script/install.sh to install the Symfony application fully
  * Run ./script/config.sh to configure the user account for accessing the dashboard
  
Once set up you can log in using the credentials that you entered when running 
the config.sh command.

Security
--------

It is recommended that this application should be run under SSL to maintain 
the security of the data and the system.

Using this without SSL will be at your own risk.

###General Help

A couple of things for you to be aware of with this application:
 
  1. User credentials: If you need to regenerate the user credentials run:

         php app/console deeson:site-status:install --regenerate
    
  2. There is a custom CSS file generated based upon the file:

        src/Deeson/SiteStatusBundle/Resources/public/css/site-custom.css
     
     If you want to override any of the styling of the application edit this 
     file and then run: 

        ./script/clear-cache.sh [ENV]

Where [ENV] is the environment that you are running on - dev/ test/ prod

[1]:  https://www.drupal.org/projects/warden
[2]:  http://getcomposer.org/
[3]:  http://docs.mongodb.org/manual/