<?php

/**
 * WindowsAzure DistributionBundle
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */
namespace WindowsAzure\DistributionBundle\Deployment\Assets;

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use WindowsAzure\DistributionBundle\Filesystem\AzureFilesystem;
use WindowsAzure\DistributionBundle\Blob\Stream;

/**
 * Serve assets from blob storage
 */
class BlobStrategy extends AssetStrategy
{

    /**
     *
     * @var string
     */
    const STREAM = 'azureassets';

    /**
     *
     * @var BlobRestProxy
     */
    private $client;

    public function __construct($container)
    {
        parent::__construct($container);
        
        $this->client = $container->get('windows_azure_distribution.assets.blob.storage');
    }

    public function deploy($documentRoot, $buildNumber)
    {
        Stream::register($this->client, self::STREAM);
        
        $this->moveTo(self::STREAM . '://v' . $buildNumber);
    }

    protected function getFilesystem()
    {
        return new AzureFilesystem();
    }
}

