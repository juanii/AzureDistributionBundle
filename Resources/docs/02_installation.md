---
title: Installation
layout: default
---

# Installation

Prerequisites for this bundle are a Windows development machine with the Windows Azure SDK installed. You don't need the PHP SDK to run this bundle.

You can either install the SDK through [Web Platform Installer](http://azurephp.interoperabilitybridges.com/articles/setup-the-windows-azure-development-environment-automatically-with-the-microsoft-web-platform-installer) or all [dependencies manually](http://azurephp.interoperabilitybridges.com/articles/setup-the-windows-azure-development-environment-manually).

## Download

Grab the ZIP Download from Github https://github.com/beberlei/AzureDistributionBundle and unzip it into "vendor/bundles/WindowsAzure/DistributionBundle". Proceed with section "Autoloading"

## Composer

For [Composer](http://www.packagist.org)-based application, add this package to your composer.json:

    ```javascript
    {
        "require": {
            "beberlei/azure-distribution-bundle": "*"
        }
    }
    ```

## bin\vendors and deps

For a 'bin\vendors' based application add the Git path to your 'deps' file.

    [AzureDistributionBundle]
    git=https://github.com/beberlei/AzureDistributionBundle.git
    target=/bundles/WindowsAzure/DistributionBundle

Then call "php bin\vendors install" or "php bin\vendors update" to install this package.Proceed with section "Autoloading"

## Autoloading

If you are not using Composer you have to manually register autoloading in 'app/autoload.php':

    'WindowsAzure\\DistributionBundle' => __DIR__ . '/../vendor/bundles',

Also you have to add the bundle in your kernel, see the next section on this.

## Azure Kernel

The Azure kernel can be used to set the temporary and cache directories to `sys_get_tempdir()` on production. These are the only writable directories for the webserver on Azure.

```php
<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use WindowsAzure\DistributionBundle\HttpKernel\AzureKernel;

class AppKernel extends AzureKernel
{
    $bundles = array(
        // ...
        new WindowsAzure\DistributionBundle\WindowsAzureDistributionBundle();
        // ...
    );

    // keep the old code here.

    return $bundles;
}
```