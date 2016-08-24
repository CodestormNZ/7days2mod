7days2mod

Silverstripe and Github Edition

This version of the 7days2mod.com website isbeing developed using the silverstripe framework and integrates with GitHub for mod storage.

To install you first need to set up a copy of silverstripe
Install Composer first, then navigate to the parent folder of where you want to framework to be installed.
Your web server should be configured to serve files from the 7days2mod folder that will be created with the composer create-project as shown below.

At the command prompt, type:
composer create-project silverstripe/installer 7days2mod

This will create a fresh install of silverstripe for you. If you have a pre configured _ss_environment.php in the folder that 7days2mod resides in then you can skip the install steps as the database and default admin user will be created when you first view a page of the new site.

_ss_environment.php

<?php
define('SS_DATABASE_SERVER','localhost');
define('SS_DATABASE_USERNAME','root'); //a user that has rights to create databases, root by default
define('SS_DATABASE_PASSWORD',''); //the password for the above account username
define('SS_DATABASE_PREFIX',''); //you can prefix text to the database name with this setting. All silverstripe DB's will also have _ss as a prefix after any setting defined here.

define('SS_ENVIRONMENT_TYPE','dev'); //dev, test, or live. If this is a public accessable site in production then set to live which disables some dev features like using the flush and dev/build commands
define('SS_DATABASE_CHOOSE_NAME', true); //this will set the database name to the same as the folder where silverstripe is installed (i.e. 7days2mod)

define('SS_DEFAULT_ADMIN_USERNAME',''); //set you default admin username here, this account will be used to log in to the /admin area of your site.
define('SS_DEFAULT_ADMIN_PASSWORD',''); //set you default admin password here
?>

For other Available Constants visit https://docs.silverstripe.org/en/getting_started/environment_management/

If you don't have the _ss_environment.php file then you will be asked to provide information on your first view of the site.

Once you have your SilverStripe installed copy the files from this repo into the folder where SilverStripe resides.
