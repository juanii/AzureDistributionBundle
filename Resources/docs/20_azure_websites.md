---
layout: default
title: Deloyment on Windows Azure Websites
---

# Deloyment on Windows Azure Websites

With the June 2012 release Windows Azure includes Websites that allow you deploy projects
from Git. It is much easier to deploy to than the Azure Cloud Services platform, which
requires a lot of work on the build and deployment process.

You can use the **AzureDistributionBundle** to deploy your Symfony2 application to
WindowsAzure Websites. Composer dependencies will be installed on the Azure Websites
platform during the deloyment, you don't have to check them into your Git repository.

This guide explains:

1. Preparing your Symfony2 project for deployment on Azure Websites.
2. Configuring your Azure Website project for Symfony2
3. Deployment of a Symfony2 project on Azure Websites.

This guide requires you to have setup an Azure Website and configured
it to work with Git Deployment.

- [Installation of AzureDistributionBundle](http://beberlei.github.io/AzureDistributionBundle/installation.html)
- [Azure Website with MySQL And Git](http://www.windowsazure.com/en-us/develop/php/tutorials/website-w-mysql-and-git/)

## Preparing

Call the following command in your project:

    php src/console azure:websites:init

This creates two files ".deployment" and "deploy.sh" into the root of your project. It also adds a new folder `websites` into your
app folder (by default, this Bundle works with PHP5.5, think to configure the right version from the Azure Website portal. If you
need an older version, feel free to remove default embeded extension into `app/websites/php/php.ini` file).

You should modify the copied ".deployment" and "deploy.sh" files to your needs,
for example you can add calls to ``php app/console doctrine:schema-tool:update --force``
if you want to auto update your database schema.

Commit the files to your Azure Git repository:

    $ git add deploy.sh .deployment app/websites web/web.config
    $ git commit -m 'Enable Azure Websites Deployment'


## Configuration

There is two things to think about in terms of configuration: parameters application (basically, the parameters.yml file in a Symfony2
project) and the platform configuration and more precisely PHP configuration.

### Application parameters

You can either commit the ``parameters.yml`` with all the production data to your
Azure Git repository or use the [external parameters feature](http://symfony.com/doc/2.1/cookbook/configuration/external_parameters.html)
to set the configuration variables in the Windows Azure Management console.

Go to your website, "Configure" and then "app settings". Enter the environment
variables there following the ``SYMFONY__`` pattern. Dots in the variable
names of your ``parameter.yml`` translate to two underscores (``__``).

<img src="http://beberlei.github.io/AzureDistributionBundle/assets/env.png" />

### PHP platform configuration

Basically, Microsoft Azure Websites embeds a pre-configured version of PHP (if you activated it). You can choose between three 
versions (for now): PHP5.3, PHP5.4 and PHP5.5. All of these versions come with a default configuration. If you want to customize 
it, you have two possibilities:

 - First one (not recommended) is based on [Microsoft Azure Websites recommendations](http://azure.microsoft.com/en-us/documentation/articles/web-sites-php-configure/): add a `.user.ini` file to your root directory, then add your custom configurations in. PHP will basically search for this file before been executed and merge its configuration with the default php.ini. Limitations of this is 
 that you cannot add more extensions then the ones provided by default. To be able to add extensions (custom or from PHP package), 
 you have to follow the [Microsoft Azure Website Guide: Enable extensions in the default PHP Runtime](http://azure.microsoft.com/en-us/documentation/articles/web-sites-php-configure/#EnableExtDefaultPHP) adding your DLLs and configuring the `PHP_EXTENSIONS` parameter (specific from Azure Websites).
 
 - Second option (introduced by this Bundle and so recommended) is based on PHP capacities. PHP proposes an environment variable `PHP_INI_SCAN_DIR` where it will search for additional ini files to merge with the default php.ini. Thanks to this, you can
 add any extension basically embeded with the default PHP versions provided. If you take a look to the file `php.ini` created into your `app/websites/php` folder, you can see this bundle makes some basic changes (enable php\_intl and php\_fileinfo extensions and adding APCu and SQLSrv for PHP55). Feel free to adapt its content to your needs.

 > _Note_: the string ${PHP_BASE_CUSTOM_EXTENSIONS_DIR} into your `php.ini` will be automatically changed by the path to your project
 specific `php/ext` directory.


Last step is to configure Microsoft Azure Website settings:

 - Configure the following settings (thanks to the azure cli or directly from the portal):
   * `PHP_INI_SCAN_DIR` to `d:/home/site/wwwroot/app/websites/php`
   * `PHP_BASE_CUSTOM_EXTENSIONS_DIR` to `d:/home/site/wwwroot/app/websites/php/ext`

 - Configure the default endpoint to the web directory (from the portal only, *virtual applications and directories section*)
 changing the default endpoint to `site\wwwroot\web`

 - Add `app.php` as a default document (from the azure cli or from the portal - default documents section)


## Deloyment

Whenever you push to your git repository now to the Azure Websites location,
Kudo (the Git Deployment Engine of Azure Websites) will trigger the custom
build command.

    $ git push azure master

## Troubleshooting

### The website build failed, what now?

If the failure didnt happen during the kudu sync your website shouldn't be broken.
You can just hit the "retry" button in the Windows Azure Management backend and deploy again.
Should the failure happen during the kudu sync then your website might be in a broken state.
Try to redeploy as soon as you can to fix potential problems.

### A command failed during deployment, what now?

You should carefully analyze what commands you run in the ``deploy.sh`` file.
There is no interaction possible and you should take care that your website always
runs and no build step breaks it.
